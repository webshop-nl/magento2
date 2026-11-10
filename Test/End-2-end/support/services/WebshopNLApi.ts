/*
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

import { execSync } from 'child_process';
import BaseApi from './BaseApi';

/** Default config for a working WebshopNL feed */
const DEFAULT_FEED_CONFIG: Record<string, string | number> = {
  'webshopnl/general/enable': 1,
  'webshopnl/feed/enable': 1,
  'webshopnl/feed/filename': 'webshop.ndjson',
};

/** One product as the feed writes it: a flat object on a line of its own. */
export interface FeedRow {
  ID: string;
  sku: string;
  [field: string]: unknown;
}

export default class WebshopNLApi extends BaseApi {
  /** The configuration path prefix of this module, for the generic layer. */
  protected modulePrefix(): string {
    return 'webshopnl';
  }

  /**
   * Generate the WebshopNL feed via CLI.
   *
   * Without a store id the command generates every enabled store view, which is what the
   * multi-store spec needs; with one it writes that store view only.
   */
  generateFeed(storeId: number | null = 1): void {
    if (!this.container) {
      throw new Error('MAGENTO_CONTAINER env var is required for feed generation');
    }

    const root = this.getMagentoRoot();
    const scope = storeId === null ? '' : ` --store-id=${storeId}`;
    const cmd = `docker exec ${this.container} php ${root}/bin/magento webshopnl:feed:create${scope}`;
    console.log(`Generating feed: ${cmd}`);
    execSync(cmd, { stdio: 'pipe', timeout: 300000 });
    console.log('Feed generated.');
  }

  /**
   * Fetch the feed over HTTP: media/webshopnl/<filename>-<storeId>.ndjson.
   *
   * The file name setting carries the extension ("webshop.ndjson"); the store id is appended
   * before it, which is why the base name is passed here without one.
   */
  async fetchFeed(baseURL: string, storeId: number = 1, baseName: string = 'webshop'): Promise<string> {
    const url = `${baseURL}media/webshopnl/${baseName}-${storeId}.ndjson`;
    console.log(`Fetching feed: ${url}`);

    const response = await fetch(url, { headers: { Accept: 'application/x-ndjson' } });
    if (!response.ok) {
      throw new Error(`Failed to fetch feed: ${response.status} ${response.statusText} (${url})`);
    }

    return response.text();
  }

  /**
   * Whether the feed file exists in the container at all.
   *
   * A store view that never produced a feed has no file, and fetching it over HTTP answers with
   * the shop's 404 page rather than an error, so the question is asked of the filesystem.
   */
  feedFileExists(storeId: number, baseName: string = 'webshop'): boolean {
    if (!this.container) {
      throw new Error('MAGENTO_CONTAINER env var is required');
    }

    const root = this.getMagentoRoot();
    const path = `${root}/pub/media/webshopnl/${baseName}-${storeId}.ndjson`;
    const out = execSync(
      `docker exec ${this.container} sh -c "test -f ${path} && echo yes || echo no"`,
      { encoding: 'utf-8' }
    ).trim();

    return out.endsWith('yes');
  }

  /** Remove every feed file, so "was it written this run" is answerable. */
  removeFeedFiles(): void {
    if (!this.container) return;
    const root = this.getMagentoRoot();
    execSync(`docker exec ${this.container} sh -c "rm -f ${root}/pub/media/webshopnl/*.ndjson"`, { stdio: 'pipe' });
  }

  /**
   * Parse the feed: newline-delimited JSON, one product per line.
   *
   * A blank trailing line is normal and skipped; anything else that does not parse is an error
   * worth failing on, because a single broken line makes the whole file unreadable to the channel.
   */
  parseFeed(ndjson: string): FeedRow[] {
    const rows: FeedRow[] = [];
    const lines = ndjson.split('\n');

    lines.forEach((line, index) => {
      if (line.trim() === '') return;
      try {
        rows.push(JSON.parse(line) as FeedRow);
      } catch (error) {
        throw new Error(`Line ${index + 1} of the feed is not JSON: ${String(error)}\n${line.slice(0, 200)}`);
      }
    });

    return rows;
  }

  /** Delete every stored webshopnl config value, reverting to the config.xml defaults. */
  resetWebshopNLConfig(): void {
    this.resetConfig('webshopnl/%');
    console.log('Config reset to config.xml defaults');
  }

  /**
   * Full feed cycle: reset all config -> apply overrides -> generate -> fetch -> parse.
   * Each cycle starts from the shipped defaults so one spec cannot colour the next.
   */
  async runFeedCycle(
    baseURL: string,
    configs: Record<string, string | number> = {},
    storeId: number = 1
  ): Promise<{ rows: FeedRow[]; ndjson: string }> {
    this.resetWebshopNLConfig();

    const fullConfig = { ...DEFAULT_FEED_CONFIG, ...configs };
    await this.setMagentoConfig(baseURL, fullConfig);

    this.generateFeed(storeId);
    const ndjson = await this.fetchFeed(baseURL, storeId);

    return { rows: this.parseFeed(ndjson), ndjson };
  }

  /**
   * What Magento's own price index holds for a SKU, at the default website and customer group.
   *
   * The feed's price has to agree with this: `final_price` is what a customer pays, while
   * `min_price` on a configurable can sit below every variant's own price when a catalog price
   * rule applies, and is then a price nothing sells for.
   */
  indexedPrice(sku: string): { price: number; final_price: number; min_price: number; max_price: number } | null {
    const json = this.execInContainer(`
      $sku = ${JSON.stringify(sku)};
      $stmt = $pdo->prepare("
        SELECT i.price, i.final_price, i.min_price, i.max_price
        FROM catalog_product_entity e
        JOIN catalog_product_index_price i ON i.entity_id = e.entity_id
        WHERE e.sku = ? AND i.website_id = 1 AND i.customer_group_id = 0
        LIMIT 1
      ");
      $stmt->execute([$sku]);
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      echo $row ? json_encode($row) : 'null';
    `).trim();

    const start = json.indexOf('{');
    if (start === -1) return null;
    const row = JSON.parse(json.slice(start));

    return {
      price: parseFloat(row.price),
      final_price: parseFloat(row.final_price),
      min_price: parseFloat(row.min_price),
      max_price: parseFloat(row.max_price),
    };
  }

  /** The SKUs of a configurable's children, so a parent can be compared with what it sells. */
  childSkus(parentSku: string): string[] {
    const json = this.execInContainer(`
      $sku = ${JSON.stringify(parentSku)};
      $stmt = $pdo->prepare("
        SELECT c.sku
        FROM catalog_product_entity p
        JOIN catalog_product_relation r ON r.parent_id = p.entity_id
        JOIN catalog_product_entity c ON c.entity_id = r.child_id
        WHERE p.sku = ?
      ");
      $stmt->execute([$sku]);
      echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));
    `).trim();

    const start = json.indexOf('[');

    return start === -1 ? [] : JSON.parse(json.slice(start));
  }

  /**
   * Create a store view in the default group, or return the one that is already there.
   *
   * Written straight into the store table: `bin/magento` has no command for it, and the admin form
   * is a page of its own that has nothing to do with this module.
   */
  createStoreView(code: string, name: string): number {
    const out = this.execInContainer(`
      $code = ${JSON.stringify(code)};
      $name = ${JSON.stringify(name)};
      $existing = $pdo->prepare("SELECT store_id FROM store WHERE code = ?");
      $existing->execute([$code]);
      $id = $existing->fetchColumn();
      if ($id) { echo (int)$id; exit; }
      $group = $pdo->query("SELECT group_id, website_id FROM store_group WHERE group_id > 0 ORDER BY group_id LIMIT 1")
        ->fetch(PDO::FETCH_ASSOC);
      $sort = (int)$pdo->query("SELECT COALESCE(MAX(sort_order), 0) + 10 FROM store")->fetchColumn();
      $pdo->prepare("
        INSERT INTO store (code, website_id, group_id, name, sort_order, is_active)
        VALUES (?, ?, ?, ?, ?, 1)
      ")->execute([$code, $group['website_id'], $group['group_id'], $name, $sort]);
      echo (int)$pdo->lastInsertId();
    `).trim();

    const id = parseInt(out.replace(/[^0-9]/g, ''), 10);
    if (!id) {
      throw new Error(`Could not create store view ${code}: ${out}`);
    }
    console.log(`Store view ready: ${code} (id ${id})`);

    return id;
  }

  /** Remove a store view and whatever was configured for it. */
  deleteStoreView(storeId: number): void {
    this.execInContainer(`
      $storeId = ${storeId};
      $pdo->prepare("DELETE FROM core_config_data WHERE scope = 'stores' AND scope_id = ?")->execute([$storeId]);
      $pdo->prepare("DELETE FROM store WHERE store_id = ?")->execute([$storeId]);
      echo 'ok';
    `);
    console.log(`Store view removed: ${storeId}`);
  }

  /** Reindex the given indexers, so a new store view has the index tables a feed run reads. */
  reindex(indexers: string[]): void {
    this.runCli(`indexer:reindex ${indexers.join(' ')}`);
  }

  /** The id of a store view by its code, or null when the shop has no such view. */
  storeIdByCode(code: string): number | null {
    const out = this.execInContainer(`
      $stmt = $pdo->prepare("SELECT store_id FROM store WHERE code = ?");
      $stmt->execute([${JSON.stringify(code)}]);
      $id = $stmt->fetchColumn();
      echo $id === false ? 'null' : (int)$id;
    `).trim();

    const digits = out.replace(/[^0-9]/g, '');

    return digits === '' ? null : parseInt(digits, 10);
  }

  /** Store a configuration value for one store view. */
  setStoreConfig(storeId: number, path: string, value: string): void {
    this.requireDb().setScopedConfig(path, value, 'stores', storeId);
  }

  /** Remove a store view's own value for a path, so the default applies again. */
  clearStoreConfig(storeId: number, path: string): void {
    this.requireDb().setScopedConfig(path, '', 'stores', storeId);
  }

  /** Every active store view id, for the multi-store check. */
  activeStoreIds(): number[] {
    const json = this.execInContainer(`
      $stmt = $pdo->query("SELECT store_id FROM store WHERE store_id > 0 AND is_active = 1 ORDER BY store_id");
      echo json_encode(array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN)));
    `).trim();

    const start = json.indexOf('[');

    return start === -1 ? [] : JSON.parse(json.slice(start));
  }

  /**
   * Enable or disable a product.
   *
   * Written straight into the EAV table rather than through the generic attribute helper: `status`
   * is a select whose options come from a source model, so there are no rows in
   * eav_attribute_option to look up. The generic helper creates one when it finds nothing, and
   * then stores that new option id as the product's status, which leaves the product neither
   * enabled nor disabled and out of every collection.
   */
  setProductStatus(sku: string, enabled: boolean): void {
    this.execInContainer(`
      $sku = ${JSON.stringify(sku)};
      $value = ${enabled ? 1 : 2};
      $id = $pdo->prepare("SELECT entity_id FROM catalog_product_entity WHERE sku = ?");
      $id->execute([$sku]);
      $entityId = (int)$id->fetchColumn();
      $attr = $pdo->query("
        SELECT a.attribute_id
        FROM eav_attribute a
        JOIN eav_entity_type t ON t.entity_type_id = a.entity_type_id
        WHERE a.attribute_code = 'status' AND t.entity_type_code = 'catalog_product'
      ");
      $attributeId = (int)$attr->fetchColumn();
      $pdo->prepare("
        INSERT INTO catalog_product_entity_int (attribute_id, store_id, entity_id, value)
        VALUES (?, 0, ?, ?)
        ON DUPLICATE KEY UPDATE value = VALUES(value)
      ")->execute([$attributeId, $entityId, $value]);
      $pdo->prepare("DELETE FROM catalog_product_entity_int WHERE attribute_id = ? AND entity_id = ? AND store_id <> 0")
        ->execute([$attributeId, $entityId]);
      echo 'ok';
    `);
    console.log(`Product status set: ${sku} enabled = ${enabled}`);
  }

  /** Put a product out of stock (or back in), so the stock filter has something to filter. */
  setStockStatus(sku: string, inStock: boolean): void {
    this.execInContainer(`
      $sku = ${JSON.stringify(sku)};
      $status = ${inStock ? 1 : 0};
      $id = $pdo->prepare("SELECT entity_id FROM catalog_product_entity WHERE sku = ?");
      $id->execute([$sku]);
      $entityId = (int)$id->fetchColumn();
      $pdo->prepare("UPDATE cataloginventory_stock_item SET is_in_stock = ? WHERE product_id = ?")
        ->execute([$status, $entityId]);
      $pdo->prepare("UPDATE cataloginventory_stock_status SET stock_status = ? WHERE product_id = ?")
        ->execute([$status, $entityId]);
      $pdo->prepare("UPDATE inventory_source_item SET status = ? WHERE sku = ?")
        ->execute([$status, $sku]);
      echo 'ok';
    `);
    console.log(`Stock status set: ${sku} in stock = ${inStock}`);
  }
}

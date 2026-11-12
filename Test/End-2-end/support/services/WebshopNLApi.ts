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

/** The integration the module creates for itself, as the database holds it. */
export interface IntegrationState {
  exists: boolean;
  status: number | null;
  hasToken: boolean;
  grantedResources: string[];
  roles?: { role_id: number; user_type: number; role_name: string }[];
  rules?: { role_id: number; user_type: number; resource_id: string; permission: string }[];
}

/** What the order endpoint answers with, one entry in a list. */
export interface OrderImportResult {
  module_version?: string;
  order_id?: string;
  remote_order_id?: number;
  merchant_id?: string;
  status?: string;
  message?: string;
}

/** The order as the database holds it, which is the source of truth for an import. */
export interface ImportedOrder {
  entity_id: number;
  increment_id: string;
  store_id: number;
  status: string;
  grand_total: number;
  shipping_amount: number;
  subtotal: number;
  customer_is_guest: number;
  customer_group_id: number;
  customer_email: string;
  shipping_method: string | null;
  total_qty_ordered: number;
}

/** One line of the order payload. `per_item` is in cents; the import divides it by 100. */
export interface PayloadProduct {
  remote_id: number;
  name: string;
  per_item: number;
  quantity: number;
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

  /**
   * The access token of the module's own integration, creating the integration when the shop has
   * none yet.
   *
   * The order endpoint is a REST route behind `WebshopNL_Connect::webapi`, so it needs a bearer
   * token rather than the token-in-the-URL that a frontend webhook would use. The module mints it
   * itself, which is also what a merchant ends up pasting into their account, so the test uses the
   * same path rather than a token of its own making.
   */
  accessToken(): string {
    if (this.token) {
      return this.token;
    }

    const before = this.integrationState();
    // Delimited rather than pattern-matched. Magento's bootstrap writes its own lines to stdout,
    // and a token picked out of that with a regular expression can just as easily be a hash from a
    // deprecation notice: the request is then refused for a token that was never the integration's,
    // which reads exactly like a missing permission.
    const out = this.runMagentoPhp(`
      $service = $om->get(\\WebshopNL\\Connect\\Service\\WebApi\\Integration::class);
      echo '<<<TOKEN>>>' . trim($service->createToken()) . '<<<END>>>';
    `);
    const delimited = out.match(/<<<TOKEN>>>(.*?)<<<END>>>/s);
    if (!delimited || delimited[1].trim() === '') {
      throw new Error(`Could not read the integration access token: ${out.slice(0, 400)}`);
    }
    const token = delimited[1].trim();

    // Magento answers a REST request from an access control list it builds once and caches, and
    // the web tier can be holding one that predates the rules the module's own setup wrote. The
    // rows are there (the access spec asserts them), the request is still refused, and no amount
    // of retrying fixes it. Dropped once per run, before the first request.
    this.flushAllCaches();
    console.log(`Integration access token ready (integration existed before: ${before.exists}).`);

    this.token = token;
    console.log(`Token in use: ${token.slice(0, 6)}... (${token.length} characters)`);

    return token;
  }

  private token: string | null = null;

  /**
   * The integration the module creates for itself, read straight from the tables.
   *
   * Asked before anything in the suite mints a token, so the answer describes what installing the
   * module leaves behind rather than what the test arranged.
   */
  integrationState(): IntegrationState {
    const json = this.execInContainer(`
      // The name the module registers its own integration under.
      $name = 'WebshopNL Integration';
      $stmt = $pdo->prepare("SELECT integration_id, consumer_id, status FROM integration WHERE name = ? LIMIT 1");
      $stmt->execute([$name]);
      $integration = $stmt->fetch(PDO::FETCH_ASSOC);
      if (!$integration) {
        echo json_encode(['exists' => false, 'status' => null, 'hasToken' => false, 'grantedResources' => []]);
        exit;
      }
      $token = $pdo->prepare("SELECT COUNT(*) FROM oauth_token WHERE consumer_id = ? AND token IS NOT NULL");
      $token->execute([$integration['consumer_id']]);
      // user_type 1 is an integration (UserContextInterface::USER_TYPE_INTEGRATION); 3 is a
      // customer. Every role for this integration id is reported with its type, so a wrong
      // assumption here shows up as data rather than as a conclusion.
      $roles = $pdo->prepare("SELECT role_id, user_type, role_name FROM authorization_role WHERE user_id = ?");
      $roles->execute([$integration['integration_id']]);
      $roleRows = $roles->fetchAll(PDO::FETCH_ASSOC);
      $granted = [];
      $ruleRows = [];
      foreach ($roleRows as $role) {
        $rules = $pdo->prepare("SELECT resource_id, permission FROM authorization_rule WHERE role_id = ?");
        $rules->execute([$role['role_id']]);
        foreach ($rules->fetchAll(PDO::FETCH_ASSOC) as $rule) {
          $ruleRows[] = ['role_id' => (int)$role['role_id'], 'user_type' => (int)$role['user_type']] + $rule;
          if ($rule['permission'] === 'allow') {
            $granted[] = $rule['resource_id'];
          }
        }
      }
      echo json_encode([
        'exists' => true,
        'status' => (int)$integration['status'],
        'hasToken' => (int)$token->fetchColumn() > 0,
        'grantedResources' => array_values(array_unique($granted)),
        'roles' => $roleRows,
        'rules' => $ruleRows,
      ]);
    `).trim();

    const start = json.indexOf('{');
    if (start === -1) {
      throw new Error(`Could not read the integration state: ${json.slice(0, 300)}`);
    }

    return JSON.parse(json.slice(start)) as IntegrationState;
  }

  /**
   * POST an order payload to the module's REST route, as the marketplace does.
   *
   * The store id lives in the path, and the body is read from the raw input stream by the module
   * rather than mapped onto the method's arguments.
   */
  async postOrder(
    baseURL: string,
    payload: Record<string, unknown>,
    storeId: number = 1,
    token: string | null = null
  ): Promise<{ status: number; body: OrderImportResult[] | Record<string, unknown> }> {
    const url = `${baseURL}rest/V1/webshopnl/order/${storeId}`;
    const headers: Record<string, string> = { 'Content-Type': 'application/json' };
    const bearer = token === null ? this.accessToken() : token;
    if (bearer !== '') {
      headers.Authorization = `Bearer ${bearer}`;
    }

    const response = await fetch(url, { method: 'POST', headers, body: JSON.stringify(payload) });
    const text = await response.text();
    let body: OrderImportResult[] | Record<string, unknown>;
    try {
      body = JSON.parse(text);
    } catch {
      throw new Error(`Order endpoint answered with no JSON (${response.status}): ${text.slice(0, 400)}`);
    }

    return { status: response.status, body };
  }

  /**
   * Switch Magento's "Allow OAuth Access Tokens to be used as standalone Bearer tokens" on or off.
   *
   * Since Magento 2.4.4 this is off unless a merchant turns it on, and with it off an integration
   * access token is ignored on a REST request: no user context, no role, and every resource
   * refused with the same message Magento uses for a missing grant. The marketplace authenticates
   * with exactly such a token, so the order route depends on this setting.
   */
  allowIntegrationBearerToken(enabled: boolean): void {
    this.requireDb().setConfig('oauth/consumer/enable_integration_as_bearer', enabled ? '1' : '0');
    this.flushAllCaches();
    console.log(`Integration tokens allowed as bearer tokens: ${enabled}`);
  }

  /**
   * The same authenticated GET, but issued from inside the container so it bypasses whatever sits
   * in front of it.
   *
   * A bearer token only reaches Magento when the web server passes the Authorization header on;
   * nginx needs `fastcgi_param HTTP_AUTHORIZATION` and Apache needs CGIPassAuth. When that is
   * missing, Magento sees an anonymous request and refuses every resource with the same message
   * it uses for a missing grant, which is indistinguishable from a module defect from the outside.
   */
  restGetInsideContainer(route: string): { status: number; body: string } {
    if (!this.container) {
      throw new Error('MAGENTO_CONTAINER env var is required');
    }

    const token = this.accessToken();
    const path = `rest/${route.replace(/^\//, '')}`;
    const out = execSync(
      `docker exec ${this.container} curl -s -o /tmp/rest-probe -w '%{http_code}' ` +
      `-H 'Accept: application/json' -H 'Authorization: Bearer ${token}' http://localhost/${path} ` +
      `&& docker exec ${this.container} head -c 200 /tmp/rest-probe`,
      { encoding: 'utf-8', timeout: 60000 }
    );

    const status = parseInt(out.slice(0, 3), 10);

    return { status: Number.isNaN(status) ? 0 : status, body: out.slice(3, 260) };
  }

  /**
   * A bare authenticated GET against any REST route, for telling an access problem that is ours
   * from one that is not.
   */
  async restGet(baseURL: string, route: string): Promise<{ status: number; body: string }> {
    const response = await fetch(`${baseURL}rest/${route.replace(/^\//, '')}`, {
      headers: { Accept: 'application/json', Authorization: `Bearer ${this.accessToken()}` },
    });

    return { status: response.status, body: (await response.text()).slice(0, 200) };
  }

  /** The module's status route for one order, by Magento entity id. */
  async orderStatus(
    baseURL: string,
    orderEntityId: number
  ): Promise<{ status: number; body: Record<string, unknown>[] | Record<string, unknown> }> {
    const url = `${baseURL}rest/V1/webshopnl/status/${orderEntityId}`;
    const response = await fetch(url, {
      headers: { Accept: 'application/json', Authorization: `Bearer ${this.accessToken()}` },
    });
    const text = await response.text();

    return { status: response.status, body: JSON.parse(text) };
  }

  /** The module's info route, which reports the installed version. */
  async moduleInfo(baseURL: string): Promise<Record<string, unknown>[] | Record<string, unknown>> {
    const response = await fetch(`${baseURL}rest/V1/webshopnl/info`, {
      headers: { Accept: 'application/json', Authorization: `Bearer ${this.accessToken()}` },
    });

    return JSON.parse(await response.text());
  }

  /**
   * An order payload with every field the module's own validation demands, and nothing it does
   * not read. Overrides are merged one level deep, so a spec can replace a single address field.
   */
  buildOrderData(overrides: Record<string, any> = {}): Record<string, any> {
    const unique = `${Date.now()}${Math.floor(Math.random() * 1000)}`;
    const address = {
      first_name: 'Eva',
      surname: 'de Vries',
      street_name: 'Keizersgracht 123',
      city: 'Amsterdam',
      zip_code: '1015 CJ',
      country_code: 'NL',
      email: `e2e-${unique}@example.com`,
      phone: '0201234567',
    };

    const base: Record<string, any> = {
      order_id: `E2E-${unique}`,
      merchant_id: 'e2e-merchant',
      customer: { email: address.email },
      billing_address: { ...address },
      delivery_address: { ...address },
      items: { products: [] },
      shipping_method: { cost: 6.95 },
    };

    const merged: Record<string, any> = { ...base };
    for (const [key, value] of Object.entries(overrides)) {
      merged[key] = value !== null && typeof value === 'object' && !Array.isArray(value)
        ? { ...base[key], ...value }
        : value;
    }

    return merged;
  }

  /**
   * What Magento's own access control says about the integration and the order resource, asked the
   * way a Web API request asks it: the role is looked up by user id together with user type, and
   * the verdict comes from the built access control list rather than from the rule table.
   *
   * Reported as data, including the token this suite sends, because the table saying "allow" and
   * the request saying "denied" can both be true at once, and only this call sits in between.
   */
  aclVerdict(resource: string): Record<string, unknown> {
    const out = this.runMagentoPhp(`
      $resource = ${JSON.stringify(resource)};
      $integrationService = $om->get(\\Magento\\Integration\\Api\\IntegrationServiceInterface::class);
      $integration = $integrationService->findByName('WebshopNL Integration');
      $roles = $om->get(\\Magento\\Authorization\\Model\\ResourceModel\\Role\\CollectionFactory::class)->create();
      $role = $roles->setUserFilter((int)$integration->getId(), 1)->getFirstItem();
      $acl = $om->get(\\Magento\\Framework\\Acl\\Builder::class)->getAcl();
      $oauth = $om->get(\\Magento\\Integration\\Api\\OauthServiceInterface::class);
      $token = $oauth->getAccessToken((int)$integration->getConsumerId());
      echo json_encode([
        'integrationId' => (int)$integration->getId(),
        'consumerId' => (int)$integration->getConsumerId(),
        'roleId' => (int)$role->getId(),
        'tokenPrefix' => $token ? substr((string)$token->getToken(), 0, 6) : null,
        'tokenType' => $token ? (string)$token->getType() : null,
        'tokenUserType' => $token ? (int)$token->getUserType() : null,
        'hasRole' => $acl->hasRole((string)$role->getId()),
        'hasResource' => $acl->hasResource($resource),
        'isAllowed' => $role->getId() ? $acl->isAllowed((string)$role->getId(), $resource) : null,
      ]);
    `);

    const start = out.indexOf('{');
    if (start === -1) {
      throw new Error(`Could not read the access control verdict: ${out.slice(0, 400)}`);
    }

    return JSON.parse(out.slice(start, out.lastIndexOf('}') + 1));
  }

  /** One payload line for a product, priced in cents as the channel sends it. */
  payloadProduct(productId: number, priceInCents: number, quantity: number = 1, name = 'E2E product'): PayloadProduct {
    return { remote_id: productId, name, per_item: priceInCents, quantity };
  }

  /** The entity id of a product by SKU, which is what the payload's remote_id refers to. */
  productIdBySku(sku: string): number {
    const out = this.execInContainer(`
      $stmt = $pdo->prepare("SELECT entity_id FROM catalog_product_entity WHERE sku = ?");
      $stmt->execute([${JSON.stringify(sku)}]);
      echo (int)$stmt->fetchColumn();
    `).trim();

    const id = parseInt(out.replace(/[^0-9]/g, ''), 10);
    if (!id) {
      throw new Error(`No product with sku ${sku}`);
    }

    return id;
  }

  /** The order the import wrote for a payload's order_id, or null when there is none. */
  importedOrder(webshopnlOrderId: string): ImportedOrder | null {
    const json = this.execInContainer(`
      $stmt = $pdo->prepare("
        SELECT entity_id, increment_id, store_id, status, grand_total, shipping_amount, subtotal,
               customer_is_guest, customer_group_id, customer_email, shipping_method, total_qty_ordered
        FROM sales_order WHERE webshopnl_order_id = ? ORDER BY entity_id DESC LIMIT 1
      ");
      $stmt->execute([${JSON.stringify(webshopnlOrderId)}]);
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      echo $row ? json_encode($row) : 'null';
    `).trim();

    const start = json.indexOf('{');
    if (start === -1) return null;
    const row = JSON.parse(json.slice(start));

    return {
      entity_id: parseInt(row.entity_id, 10),
      increment_id: String(row.increment_id),
      store_id: parseInt(row.store_id, 10),
      status: String(row.status),
      grand_total: parseFloat(row.grand_total),
      shipping_amount: parseFloat(row.shipping_amount),
      subtotal: parseFloat(row.subtotal),
      customer_is_guest: parseInt(row.customer_is_guest, 10),
      customer_group_id: parseInt(row.customer_group_id, 10),
      customer_email: String(row.customer_email),
      shipping_method: row.shipping_method === null ? null : String(row.shipping_method),
      total_qty_ordered: parseFloat(row.total_qty_ordered),
    };
  }

  /**
   * The order's lines as the database holds them, for an assertion that has to say what went wrong
   * rather than only that a total did not match.
   */
  orderItems(orderEntityId: number): Record<string, string>[] {
    const json = this.execInContainer(`
      $stmt = $pdo->prepare("
        SELECT sku, qty_ordered, price, base_price, original_price, price_incl_tax, row_total, discount_amount, tax_percent
        FROM sales_order_item WHERE order_id = ?
      ");
      $stmt->execute([${orderEntityId}]);
      echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    `).trim();

    const start = json.indexOf('[');

    return start === -1 ? [] : JSON.parse(json.slice(start));
  }

  /** How many orders the shop holds for a payload's order_id, so a duplicate is provable. */
  orderCount(webshopnlOrderId: string): number {
    const out = this.execInContainer(`
      $stmt = $pdo->prepare("SELECT COUNT(*) FROM sales_order WHERE webshopnl_order_id = ?");
      $stmt->execute([${JSON.stringify(webshopnlOrderId)}]);
      echo (int)$stmt->fetchColumn();
    `).trim();

    return parseInt(out.replace(/[^0-9]/g, ''), 10) || 0;
  }

  /** Whether the import also created an invoice for an order. */
  invoiceCount(orderEntityId: number): number {
    const out = this.execInContainer(`
      $stmt = $pdo->prepare("SELECT COUNT(*) FROM sales_invoice WHERE order_id = ?");
      $stmt->execute([${orderEntityId}]);
      echo (int)$stmt->fetchColumn();
    `).trim();

    return parseInt(out.replace(/[^0-9]/g, ''), 10) || 0;
  }

  /** The customer account an import created, by email, or null when it stayed a guest order. */
  customerByEmail(email: string): { entity_id: number; group_id: number; store_id: number } | null {
    const json = this.execInContainer(`
      $stmt = $pdo->prepare("SELECT entity_id, group_id, store_id FROM customer_entity WHERE email = ? LIMIT 1");
      $stmt->execute([${JSON.stringify(email)}]);
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      echo $row ? json_encode($row) : 'null';
    `).trim();

    const start = json.indexOf('{');
    if (start === -1) return null;
    const row = JSON.parse(json.slice(start));

    return {
      entity_id: parseInt(row.entity_id, 10),
      group_id: parseInt(row.group_id, 10),
      store_id: parseInt(row.store_id, 10),
    };
  }

  /** The billing address of an order, for the fields the payload carries. */
  orderBillingAddress(orderEntityId: number): Record<string, string> {
    const json = this.execInContainer(`
      $stmt = $pdo->prepare("
        SELECT company, firstname, lastname, street, city, postcode, country_id, telephone, vat_id, email, region_id
        FROM sales_order_address WHERE parent_id = ? AND address_type = 'billing' LIMIT 1
      ");
      $stmt->execute([${orderEntityId}]);
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      echo json_encode($row ?: []);
    `).trim();

    const start = json.indexOf('{');

    return start === -1 ? {} : JSON.parse(json.slice(start));
  }

  /**
   * Ship an order in full with a tracking number, so the status route has delivery data to report.
   *
   * Through Magento's own ship-order service rather than the admin form: what is under test is
   * what the module reports about a shipment, not how one is created.
   */
  shipOrder(orderEntityId: number, trackingNumber: string): void {
    this.runMagentoPhp(`
      $ship = $om->get(\\Magento\\Sales\\Api\\ShipOrderInterface::class);
      $trackFactory = $om->get(\\Magento\\Sales\\Api\\Data\\ShipmentTrackCreationInterfaceFactory::class);
      $track = $trackFactory->create();
      $track->setTrackNumber(${JSON.stringify(trackingNumber)});
      $track->setCarrierCode('custom');
      $track->setTitle('E2E carrier');
      echo $ship->execute(${orderEntityId}, [], false, false, null, [], [$track]);
    `);
    console.log(`Order ${orderEntityId} shipped with tracking ${trackingNumber}`);
  }

  /** Put stock on a product so an order for it can actually be placed. */
  restock(sku: string, qty: number = 1000): void {
    this.execInContainer(`
      $sku = ${JSON.stringify(sku)};
      $id = $pdo->prepare("SELECT entity_id FROM catalog_product_entity WHERE sku = ?");
      $id->execute([$sku]);
      $entityId = (int)$id->fetchColumn();
      $pdo->prepare("UPDATE cataloginventory_stock_item SET qty = ?, is_in_stock = 1 WHERE product_id = ?")
        ->execute([${qty}, $entityId]);
      $pdo->prepare("UPDATE cataloginventory_stock_status SET qty = ?, stock_status = 1 WHERE product_id = ?")
        ->execute([${qty}, $entityId]);
      $pdo->prepare("UPDATE inventory_source_item SET quantity = ?, status = 1 WHERE sku = ?")
        ->execute([${qty}, $sku]);
      echo 'ok';
    `);
  }
}

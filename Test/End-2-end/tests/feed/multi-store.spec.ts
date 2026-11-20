/*
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Until release 1.2.0 a shop with more than one store view got one feed. The price collection was
 * built once and reused, so the second store view added the same price_index join a second time
 * and the run stopped on "You cannot define a correlation name 'price_index' more than once" -
 * after the first file was already on disk, which is why it read as a success. The second store
 * view is created in global setup, so this runs on a shop that ships one.
 */

import { test, expect } from '@playwright/test';
import WebshopNLApi from 'Services/WebshopNLApi';
import { SECOND_STORE_CODE } from '../../global-setup';

const api = new WebshopNLApi();

/** Where the second store view's products are linked, so the two feeds can be told apart. */
const SECOND_STORE_PATH = 'second/';

test.describe('Every store view', () => {
  let storeIds: number[] = [];

  test.beforeAll(async ({}, testInfo) => {
    const baseURL = testInfo.project.use.baseURL!;
    storeIds = api.activeStoreIds();
    if (storeIds.length < 2) {
      return;
    }

    const second = api.storeIdByCode(SECOND_STORE_CODE) ?? storeIds[storeIds.length - 1];
    // Both store views sit in the same group and would otherwise generate the same links, which
    // makes "every store view got its own feed" impossible to tell from "the first one twice".
    // The feed builds its links from base_url of the store it is generating, so that is the value
    // to move; nothing in this suite opens a page on the second store view.
    api.setStoreConfig(second, 'web/unsecure/base_url', `${baseURL}${SECOND_STORE_PATH}`);
    api.setStoreConfig(second, 'web/secure/base_url', `${baseURL}${SECOND_STORE_PATH}`);

    api.resetWebshopNLConfig();
    await api.setMagentoConfig(baseURL, {
      'webshopnl/general/enable': 1,
      'webshopnl/feed/enable': 1,
      'webshopnl/feed/filename': 'webshop.ndjson',
    });

    api.removeFeedFiles();
    // No --store-id: the command then walks every enabled store view, which is the path that broke.
    api.generateFeed(null);
  });

  test.afterAll(() => {
    if (storeIds.length < 2) {
      return;
    }
    const second = api.storeIdByCode(SECOND_STORE_CODE) ?? storeIds[storeIds.length - 1];
    api.clearStoreConfig(second, 'web/unsecure/base_url');
    api.clearStoreConfig(second, 'web/secure/base_url');
    api.flushAllCaches();
  });

  test('one run writes a feed for every enabled store view', () => {
    test.skip(storeIds.length < 2, 'this shop has a single store view; nothing to tell apart');

    for (const storeId of storeIds) {
      expect(api.feedFileExists(storeId), `store view ${storeId} has a feed file`).toBe(true);
    }
  });

  test('the run reports every store view and no collection error', () => {
    test.skip(storeIds.length < 2, 'this shop has a single store view; nothing to tell apart');

    const run = api.runCliRaw('webshopnl:feed:create');
    expect(run.output, 'the run names a correlation it already defined').not.toMatch(/correlation name/i);

    const reported = run.output.match(/Store ID (\d+)/g) ?? [];
    expect(
      reported.length,
      `expected a line per store view, got:\n${run.output}`
    ).toBe(storeIds.length);
  });

  test('each store view writes its own products, not the first one twice', async ({}, testInfo) => {
    const baseURL = testInfo.project.use.baseURL!;
    test.skip(storeIds.length < 2, 'this shop has a single store view; nothing to tell apart');

    const first = storeIds[0];
    const second = api.storeIdByCode(SECOND_STORE_CODE) ?? storeIds[storeIds.length - 1];
    const firstRows = api.parseFeed(await api.fetchFeed(baseURL, first));
    const secondRows = api.parseFeed(await api.fetchFeed(baseURL, second));

    expect(firstRows.length, `store view ${first} has products`).toBeGreaterThan(0);
    expect(secondRows.length, `store view ${second} has products`).toBeGreaterThan(0);

    // The same catalogue is sold in both views, so the product set matches while the links differ:
    // each row points at the store view it was written for.
    expect(secondRows.length, 'both feeds cover the same catalogue').toBe(firstRows.length);

    const secondUrls = secondRows.map((row) => String(row.productURL));
    const wrongStore = secondUrls.filter((url) => !url.includes(`/${SECOND_STORE_PATH}`));
    expect(
      wrongStore.length,
      `${wrongStore.length} of ${secondUrls.length} rows in the feed for store view ${second} link to another store view, e.g. ${wrongStore[0]}`
    ).toBe(0);
  });
});

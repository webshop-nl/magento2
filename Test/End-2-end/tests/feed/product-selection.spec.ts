/*
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Which products reach the channel. Until release 1.2.0 the "exclude out of stock" setting filled
 * in an unrelated "add disabled products" flag, so with the setting off every disabled product was
 * published and no product was ever held back for being out of stock.
 */

import { test, expect } from '@playwright/test';
import WebshopNLApi from 'Services/WebshopNLApi';

const api = new WebshopNLApi();

/** A plain simple product from the sample catalogue, sold on its own. */
const SIMPLE_SKU = '24-MB01';

test.describe('Product selection', () => {
  test.afterAll(() => {
    try {
      api.setProductStatus(SIMPLE_SKU, true);
      api.setStockStatus(SIMPLE_SKU, true);
      api.flushAllCaches();
    } catch {
      // best effort: the next spec resets the configuration anyway
    }
  });

  test('a disabled product stays out of the feed', async ({}, testInfo) => {
    const baseURL = testInfo.project.use.baseURL!;

    api.setProductStatus(SIMPLE_SKU, true);
    const { rows: withProduct } = await api.runFeedCycle(baseURL);
    expect(withProduct.some((row) => row.sku === SIMPLE_SKU), `${SIMPLE_SKU} is in the feed when enabled`).toBe(true);

    api.setProductStatus(SIMPLE_SKU, false);
    const { rows: withoutProduct } = await api.runFeedCycle(baseURL);

    expect(
      withoutProduct.some((row) => row.sku === SIMPLE_SKU),
      `${SIMPLE_SKU} is disabled and must not be published`
    ).toBe(false);
    expect(withoutProduct.length, 'the feed is one product shorter').toBe(withProduct.length - 1);

    api.setProductStatus(SIMPLE_SKU, true);
  });

  test('with the stock filter on, an out of stock product stays out of the feed', async ({}, testInfo) => {
    const baseURL = testInfo.project.use.baseURL!;

    api.setStockStatus(SIMPLE_SKU, false);
    api.flushAllCaches();

    const { rows: unfiltered } = await api.runFeedCycle(baseURL, { 'webshopnl/feed/filter_stock': 0 });
    expect(
      unfiltered.some((row) => row.sku === SIMPLE_SKU),
      `with the filter off ${SIMPLE_SKU} is published even though it is out of stock`
    ).toBe(true);

    const { rows: filtered } = await api.runFeedCycle(baseURL, { 'webshopnl/feed/filter_stock': 1 });
    expect(
      filtered.some((row) => row.sku === SIMPLE_SKU),
      `with the filter on ${SIMPLE_SKU} must be held back`
    ).toBe(false);

    api.setStockStatus(SIMPLE_SKU, true);
    api.flushAllCaches();
  });

  test('a variant carries its parent in family_ID and a standalone product does not', async ({}, testInfo) => {
    const baseURL = testInfo.project.use.baseURL!;
    const { rows } = await api.runFeedCycle(baseURL);

    const children = api.childSkus('MP01');
    expect(children.length, 'MP01 has variants in the sample catalogue').toBeGreaterThan(0);

    const child = rows.find((row) => children.includes(String(row.sku)));
    expect(child, 'a variant of MP01 is in the feed').toBeTruthy();
    expect(String(child!.family_ID), `variant ${child!.sku} names its parent`).not.toBe('');

    const standalone = rows.find((row) => row.sku === SIMPLE_SKU);
    expect(standalone, `${SIMPLE_SKU} is in the feed`).toBeTruthy();
    expect(
      String(standalone!.family_ID ?? ''),
      `${SIMPLE_SKU} is sold on its own and has no parent`
    ).toBe('');
  });
});

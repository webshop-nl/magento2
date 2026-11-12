/*
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * The two routes a marketplace polls after it has sent an order: the status of one order, and the
 * module's own version.
 *
 * The status route is where release 1.2.0 fixed a crash: the shipment lookup runs on Magento's
 * concrete order model while the repository is declared to hand back the interface, and the
 * shipment collection of an order that was never saved is false rather than empty. Both paths are
 * exercised here, with an order that has no shipment and one that has a shipment with tracking.
 */

import { expect, test } from '@playwright/test';
import WebshopNLApi, { ImportedOrder, OrderImportResult } from 'Services/WebshopNLApi';

const api = new WebshopNLApi();

const PRODUCT_SKU = '24-MB01';
const TRACKING = 'E2E-TRACK-0001';

const BASE_CONFIG: Record<string, string | number> = {
  'webshopnl/general/enable': 1,
  'webshopnl/order/shipping_method': 'flatrate_flatrate',
  'webshopnl/order/shipping_method_fallback': 'flatrate_flatrate',
  'webshopnl/order/import_customer': 0,
};

let productId: number;
let unshipped: ImportedOrder;
let shipped: ImportedOrder;

/** The single entry the status route answers with. */
const entry = (body: unknown): Record<string, unknown> => {
  expect(Array.isArray(body), `a list, got ${JSON.stringify(body).slice(0, 300)}`).toBe(true);
  const list = body as Record<string, unknown>[];
  expect(list.length, 'one entry').toBe(1);

  return list[0];
};

async function importOne(baseURL: string): Promise<ImportedOrder> {
  const payload = api.buildOrderData({ items: { products: [api.payloadProduct(productId, 1999)] } });
  const response = await api.postOrder(baseURL, payload, 1);
  const answer = (response.body as OrderImportResult[])[0];
  expect(answer?.status, `import of ${payload.order_id}: ${answer?.message}`).toBe('CONFIRMED');
  const order = api.importedOrder(String(payload.order_id));
  expect(order, 'the order is in sales_order').not.toBeNull();

  return order!;
}

test.beforeAll(async ({}, testInfo) => {
  const baseURL = testInfo.project.use.baseURL!;
  productId = api.productIdBySku(PRODUCT_SKU);
  api.restock(PRODUCT_SKU);
  // The marketplace authenticates with the module's integration token, which Magento ignores on a
  // REST request unless this is on. Off is the default since 2.4.4.
  api.allowIntegrationBearerToken(true);
  api.resetWebshopNLConfig();
  await api.setMagentoConfig(baseURL, BASE_CONFIG);

  unshipped = await importOne(baseURL);
  shipped = await importOne(baseURL);
  api.shipOrder(shipped.entity_id, TRACKING);
});

test.describe('Order status route', () => {
  test('an order without a shipment reports no delivery data', async ({ baseURL }) => {
    const response = await api.orderStatus(baseURL!, unshipped.entity_id);
    expect(response.status, 'HTTP status').toBe(200);

    const status = entry(response.body);
    expect(status.id, 'the order it was asked about').toBe(unshipped.entity_id);
    expect(status.status, 'the Magento order status').toBe(unshipped.status);
    expect(status.shipment_available, 'nothing shipped yet').toBe(false);
    expect(status.delivery_data, 'and so no delivery data').toEqual([]);
    expect(status.module_version, 'the module version is reported').toBeTruthy();
  });

  test('a shipped order reports its tracking number', async ({ baseURL }) => {
    const response = await api.orderStatus(baseURL!, shipped.entity_id);
    expect(response.status, 'HTTP status').toBe(200);

    const status = entry(response.body);
    expect(status.shipment_available, 'it has a shipment').toBe(true);
    expect(JSON.stringify(status.delivery_data), 'the tracking number is in the delivery data')
      .toContain(TRACKING);
  });

  test('an unknown order id is answered with a bad request, not an exception', async ({ baseURL }) => {
    const response = await api.orderStatus(baseURL!, 99999999);
    expect(response.status, 'a 400, because the order does not exist').toBe(400);
    expect(JSON.stringify(response.body), 'and a message naming the id').toContain('99999999');
  });

  test('the info route reports the installed version', async ({ baseURL }) => {
    const info = await api.moduleInfo(baseURL!);
    expect(JSON.stringify(info), 'a version that looks like one').toMatch(/\d+\.\d+\.\d+/);
  });
});

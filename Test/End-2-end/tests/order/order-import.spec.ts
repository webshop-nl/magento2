/*
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Order import: a marketplace posts an order to the module's REST route and the shop has to end up
 * with that order.
 *
 * Release 1.2.0 repaired four ways this could die without saying anything useful, and none of them
 * was reachable from a unit test: a null return from the quote submission dereferenced as an
 * object, a catch reporting on a variable that does not exist when the first product fails, invoice
 * methods called on an interface that does not declare them, and an access token dereferenced
 * without a check. This spec drives the real route with a real token, so those paths run.
 *
 * Each case asserts against the database rather than the response, because the response is the
 * module's own summary of what it believes it did.
 */

import { expect, test } from '@playwright/test';
import WebshopNLApi, { ImportedOrder, OrderImportResult } from 'Services/WebshopNLApi';
import OrderViewPage from 'Pages/backend/OrderViewPage';

const api = new WebshopNLApi();
const orderView = new OrderViewPage();

/** A simple product from the sample catalogue, in stock and orderable. */
const PRODUCT_SKU = '24-MB01';

/** What every case starts from: the module on, orders on, a known shipping method. */
const BASE_CONFIG: Record<string, string | number> = {
  'webshopnl/general/enable': 1,
  'webshopnl/order/shipping_method': 'flatrate_flatrate',
  'webshopnl/order/shipping_method_fallback': 'flatrate_flatrate',
  'webshopnl/order/import_customer': 0,
  'webshopnl/order/order_email': 0,
  'webshopnl/order/invoice_order_email': 0,
};

let productId: number;

/** The single entry in the module's response list. */
const result = (body: unknown): OrderImportResult => {
  expect(Array.isArray(body), `the endpoint answers with a list, got ${JSON.stringify(body).slice(0, 300)}`).toBe(true);
  const list = body as OrderImportResult[];
  expect(list.length, 'one result entry').toBe(1);

  return list[0];
};

/** Post a payload and return both the module's answer and the order it wrote. */
async function importOrder(
  baseURL: string,
  payload: Record<string, any>,
  storeId = 1
): Promise<{ answer: OrderImportResult; order: ImportedOrder }> {
  const response = await api.postOrder(baseURL, payload, storeId);
  expect(response.status, `HTTP status (${JSON.stringify(response.body).slice(0, 300)})`).toBe(200);
  const answer = result(response.body);
  expect(answer.status, `import status for ${payload.order_id}: ${answer.message}`).toBe('CONFIRMED');

  const order = api.importedOrder(String(payload.order_id));
  expect(order, `order ${payload.order_id} is in sales_order`).not.toBeNull();

  return { answer, order: order! };
}

test.beforeAll(async () => {
  productId = api.productIdBySku(PRODUCT_SKU);
  api.restock(PRODUCT_SKU);
  // The marketplace authenticates with the module's integration token, which Magento ignores on a
  // REST request unless this is on. Off is the default since 2.4.4.
  api.allowIntegrationBearerToken(true);
});

test.beforeEach(async ({}, testInfo) => {
  api.resetWebshopNLConfig();
  await api.setMagentoConfig(testInfo.project.use.baseURL!, BASE_CONFIG);
});

test.describe('Order import', () => {
  test('a posted order becomes a Magento order, and the response names it', async ({ baseURL }) => {
    const payload = api.buildOrderData({ items: { products: [api.payloadProduct(productId, 1999)] } });
    const { answer, order } = await importOrder(baseURL!, payload);

    expect(answer.order_id, 'the channel order id is echoed back').toBe(payload.order_id);
    // Reported as a string: Magento hands entity ids over as strings and the REST serializer
    // keeps them that way.
    expect(Number(answer.remote_order_id), 'the Magento entity id is reported back').toBe(order.entity_id);
    expect(answer.merchant_id, 'the merchant id is echoed back').toBe('e2e-merchant');
    expect(answer.module_version, 'the module version is reported').toBeTruthy();
    expect(order.increment_id, 'the order has an increment id').toMatch(/\d/);
  });

  test('an item price arrives in cents and lands as a currency amount', async ({ baseURL }) => {
    const payload = api.buildOrderData({ items: { products: [api.payloadProduct(productId, 1999)] } });
    const { order } = await importOrder(baseURL!, payload);

    expect(
      order.subtotal,
      `per_item 1999 is 19.99; lines: ${JSON.stringify(api.orderItems(order.entity_id))}; grand total ${order.grand_total}`
    ).toBeCloseTo(19.99, 2);
    expect(order.total_qty_ordered, 'one of them').toBeCloseTo(1, 2);
  });

  test('a quantity above one multiplies the line, not the price', async ({ baseURL }) => {
    const payload = api.buildOrderData({ items: { products: [api.payloadProduct(productId, 1250, 3)] } });
    const { order } = await importOrder(baseURL!, payload);

    expect(order.total_qty_ordered, 'three ordered').toBeCloseTo(3, 2);
    expect(
      order.subtotal,
      `3 x 12.50; lines: ${JSON.stringify(api.orderItems(order.entity_id))}`
    ).toBeCloseTo(37.5, 2);
  });

  test('the shipping cost from the payload ends up on the order', async ({ baseURL }) => {
    const payload = api.buildOrderData({
      items: { products: [api.payloadProduct(productId, 1999)] },
      shipping_method: { cost: 7.5 },
    });
    const { order } = await importOrder(baseURL!, payload);

    expect(order.shipping_amount, 'the cost the channel sent').toBeCloseTo(7.5, 2);
    expect(order.shipping_method, 'the configured method').toBe('flatrate_flatrate');
  });

  test('an unavailable shipping method falls back to the configured fallback', async ({ baseURL }) => {
    await api.setMagentoConfig(baseURL!, {
      'webshopnl/order/shipping_method': 'webshopnl_webshopnl',
      'webshopnl/order/shipping_method_fallback': 'flatrate_flatrate',
      'carriers/webshopnl/active': 0,
    });

    const payload = api.buildOrderData({ items: { products: [api.payloadProduct(productId, 1999)] } });
    const { order } = await importOrder(baseURL!, payload);

    expect(order.shipping_method, 'the fallback, because the configured carrier offered no rate')
      .toBe('flatrate_flatrate');
  });

  test('without customer import the order stays a guest order', async ({ baseURL }) => {
    const payload = api.buildOrderData({ items: { products: [api.payloadProduct(productId, 1999)] } });
    const { order } = await importOrder(baseURL!, payload);

    expect(order.customer_is_guest, 'guest').toBe(1);
    expect(order.customer_group_id, 'NOT LOGGED IN').toBe(0);
    expect(api.customerByEmail(order.customer_email), 'no account was created').toBeNull();
  });

  test('with customer import an account is created in the configured group', async ({ baseURL }) => {
    await api.setMagentoConfig(baseURL!, {
      'webshopnl/order/import_customer': 1,
      'webshopnl/order/customers_group': 1,
    });

    const payload = api.buildOrderData({ items: { products: [api.payloadProduct(productId, 1999)] } });
    const { order } = await importOrder(baseURL!, payload);

    expect(order.customer_is_guest, 'not a guest').toBe(0);
    const customer = api.customerByEmail(order.customer_email);
    expect(customer, `an account for ${order.customer_email}`).not.toBeNull();
    expect(customer!.group_id, 'the configured group').toBe(1);
  });

  test('company and VAT number land on the billing address', async ({ baseURL }) => {
    const payload = api.buildOrderData({
      items: { products: [api.payloadProduct(productId, 1999)] },
      billing_address: { company: 'Magmodules BV', vat_id: 'NL123456789B01' },
    });
    const { order } = await importOrder(baseURL!, payload);

    const address = api.orderBillingAddress(order.entity_id);
    expect(address.company, 'company').toBe('Magmodules BV');
    expect(address.vat_id, 'VAT id').toBe('NL123456789B01');
  });

  test('a payload without a phone number still gets a telephone, because Magento demands one', async ({ baseURL }) => {
    const payload = api.buildOrderData({ items: { products: [api.payloadProduct(productId, 1999)] } });
    delete payload.billing_address.phone;
    delete payload.delivery_address.phone;

    const { order } = await importOrder(baseURL!, payload);
    const address = api.orderBillingAddress(order.entity_id);
    expect(address.telephone, 'the placeholder the import falls back to').toBe('000');
  });

  test('a mobile number wins over a landline', async ({ baseURL }) => {
    const payload = api.buildOrderData({
      items: { products: [api.payloadProduct(productId, 1999)] },
      delivery_address: { phone: '0201234567', mobile: '0612345678' },
    });
    const { order } = await importOrder(baseURL!, payload);

    const address = api.orderBillingAddress(order.entity_id);
    expect(address.telephone, 'the mobile number').toBe('0612345678');
  });

  test('every imported order is invoiced', async ({ baseURL }) => {
    const payload = api.buildOrderData({ items: { products: [api.payloadProduct(productId, 1999)] } });
    const { order } = await importOrder(baseURL!, payload);

    // The import creates the invoice unconditionally; there is no setting for it.
    expect(api.invoiceCount(order.entity_id), 'one invoice').toBe(1);
  });

  test('the same order posted twice is refused, and no second order appears', async ({ baseURL }) => {
    const payload = api.buildOrderData({ items: { products: [api.payloadProduct(productId, 1999)] } });
    await importOrder(baseURL!, payload);

    const second = await api.postOrder(baseURL!, payload, 1);
    expect(second.status, 'HTTP status').toBe(200);
    const answer = result(second.body);
    expect(answer.status, 'refused').toBe('REJECTED');
    expect(String(answer.message), 'and says why').toContain('already imported');
    expect(api.orderCount(String(payload.order_id)), 'still one order').toBe(1);
  });

  test('the import records when it last ran', async ({ baseURL }) => {
    const payload = api.buildOrderData({ items: { products: [api.payloadProduct(productId, 1999)] } });
    await importOrder(baseURL!, payload);

    const stored = api.requireDb().readConfig(['webshopnl/order/last_order_import']);
    expect(stored['webshopnl/order/last_order_import'], 'a timestamp').toMatch(/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/);
  });

  test('the imported order renders in the admin', async ({ baseURL, page }) => {
    const payload = api.buildOrderData({
      items: { products: [api.payloadProduct(productId, 1999)] },
      billing_address: { company: 'Magmodules BV' },
    });
    const { order } = await importOrder(baseURL!, payload);

    await orderView.openById(page, order.entity_id);
    expect(await orderView.incrementId(page), 'the heading names the order').toBe(order.increment_id);
    expect(await orderView.shippingAddress(page), 'the street from the payload').toContain('Keizersgracht 123');
    expect(await orderView.billingAddress(page), 'the company from the payload').toContain('Magmodules BV');
    expect(await orderView.shippingMethod(page), 'the shipping method').not.toBe('');
  });
});

test.describe('Order import: what the module refuses', () => {
  const missing: { title: string; mangle: (payload: Record<string, any>) => void; says: string }[] = [
    { title: 'no delivery address', mangle: (p) => { delete p.delivery_address; }, says: 'delivery_address' },
    { title: 'no billing address', mangle: (p) => { delete p.billing_address; }, says: 'billing_address' },
    { title: 'no email', mangle: (p) => { delete p.delivery_address.email; }, says: 'email' },
    { title: 'no first name', mangle: (p) => { delete p.delivery_address.first_name; }, says: 'first_name' },
    { title: 'no surname', mangle: (p) => { delete p.delivery_address.surname; }, says: 'surname' },
    { title: 'no products', mangle: (p) => { p.items = { products: [] }; }, says: 'products' },
    { title: 'no shipping cost', mangle: (p) => { delete p.shipping_method.cost; }, says: 'shipping_method' },
  ];

  for (const condition of missing) {
    test(`a payload with ${condition.title} is refused with a message naming it`, async ({ baseURL }) => {
      const payload = api.buildOrderData({ items: { products: [api.payloadProduct(productId, 1999)] } });
      condition.mangle(payload);

      const response = await api.postOrder(baseURL!, payload, 1);
      const text = JSON.stringify(response.body);
      expect(text, `the answer names ${condition.says}`).toContain(condition.says);
      expect(api.orderCount(String(payload.order_id)), 'no order was created').toBe(0);
    });
  }

  test('a payload without an order id is refused', async ({ baseURL }) => {
    const payload = api.buildOrderData({ items: { products: [api.payloadProduct(productId, 1999)] } });
    const orderId = String(payload.order_id);
    delete payload.order_id;

    const response = await api.postOrder(baseURL!, payload, 1);
    expect(JSON.stringify(response.body), 'says the order id is missing').toContain('order_id');
    expect(api.orderCount(orderId), 'no order was created').toBe(0);
  });

  test('a request without a token is refused by the ACL', async ({ baseURL }) => {
    const payload = api.buildOrderData({ items: { products: [api.payloadProduct(productId, 1999)] } });

    const response = await api.postOrder(baseURL!, payload, 1, '');
    expect([401, 403], `an unauthenticated POST is refused, got ${response.status}`).toContain(response.status);
    expect(api.orderCount(String(payload.order_id)), 'no order was created').toBe(0);
  });
});

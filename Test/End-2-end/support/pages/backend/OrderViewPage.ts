/*
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

import { expect, Page } from '@playwright/test';
import { adminUrl } from 'Helpers/AdminUrl';
import { MAGENTO_ERROR } from 'Helpers/MagentoErrors';

/**
 * Magento's own order view, read the way a merchant reads it.
 *
 * The database is the source of truth for what an import wrote, and the specs assert there. This
 * page is for the other half of the question: whether what was imported also renders. An order
 * that is correct in the tables and blank on screen is still a broken import.
 */
export default class OrderViewPage {
  async openById(page: Page, orderEntityId: number): Promise<void> {
    await page.goto(adminUrl(`/admin/sales/order/view/order_id/${orderEntityId}`));
    await expect(page.locator('h1').first()).toBeVisible({ timeout: 30_000 });
    await expect(page.locator('body')).not.toContainText(MAGENTO_ERROR);
  }

  /** The increment id in the heading, which reads "#000000123". */
  async incrementId(page: Page): Promise<string> {
    const text = (await page.locator('h1').first().textContent() ?? '').trim();
    const match = text.match(/#\s*(\S+)/);

    return match ? match[1] : text;
  }

  async status(page: Page): Promise<string> {
    return ((await page.locator('#order_status').textContent()) ?? '').trim();
  }

  async customerGroup(page: Page): Promise<string> {
    const cell = page.locator('tr', { hasText: 'Customer Group' }).locator('td').first();

    return ((await cell.textContent()) ?? '').trim();
  }

  async shippingAddress(page: Page): Promise<string> {
    return ((await page.locator('.order-shipping-address address').first().innerText()) ?? '').trim();
  }

  async billingAddress(page: Page): Promise<string> {
    return ((await page.locator('.order-billing-address address').first().innerText()) ?? '').trim();
  }

  async shippingMethod(page: Page): Promise<string> {
    return ((await page.locator('.order-shipping-method').textContent()) ?? '').trim();
  }

  /** A total from the order totals table, by the label Magento prints next to it. */
  async total(page: Page, label: string): Promise<string> {
    const row = page.locator('.order-subtotal-table tr', { hasText: label }).first();
    if (await row.count() === 0) {
      throw new Error(`No "${label}" row in the order totals`);
    }

    return ((await row.locator('td').last().textContent()) ?? '').trim();
  }

  /** The ordered quantity of the first item, which Magento prints as "Ordered 3". */
  async firstItemQty(page: Page): Promise<string> {
    const table = page.locator('.edit-order-table, .order-tables .data-table').first();

    return ((await table.locator('tbody tr').first().innerText()) ?? '').trim();
  }
}

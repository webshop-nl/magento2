/*
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * The price of a configurable parent: the price a customer can actually pay for the cheapest
 * variant, which is what the store page shows. Neither column of Magento's price index answers
 * that on its own. min_price can sit below every variant - with a catalog price rule on a
 * configurable, a 35.00 product indexes as final 28.00 and min 21.00 while all variants cost
 * 28.00. final_price can sit above them - with the discount on the variants instead, the same
 * product indexes as final 35.00 while the cheapest variant costs 28.00. Both published a price
 * nothing in the catalogue sells for, which is what this spec exists to keep out of the feed.
 */

import { test, expect } from '@playwright/test';
import WebshopNLApi, { FeedRow } from 'Services/WebshopNLApi';

const api = new WebshopNLApi();

/** A configurable from the sample catalogue: twelve variants, all at the same price. */
const PARENT_SKU = 'MP01';

let rows: FeedRow[];

test.beforeAll(async ({}, testInfo) => {
  const baseURL = testInfo.project.use.baseURL!;
  rows = (await api.runFeedCycle(baseURL)).rows;
});

/** "28.00 EUR" -> 28 */
const amount = (value: unknown): number => parseFloat(String(value ?? '').replace(/[^0-9.]/g, ''));

/** The parent and its variants as the feed published them. */
const family = () => {
  const children = api.childSkus(PARENT_SKU);
  const parent = rows.find((row) => row.sku === PARENT_SKU);
  const variants = rows.filter((row) => children.includes(String(row.sku)));

  return { parent, variants, prices: variants.map((row) => amount(row.price)) };
};

test.describe('Configurable parent price', () => {
  test('the parent is published at the price of its cheapest variant', () => {
    const { parent, prices } = family();
    expect(parent, `${PARENT_SKU} is in the feed`).toBeTruthy();
    expect(prices.length, `${PARENT_SKU} has variants in the feed`).toBeGreaterThan(0);

    expect(
      amount(parent!.price),
      `${PARENT_SKU} is published at ${parent!.price} while its variants cost ${[...new Set(prices)].join(', ')}`
    ).toBeCloseTo(Math.min(...prices), 2);
  });

  test('the parent price is one a variant actually sells for', () => {
    const { parent, prices } = family();

    // The whole point: whatever the index holds, the parent's price has to exist in the catalogue.
    expect(
      prices,
      `${PARENT_SKU} is published at ${parent!.price}, which no variant sells for`
    ).toContain(amount(parent!.price));
  });

  test('neither index column is published blind', () => {
    const indexed = api.indexedPrice(PARENT_SKU);
    expect(indexed, `${PARENT_SKU} is in the price index`).toBeTruthy();

    const { parent, prices } = family();
    const cheapest = Math.min(...prices);

    // Named so a failure says which of the two old behaviours came back. Either column is allowed
    // to be right - it just may not be published when it disagrees with the variants.
    if (Math.abs(indexed!.final_price - cheapest) > 0.005) {
      expect(
        amount(parent!.price),
        `${PARENT_SKU} is published at the index final price ${indexed!.final_price}, above its cheapest variant ${cheapest}`
      ).not.toBeCloseTo(indexed!.final_price, 2);
    }
    if (Math.abs(indexed!.min_price - cheapest) > 0.005) {
      expect(
        amount(parent!.price),
        `${PARENT_SKU} is published at the index minimum ${indexed!.min_price}, which no variant sells for`
      ).not.toBeCloseTo(indexed!.min_price, 2);
    }
  });

  test('every variant carries its own price, not the one next to it in the batch', () => {
    const { variants } = family();
    expect(variants.length, `${PARENT_SKU} has variants in the feed`).toBeGreaterThan(0);

    for (const variant of variants) {
      const indexed = api.indexedPrice(String(variant.sku));
      expect(indexed, `${variant.sku} is in the price index`).toBeTruthy();
      expect(
        amount(variant.price),
        `variant ${variant.sku} is published at ${variant.price} while the index says ${indexed!.final_price}`
      ).toBeCloseTo(indexed!.final_price, 2);
    }
  });

  test('both prices are written as an amount with a currency', () => {
    const { parent } = family();
    expect(String(parent!.price), 'price').toMatch(/^\d+\.\d{2} [A-Z]{3}$/);
    expect(String(parent!.fromPrice), 'fromPrice').toMatch(/^\d+\.\d{2} [A-Z]{3}$/);
    expect(
      amount(parent!.fromPrice),
      `fromPrice ${parent!.fromPrice} is the price before a discount, so never below price ${parent!.price}`
    ).toBeGreaterThanOrEqual(amount(parent!.price));
  });
});

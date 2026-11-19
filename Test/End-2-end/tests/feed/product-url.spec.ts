/*
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Until release 1.2.0 the feed shipped 1859 of its 2040 products without a link: only a product
 * with a URL rewrite of its own got one, so every variant of a configurable and every component
 * of a bundle reached the channel with an empty productURL and nothing to click through to. A
 * product without a link is not sellable through the channel, which makes this the one field that
 * must never be empty again.
 *
 * Filling it is not enough. The collector falls back on catalog/product/view/id/<id> for a product
 * without a rewrite, and that page does not exist for a variant, which is not visible on its own:
 * 1859 of 2040 rows carried a link that answered 404. So the links are fetched here, not just
 * matched against a pattern.
 */

import { test, expect } from '@playwright/test';
import WebshopNLApi, { FeedRow } from 'Services/WebshopNLApi';

const api = new WebshopNLApi();

let rows: FeedRow[];

test.beforeAll(async ({}, testInfo) => {
  const baseURL = testInfo.project.use.baseURL!;
  rows = (await api.runFeedCycle(baseURL)).rows;
});

test.describe('Product URL', () => {
  test('every product has a non-empty productURL', () => {
    const empty = rows.filter((row) => !row.productURL || String(row.productURL).trim() === '');
    expect(
      empty.length,
      `${empty.length} of ${rows.length} products have no productURL, e.g. ${empty.slice(0, 5).map((row) => row.sku).join(', ')}`
    ).toBe(0);
  });

  test('every productURL is an absolute http(s) address', () => {
    const bad = rows.filter((row) => !/^https?:\/\//.test(String(row.productURL)));
    expect(
      bad.length,
      `not absolute: ${bad.slice(0, 5).map((row) => `${row.sku}=${row.productURL}`).join(', ')}`
    ).toBe(0);
  });

  test('a product with a parent is never published on the id pattern', () => {
    // The id pattern resolves only for a product that is visible on its own, so for a variant it is
    // a 404. A child inherits its parent's URL instead. What stays behind is the orphan: not
    // visible on its own and with no parent in catalog_product_relation or bundle selections, so
    // there is no address that works. The sample catalogue has six, all Sprite Stasis Ball colours.
    const withParent = rows.filter((row) => row.family_ID !== undefined && String(row.family_ID) !== '');
    const idPattern = withParent.filter((row) => String(row.productURL).includes('/catalog/product/view/id/'));

    expect(
      idPattern.length,
      `${idPattern.length} of ${withParent.length} rows with a parent fall back on the id pattern, e.g. ${idPattern.slice(0, 5).map((row) => `${row.sku}=${row.productURL}`).join(', ')}`
    ).toBe(0);
  });

  test('the orphans that keep the id pattern stay the handful we know about', () => {
    const orphans = rows
      .filter((row) => !row.family_ID || String(row.family_ID) === '')
      .filter((row) => String(row.productURL).includes('/catalog/product/view/id/'));

    // A rise here means a product type stopped inheriting, not that the catalogue grew.
    expect(
      orphans.length,
      `orphans on the id pattern: ${orphans.map((row) => row.sku).join(', ')}`
    ).toBeLessThanOrEqual(6);
  });

  test('the links resolve, for variants as well as for products of their own', async ({ request }) => {
    const children = rows.filter((row) => row.family_ID !== undefined && String(row.family_ID) !== '');
    const standalone = rows.filter((row) => !row.family_ID || String(row.family_ID) === '');

    // A sample rather than all 2040: enough to catch a pattern that does not resolve, cheap enough
    // to run on every build.
    // Orphans are left out: they have no working address at all, which the test above pins.
    const sample = [
      ...children.slice(0, 10),
      ...standalone.filter((row) => !String(row.productURL).includes('/catalog/product/view/id/')).slice(0, 5)
    ];
    expect(sample.length, 'there is something to fetch').toBeGreaterThan(0);

    for (const row of sample) {
      const response = await request.get(String(row.productURL));
      expect(response.status(), `${row.sku} -> ${row.productURL}`).toBe(200);
    }
  });

  test('a variant of a configurable has a link of its own', () => {
    // These are the rows that used to be empty: children carry a family_ID and, before 1.2.0, no URL.
    const children = rows.filter((row) => row.family_ID !== undefined && String(row.family_ID) !== '');
    expect(children.length, 'the catalogue has variants to check').toBeGreaterThan(0);

    for (const child of children) {
      expect(String(child.productURL), `variant ${child.sku}`).toMatch(/^https?:\/\//);
    }
  });
});

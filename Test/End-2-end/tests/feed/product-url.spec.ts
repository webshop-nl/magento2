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

  test('a variant of a configurable has a link of its own', () => {
    // These are the rows that used to be empty: children carry a family_ID and, before 1.2.0, no URL.
    const children = rows.filter((row) => row.family_ID !== undefined && String(row.family_ID) !== '');
    expect(children.length, 'the catalogue has variants to check').toBeGreaterThan(0);

    for (const child of children) {
      expect(String(child.productURL), `variant ${child.sku}`).toMatch(/^https?:\/\//);
    }
  });
});

/*
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * The shape of the file the channel reads: newline-delimited JSON, one product per line, every
 * line parseable, every product identifiable. A single unparseable line makes the whole file
 * unreadable to the channel, so this is checked before anything about the contents.
 */

import { test, expect } from '@playwright/test';
import WebshopNLApi, { FeedRow } from 'Services/WebshopNLApi';

const api = new WebshopNLApi();

let rows: FeedRow[];
let ndjson: string;

test.beforeAll(async ({}, testInfo) => {
  const baseURL = testInfo.project.use.baseURL!;
  const result = await api.runFeedCycle(baseURL);
  rows = result.rows;
  ndjson = result.ndjson;
});

test.describe('Feed structure', () => {
  test('every line of the file is one JSON object', () => {
    const lines = ndjson.split('\n').filter((line) => line.trim() !== '');
    expect(lines.length, 'the feed has lines').toBeGreaterThan(0);
    // parseFeed throws on the first line that is not JSON, so reaching here means they all are.
    expect(rows.length, 'every non-empty line parsed').toBe(lines.length);
    for (const row of rows) {
      expect(Array.isArray(row), 'a feed line is an object, not an array').toBe(false);
    }
  });

  test('every product carries an ID and a sku', () => {
    for (const row of rows) {
      expect(row.ID, `a product has an ID: ${JSON.stringify(row).slice(0, 120)}`).toBeTruthy();
      expect(row.sku, `product ${row.ID} has a sku`).toBeTruthy();
    }
  });

  test('no ID appears twice', () => {
    const ids = rows.map((row) => String(row.ID));
    const seen = new Set(ids);
    const duplicates = ids.filter((id, index) => ids.indexOf(id) !== index);
    expect(seen.size, `duplicate IDs: ${[...new Set(duplicates)].slice(0, 10).join(', ')}`).toBe(ids.length);
  });

  test('every product has a name and an availability the channel knows', () => {
    const allowed = ['in stock', 'out of stock'];
    for (const row of rows) {
      expect(row.name, `product ${row.ID} has a name`).toBeTruthy();
      expect(allowed, `product ${row.ID} availability "${row.availability}"`).toContain(row.availability);
    }
  });
});

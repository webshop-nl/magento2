/*
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * The REST schema of the whole shop. Magento reflects the docblocks of every service in webapi.xml
 * when it is requested, so one type it cannot resolve (a short class name, a bare "array") turns
 * /rest/all/schema into a 500 for every module in the shop, while the module's own endpoints keep
 * working. The schema lists only the routes the caller may use, hence the admin token.
 */
import { test, expect } from '@playwright/test';

/** One route of this module, as webapi.xml spells it without the version prefix's parameters. */
const MODULE_ROUTE = '/V1/webshopnl/info';

test.describe('REST schema', () => {
  test('GET /rest/all/schema lists this module without an error', async () => {
    const baseURL = process.env.BASE_URL || 'https://magento.test/';
    const response = await fetch(new URL('rest/all/schema?services=all', baseURL).toString(), {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${process.env.admin_token}`,
        'Accept': 'application/json',
      },
    });

    expect(response.status).toBe(200);

    const body = await response.json();
    expect(body).toHaveProperty('paths');
    expect(Object.keys(body.paths)).toContain(MODULE_ROUTE);
  });
});

/*
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

import { type FullConfig } from '@playwright/test';
import WebshopNLApi from './support/services/WebshopNLApi';

/** The store view the multi-store spec compares against the default one. */
export const SECOND_STORE_CODE = 'e2e_second';

async function globalSetup(config: FullConfig) {
  process.env['NODE_TLS_REJECT_UNAUTHORIZED'] = '0';
  const { baseURL } = config.projects[0].use;
  if (!baseURL) {
    throw new Error('No baseURL configured: set BASE_URL');
  }

  await getAdminToken(baseURL);
  prepareStore();
  prepareSecondStoreView();
}

const getAdminToken = async (baseURL: string) => {
  const username = process.env.MAGENTO_ADMIN_USER || 'exampleuser';
  const password = process.env.MAGENTO_ADMIN_PASS || 'examplepassword123';

  console.log('Requesting admin token from "' + baseURL + '"...');

  const response = await fetch(baseURL + 'rest/all/V1/integration/admin/token', {
    method: 'POST',
    headers: {
      'accept': 'application/json',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ username, password }),
  });

  process.env.admin_token = await response.json();
  console.log('Admin token acquired.');
};

/**
 * Everything about the shop that must be true before the first request. Secret keys in admin URLs
 * and the admin captcha break the admin specs; the configure script switches them off too, but a
 * box that was provisioned differently gets the same treatment here.
 */
const prepareStore = () => {
  if (!process.env.MAGENTO_CONTAINER) {
    return;
  }
  const api = new WebshopNLApi();
  api.setBaseConfig({
    'admin/security/use_form_key': 0,
    'admin/captcha/enable': 0,
  });
  api.flushAllCaches();
};

/**
 * The second store view the multi-store spec needs, created before the first request.
 *
 * A store view added halfway through a run is not in the store list a running PHP process already
 * read, and Magento's resolver falls back to the default store from that stale list; flushing and
 * retrying loses that race. Created here it is in every list from the start. The shops this suite
 * runs against ship a single store view, so without it the whole multi-store spec skips and the
 * defect it covers is not covered anywhere.
 */
const prepareSecondStoreView = () => {
  if (!process.env.MAGENTO_CONTAINER) {
    return;
  }
  const api = new WebshopNLApi();
  try {
    const id = api.createStoreView(SECOND_STORE_CODE, 'E2E Second');
    // A new store view has no index tables of its own, and a feed run reads them.
    api.reindex(['catalog_category_product', 'catalog_product_price']);
    api.flushAllCaches();
    console.log(`Second store view ready before the run: ${SECOND_STORE_CODE} (id ${id}).`);
  } catch (error) {
    // Named, not swallowed: the multi-store spec would otherwise skip with no hint that its
    // fixture was never created.
    console.warn('Could not create the second store view:', error);
  }
};

export default globalSetup;

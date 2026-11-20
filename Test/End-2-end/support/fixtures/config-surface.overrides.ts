/*
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

import type { SurfaceOverrides } from 'Helpers/ConfigSurface';

/**
 * Fields the generic "everything on" chooser must not set blind. Every entry carries its reason;
 * this file is reviewed like code, it is not a place to hide a field that fails.
 */
export const CONFIG_SURFACE_OVERRIDES: SurfaceOverrides = {
  'webshopnl/general/enable': {
    value: '1',
    reason: 'the module stays enabled for the rest of the run',
  },
  'webshopnl/feed/enable': {
    value: '1',
    reason: 'the feed stays enabled so the work check has something to generate',
  },
  'webshopnl_general/general/integration_token': {
    value: null,
    reason: 'rendered by a block that shows the Magento integration token; there is nothing to set',
  },
  'webshopnl/feed/filter_category': {
    value: '0',
    reason: 'an arbitrary category restriction empties the feed; covered by the feed specs',
  },
  'webshopnl/feed/filter_visibility': {
    value: '0',
    reason: 'picking one visibility leaves a feed of only those products; the work check needs a full one',
  },
  'webshopnl/feed/filter_stock': {
    value: '0',
    reason: 'excluding out of stock is what product-selection.spec.ts proves; here it would shrink the feed for no reason',
  },
  'webshopnl/feed/extra_fields': {
    value: null,
    reason: 'a dynamic-rows table of attribute mappings the generic filler cannot build',
  },
  'webshopnl/feed/shipping': {
    value: null,
    reason: 'a dynamic-rows price band table; a text value in a price band makes the band meaningless',
  },
  'webshopnl/feed/custom_filters_data': {
    value: null,
    reason: 'a dynamic-rows condition table the generic filler cannot build',
  },
};

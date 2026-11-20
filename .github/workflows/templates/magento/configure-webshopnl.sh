#!/bin/bash
#
# Copyright Magmodules.eu. All rights reserved.
# See COPYING.txt for license details.
#

set -e

# Enable the WebshopNL module and its feed
bin/magento config:set webshopnl/general/enable 1
bin/magento config:set webshopnl/feed/enable 1
bin/magento config:set webshopnl/feed/filename webshop.ndjson

# Admin pages are opened by URL in the admin specs; the secret key would redirect them to the dashboard.
bin/magento config:set admin/security/use_form_key 0

# Disable 2FA if present
if grep -q Magento_TwoFactorAuth "app/etc/config.php"; then
    bin/magento module:disable Magento_TwoFactorAuth -f
fi

# Flush cache
bin/magento cache:flush

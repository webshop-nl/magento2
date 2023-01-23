<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Model\Config\System;

use Magento\Tax\Model\Config as TaxConfig;
use WebshopNL\Connect\Api\Config\System\OrderInterface;

/**
 * Order provider class
 */
class OrderRepository extends FeedRepository implements OrderInterface
{

    /**
     * @inheritDoc
     */
    public function isOrderEnabled(int $storeId = null): bool
    {
        return $this->isSetFlag(self::XML_PATH_ORDER_ENABLE, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getDefaultShippingMethod(int $storeId = null): ?string
    {
        return $this->getStoreValue(self::XML_PATH_SHIPPING_METHOD, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getFallbackShippingMethod(int $storeId = null): ?string
    {
        $fallback = $this->getStoreValue(self::XML_PATH_SHIPPING_METHOD_FALLBACK, $storeId);
        return !empty($fallback) ? $fallback : 'flatrate_flatrate';
    }

    /**
     * @inheritDoc
     */
    public function createCustomerOnImport(int $storeId = null): bool
    {
        return $this->isSetFlag(self::XML_PATH_IMPORT_CUSTOMER, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function customerGroupForOrderImport(int $storeId = null): ?string
    {
        return $this->getStoreValue(self::XML_PATH_CUSTOMER_GROUP_ID, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function separateHouseNumber(int $storeId = null): bool
    {
        return $this->isSetFlag(self::XML_PATH_SEPARATE_HOUSE_NUMBER, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerStreetLines(int $storeId): int
    {
        return (int)$this->getStoreValue('customer/address/street_lines', (int)$storeId);
    }

    /**
     * @inheritDoc
     */
    public function getNeedsTaxCalculation(string $type, int $storeId = null): bool
    {
        if ($type == 'shipping') {
            return $this->isSetFlag(TaxConfig::CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX, (int)$storeId);
        } else {
            return $this->isSetFlag(TaxConfig::CONFIG_XML_PATH_PRICE_INCLUDES_TAX, (int)$storeId);
        }
    }

    /**
     * @inheritDoc
     */
    public function getTaxClassShipping(int $storeId = null): string
    {
        return (string)$this->getStoreValue(TaxConfig::CONFIG_XML_PATH_SHIPPING_TAX_CLASS, (int)$storeId);
    }

    /**
     * @inheritDoc
     */
    public function sendOrderEmailOnImport(int $storeId = null): bool
    {
        return $this->isSetFlag(self::XML_PATH_SEND_ORDER_EMAIL, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function sendInvoiceEmailOnImport(int $storeId = null): bool
    {
        return $this->isSetFlag(self::XML_PATH_SEND_INVOICE, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function logOrderImport(int $storeId = null): bool
    {
        return $this->isSetFlag(self::XML_PATH_LOG, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getLastOrderImport(): string
    {
        return (string)$this->getStoreValue(self::LAST_ORDER_IMPORT);
    }
}

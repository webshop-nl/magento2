<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Api\Config\System;

/**
 * Order group interface
 */
interface OrderInterface extends FeedInterface
{

    /** General Group */
    public const XML_PATH_ORDER_ENABLE = 'webshopnl/general/enable';

    /** Order Group */
    public const XML_PATH_SHIPPING_METHOD = 'webshopnl/order/shipping_method';
    public const XML_PATH_SHIPPING_METHOD_FALLBACK = 'webshopnl/order/shipping_method_fallback';
    public const XML_PATH_IMPORT_CUSTOMER = 'webshopnl/order/import_customer';
    public const XML_PATH_CUSTOMER_GROUP_ID = 'webshopnl/order/customers_group';
    public const XML_PATH_SEPARATE_HOUSE_NUMBER = 'webshopnl/order/separate_house_number';
    public const XML_PATH_SEND_ORDER_EMAIL = 'webshopnl/order/order_email';
    public const XML_PATH_SEND_INVOICE = 'webshopnl/order/invoice_order_email';
    public const XML_PATH_LOG = 'webshopnl/order/log';
    public const LAST_ORDER_IMPORT = 'webshopnl/order/last_order_import';

    /**
     * Enabled flag for Order Import.
     *
     * @param null|int $storeId
     *
     * @return bool
     */
    public function isOrderEnabled(int $storeId = null): bool;

    /**
     * Returns shipping method code that will be forces to use on order import.
     *
     * @param null|int $storeId
     *
     * @return null|string
     */
    public function getDefaultShippingMethod(int $storeId = null): ?string;

    /**
     * Returns shipping method that should be used in case of no matched methods
     * are available. If not set 'flatrate_flatrate' is returned
     *
     * @param null|int $storeId
     *
     * @return string
     */
    public function getFallbackShippingMethod(int $storeId = null): ?string;

    /**
     * Create customer on order import
     *
     * @param null|int $storeId
     *
     * @return bool
     */
    public function createCustomerOnImport(int $storeId = null): bool;

    /**
     * Group customers should be added on order import
     *
     * @param null|int $storeId
     *
     * @return string
     */
    public function customerGroupForOrderImport(int $storeId = null): ?string;

    /**
     * Separate house number into 'streets'. Option is used when second street
     * is used as house number field.
     *
     * @param null|int $storeId
     *
     * @return bool
     */
    public function separateHouseNumber(int $storeId = null): bool;

    /**
     * The number of lines in a street address is configurable via 'customer/address/street_lines'.
     * To avoid a mismatch we'll concatenate additional lines so that they fit within the configured path.
     *
     * @param int $storeId
     * @return int
     */
    public function getCustomerStreetLines(int $storeId): int;

    /**
     * Check whether tax needs to be calculated
     *
     * @param string $type
     * @param int|null $storeId
     *
     * @return bool
     */
    public function getNeedsTaxCalculation(string $type, int $storeId = null): bool;

    /**
     * Tax Class ID used for shipping
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getTaxClassShipping(int $storeId = null): string;

    /**
     * Send invoice email to customer after order import
     *
     * @param null|int $storeId
     *
     * @return bool
     */
    public function sendInvoiceEmailOnImport(int $storeId = null): bool;

    /**
     * Send invoice email to customer after order import
     *
     * @param null|int $storeId
     *
     * @return bool
     */
    public function sendOrderEmailOnImport(int $storeId = null): bool;

    /**
     * Log order import
     *
     * @param null|int $storeId
     *
     * @return bool
     */
    public function logOrderImport(int $storeId = null): bool;

    /**
     * @return string
     */
    public function getLastOrderImport(): string;
}

<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Api\Config\System;

/**
 * Feed group interface
 */
interface FeedInterface
{

    /** General Group */
    public const XPATH_FEED_ENABLED = 'webshopnl/feed/enable';
    public const XPATH_FEED_FILENAME = 'webshopnl/feed/filename';
    public const XPATH_FEED_RESULT = 'webshopnl/feeds/results';
    public const XPATH_CRON_FREQUENCY = 'webshopnl/feeds/cron_frequency';

    /** Product Data Group */
    public const XML_PATH_NAME_SOURCE = 'webshopnl/feed/name_attribute';
    public const XML_PATH_DESCRIPTION_SOURCE = 'webshopnl/feed/description_attribute';
    public const XML_PATH_DESCRIPTION_LONG_SOURCE = 'webshopnl/feed/description_long_attribute';
    public const XML_PATH_IMAGE_SOURCE = 'webshopnl/feed/image';
    public const XML_PATH_IMAGE_MAIN = 'webshopnl/feed/main_image';
    public const XML_PATH_EAN_SOURCE = 'webshopnl/feed/ean_attribute';
    public const XML_PATH_BRAND_SOURCE = 'webshopnl/feed/brand_attribute';
    public const XML_PATH_COLOR_SOURCE = 'webshopnl/feed/color_attribute';
    public const XML_PATH_MATERIAL_SOURCE = 'webshopnl/feed/material_attribute';
    public const XML_PATH_SIZE_SOURCE = 'webshopnl/feed/size_attribute';
    public const XML_PATH_GENDER_SOURCE = 'webshopnl/feed/gender_attribute';
    public const XML_PATH_EXTRA_INFO = 'webshopnl/feed/extra_info';
    public const XPATH_CATEGORY_SOURCE = 'webshopnl/feed/category_source';
    public const XPATH_CATEGORY_ATTRIBUTE = 'webshopnl/feed/category_attribute';
    public const XPATH_CATEGORY_CUSTOM = 'webshopnl/feed/category_custom';
    public const XPATH_DELIVERY_SOURCE = 'webshopnl/feed/delivery_source';
    public const XPATH_DELIVERY_ATTRIBUTE = 'webshopnl/feed/delivery_attribute';
    public const XPATH_DELIVERY_IN_STOCK = 'webshopnl/feed/delivery_in_stock';
    public const XPATH_DELIVERY_OUT_OF_STOCK = 'webshopnl/feed/delivery_out_of_stock';

    /** Product Types Group */
    public const XML_PATH_CONFIGURABLE = 'webshopnl/feed/configurable';
    public const XML_PATH_CONFIGURABLE_LINK = 'webshopnl/feed/configurable_link';
    public const XML_PATH_CONFIGURABLE_IMAGE = 'webshopnl/feed/configurable_image';
    public const XML_PATH_CONFIGURABLE_PARENT_ATTRIBUTES = 'webshopnl/feed/configurable_parent_attributes';
    public const XML_PATH_CONFIGURABLE_NON_VISIBLE = 'webshopnl/feed/configurable_non_visible';
    public const XML_PATH_BUNDLE = 'webshopnl/feed/bundle';
    public const XML_PATH_BUNDLE_LINK = 'webshopnl/feed/bundle_link';
    public const XML_PATH_BUNDLE_IMAGE = 'webshopnl/feed/bundle_image';
    public const XML_PATH_BUNDLE_PARENT_ATTRIBUTES = 'webshopnl/feed/bundle_parent_attributes';
    public const XML_PATH_BUNDLE_NON_VISIBLE = 'webshopnl/feed/bundle_non_visible';
    public const XML_PATH_GROUPED = 'webshopnl/feed/grouped';
    public const XML_PATH_GROUPED_LINK = 'webshopnl/feed/grouped_link';
    public const XML_PATH_GROUPED_IMAGE = 'webshopnl/feed/grouped_image';
    public const XML_PATH_GROUPED_PARENT_PRICE = 'webshopnl/feed/grouped_parent_price';
    public const XML_PATH_GROUPED_PARENT_ATTRIBUTES = 'webshopnl/feed/grouped_parent_attributes';
    public const XML_PATH_GROUPED_NON_VISIBLE = 'webshopnl/feed/grouped_non_visible';

    /** Additional Configuration Group */
    public const XML_PATH_EXTRA_FIELDS = 'webshopnl/feed/extra_fields';
    public const XML_PATH_SHIPPING = 'webshopnl/feed/shipping';
    public const XML_PATH_UTM_STRING = 'webshopnl/feed/utm_string';

    /** Filter Options Group */
    public const XML_PATH_VISIBILITY = 'webshopnl/feed/filter_visibility';
    public const XML_PATH_VISIBILITY_OPTIONS = 'webshopnl/feed/filter_visibility_options';
    public const XML_PATH_CATEGORY_FILTER = 'webshopnl/feed/filter_category';
    public const XML_PATH_CATEGORY_FILTER_TYPE = 'webshopnl/feed/filter_type_category';
    public const XML_PATH_CATEGORY_IDS = 'webshopnl/feed/filter_category_ids';
    public const XML_PATH_STOCK = 'webshopnl/feed/filter_stock';
    public const XML_PATH_FILTERS = 'webshopnl/feed/custom_filters';
    public const XML_PATH_FILTERS_DATA = 'webshopnl/feed/custom_filters_data';

    /**
     * Check if feed generation is enabled
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isFeedEnabled(int $storeId = null): bool;

    /**
     * Return all enabled storeIds
     *
     * @return array
     */
    public function getAllEnabledStoreIds(): array;

    /**
     * Returns cron frequency expression
     *
     * @return string
     * @see \WebshopNL\Connect\Model\Config\System\Source\CronFrequency
     */
    public function getCronFrequency(): string;

    /**
     * Returns array of attributes
     *
     * @param int $storeId
     *
     * @return array
     */
    public function getAttributes(int $storeId): array;

    /**
     * Get Extra fields array
     *
     * @param int $storeId
     *
     * @return array
     */
    public function getExtraFields(int $storeId): array;

    /**
     * Get 'image' fields array
     *
     * @param int $storeId
     *
     * @return array
     */
    public function getImageAttributes(int $storeId): array;

    /**
     * Returns array of static fields
     *
     * @param int $storeId
     *
     * @return array
     */
    public function getStaticFields(int $storeId): array;

    /**
     * Get source for category feed entity
     *
     * @param int $storeId
     *
     * @return string
     * @see \WebshopNL\Connect\Model\Config\System\Source\CategorySource
     */
    public function getCategorySource(int $storeId): string;

    /**
     * Get custom field for category
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getCategoryCustomField(int $storeId): string;

    /**
     * Get product data filters
     *
     * @param int $storeId
     * @return array
     */
    public function getFilters(int $storeId): array;

    /**
     * Get 'configurable' products data behaviour
     *
     * @param int $storeId
     * @return array
     */
    public function getConfigProductsBehaviour(int $storeId): array;

    /**
     * Get 'bundle' products data behaviour
     *
     * @param int $storeId
     * @return array
     */
    public function getBundleProductsBehaviour(int $storeId): array;

    /**
     * Get 'grouped' products data behaviour
     *
     * @param int $storeId
     * @return array
     */
    public function getGroupedProductsBehaviour(int $storeId): array;

    /**
     * Get result of feed generation
     *
     * @param int $storeId
     * @return string
     */
    public function getFeedGenerationResult(int $storeId): string;

    /**
     * Set result of feed generation
     *
     * @param int $storeId
     * @param string $msg
     */
    public function setFeedGenerationResult(int $storeId, string $msg): void;

    /**
     * Get filename of feed
     *
     * @param int|null $storeId
     * @return string
     */
    public function getFileName(int $storeId = null): string;
}

<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Api\Config;

use Magento\Store\Api\Data\StoreInterface;

/**
 * Config repository interface
 */
interface RepositoryInterface extends System\OrderInterface
{

    public const EXTENSION_CODE = 'WebshopNL_Connect';
    public const XML_PATH_EXTENSION_VERSION = 'webshopnl/general/version';
    public const XML_PATH_EXTENSION_ENABLE = 'webshopnl/general/enable';
    public const XML_PATH_DEBUG = 'webshopnl/general/debug';
    public const MODULE_SUPPORT_LINK = 'https://www.magmodules.eu/help/%s';

    /**
     * Get extension version
     *
     * @return string
     */
    public function getExtensionVersion(): string;

    /**
     * Get extension code
     *
     * @return string
     */
    public function getExtensionCode(): string;

    /**
     * Get Magento Version
     *
     * @return string
     */
    public function getMagentoVersion(): string;

    /**
     * Check if module is enabled
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isEnabled(int $storeId = null): bool;

    /**
     * Check if debug mode is enabled
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isDebugMode(int $storeId = null): bool;

    /**
     * Get store
     *
     * @param null $storeId
     * @return StoreInterface
     */
    public function getStore($storeId = null): StoreInterface;

    /**
     * Support link for extension.
     *
     * @return string
     */
    public function getSupportLink(): string;
}

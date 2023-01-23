<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Model\Config\System;

use Exception;
use Magento\Config\Model\ResourceModel\Config as ConfigData;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory as ConfigDataCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Config repository class
 */
class BaseRepository
{

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var ProductMetadataInterface
     */
    protected $metadata;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var ConfigDataCollectionFactory
     */
    private $configDataCollectionFactory;
    /**
     * @var ConfigData
     */
    private $config;

    /**
     * Repository constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigDataCollectionFactory $configDataCollectionFactory
     * @param ConfigData $config
     * @param ProductMetadataInterface $metadata
     * @param Json $json
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ConfigDataCollectionFactory $configDataCollectionFactory,
        ConfigData $config,
        ProductMetadataInterface $metadata,
        Json $json
    ) {
        $this->storeManager = $storeManager;
        $this->configDataCollectionFactory = $configDataCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
        $this->metadata = $metadata;
        $this->json = $json;
    }

    /**
     * Get config value flag
     *
     * @param string $path
     * @param int|null $storeId
     * @param string|null $scope
     *
     * @return bool
     */
    protected function isSetFlag(string $path, int $storeId = null, string $scope = null): bool
    {
        if (empty($scope)) {
            $scope = ScopeInterface::SCOPE_STORE;
        }

        if (empty($storeId)) {
            $storeId = $this->getStore()->getId();
        }
        return $this->scopeConfig->isSetFlag($path, $scope, $storeId);
    }

    /**
     * @param $storeId
     * @return StoreInterface
     */
    public function getStore($storeId = null): StoreInterface
    {
        try {
            return $this->storeManager->getStore($storeId);
        } catch (Exception $e) {
            if ($store = $this->storeManager->getDefaultStoreView()) {
                return $store;
            }
        }
        $stores = $this->storeManager->getStores();
        return reset($stores);
    }

    /**
     * Retrieve config value array by path, storeId and scope
     *
     * @param string $path
     * @param int|null $storeId
     * @param string|null $scope
     *
     * @return array
     */
    protected function getStoreValueArray(string $path, int $storeId = null, string $scope = null): array
    {
        $value = $this->getStoreValue($path, (int)$storeId, $scope);

        if (empty($value)) {
            return [];
        }

        try {
            return $this->json->unserialize($value);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get Configuration data
     *
     * @param string $path
     * @param int|null $storeId
     * @param string|null $scope
     *
     * @return string
     */
    protected function getStoreValue(
        string $path,
        int $storeId = null,
        string $scope = null
    ): string {
        if (!$storeId) {
            $storeId = (int)$this->getStore()->getId();
        }
        $scope = $scope ?? ScopeInterface::SCOPE_STORE;
        return (string)$this->scopeConfig->getValue($path, $scope, (int)$storeId);
    }

    /**
     * Return uncached store config data
     *
     * @param string $path
     * @param int|null $storeId
     *
     * @return string
     */
    protected function getUncachedStoreValue(string $path, int $storeId = null): string
    {
        $collection = $this->configDataCollectionFactory->create()
            ->addFieldToSelect('value')
            ->addFieldToFilter('path', $path);

        if ($storeId > 0) {
            $collection->addFieldToFilter('scope_id', $storeId);
            $collection->addFieldToFilter('scope', 'stores');
        } else {
            $collection->addFieldToFilter('scope_id', 0);
            $collection->addFieldToFilter('scope', 'default');
        }

        $collection->getSelect()->limit(1);

        return (string)$collection->getFirstItem()->getData('value');
    }

    /**
     * Set Store data
     *
     * @param string $value
     * @param string $key
     * @param int|null $storeId
     */
    protected function setConfigData(string $value, string $key, int $storeId = null): void
    {
        if ($storeId) {
            $this->config->saveConfig($key, $value, 'stores', $storeId);
        } else {
            $this->config->saveConfig($key, $value, 'default', 0);
        }
    }
}

<?php
/**
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Model\Webapi;

use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use WebshopNL\Connect\Api\Config\RepositoryInterface as ConfigRepository;
use WebshopNL\Connect\Api\Feed\RepositoryInterface as FeedRepository;
use WebshopNL\Connect\Api\Webapi\InfoInterface;

/**
 * Info model
 */
class Info implements InfoInterface
{
    /**
     * @var ConfigRepository
     */
    private $configRepository;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var FeedRepository
     */
    private $feedRepository;
    /**
     * @var string
     */
    private $storeUrl = '';

    /**
     * Info constructor.
     *
     * @param ConfigRepository $configRepository
     * @param StoreManagerInterface $storeManager
     * @param FeedRepository $feedRepository
     */
    public function __construct(
        ConfigRepository $configRepository,
        StoreManagerInterface $storeManager,
        FeedRepository $feedRepository
    ) {
        $this->configRepository = $configRepository;
        $this->storeManager = $storeManager;
        $this->feedRepository = $feedRepository;
    }

    /**
     * @inheritDoc
     */
    public function getInfo(): array
    {
        return [
            [
                'enabled' => $this->configRepository->isEnabled(),
                'module_version' => $this->configRepository->getExtensionVersion(),
                'magento_version' => $this->configRepository->getMagentoVersion(),
                'feed_url' => $this->getFeedUrls(),
                'order_post_url' => $this->getStoreUrl() . '/V1/webshopnl/order',
                'last_order_import' => $this->configRepository->getLastOrderImport(),
                'active_stores' => $this->getActiveStores()
            ]
        ];
    }

    /**
     * @return array
     */
    private function getFeedUrls(): array
    {
        $feedUrls = [];
        foreach ($this->storeManager->getStores() as $store) {
            $feedUrls[] = [
                'store' => $store->getId(),
                'url' => $this->feedRepository->getFeedLocation((int)$store->getId())['url']
            ];
        }
        return $feedUrls;
    }

    /**
     * @return string
     */
    private function getStoreUrl(): ?string
    {
        if (!$this->storeUrl) {
            try {
                $this->storeUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB);
            } catch (\Exception $exception) {
                $this->storeUrl = null;
            }
        }
        return $this->storeUrl;
    }

    /**
     * @return array
     */
    private function getActiveStores(): array
    {
        $storeIds = [];
        foreach ($this->storeManager->getStores() as $store) {
            if ($this->configRepository->isEnabled((int)$store->getId()) && $store->getIsActive()) {
                $storeIds[] = [
                    'integration' => $this->configRepository->isEnabled((int)$store->getId()),
                    'store_id' => (int)$store->getId(),
                    'store_name' => $store->getName()
                ];
            }
        }

        return $storeIds;
    }
}

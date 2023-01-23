<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Model\Cron;

use WebshopNL\Connect\Api\Config\System\FeedInterface as FeedConfigRepository;
use WebshopNL\Connect\Api\Feed\RepositoryInterface as FeedRepository;

/**
 * GenerateFeeds Cron Class
 */
class GenerateFeeds
{
    /**
     * @var FeedRepository
     */
    private $feedRepository;
    /**
     * @var FeedConfigRepository
     */
    private $feedConfigRepository;

    /**
     * GenerateFeeds constructor.
     * @param FeedRepository $feedRepository
     * @param FeedConfigRepository $feedConfigRepository
     */
    public function __construct(
        FeedRepository $feedRepository,
        FeedConfigRepository $feedConfigRepository
    ) {
        $this->feedRepository = $feedRepository;
        $this->feedConfigRepository = $feedConfigRepository;
    }

    /**
     * Execute: Run all TradeTracker Feed generation.
     */
    public function execute()
    {
        if (!$this->feedConfigRepository->getCronFrequency()) {
            return $this;
        }

        foreach ($this->feedConfigRepository->getAllEnabledStoreIds() as $storeId) {
            $this->feedRepository->generateAndSaveFeed(
                (int)$storeId,
                'cron'
            );
        }

        return $this;
    }
}

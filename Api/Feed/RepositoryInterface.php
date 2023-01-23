<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Api\Feed;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Feed repository interface
 */
interface RepositoryInterface
{

    public const DEFAULT_DIRECTORY = 'webshopnl';
    public const DEFAULT_DIRECTORY_PATH = 'pub/media/webshopnl';
    public const PREVIEW_URL = 'webshopnl/feed/preview';
    public const DOWNLOAD_URL = 'webshopnl/feed/download';
    public const GENERATE_URL = 'webshopnl/feed/generate';

    /**
     * Returns feed configuration data array for all stores
     *
     * @return array
     */
    public function getStoreData(): array;

    /**
     * Returns feed location data array for store
     *
     * @param int|null $storeId
     * @param null $type
     * @return array
     */
    public function getFeedLocation(int $storeId = null, $type = null): array;

    /**
     * Generate feed and write to file
     *
     * @param int $storeId
     * @param string $type
     * @return array
     */
    public function generateAndSaveFeed(int $storeId, string $type = 'manual'): array;

    /**
     * Generate feed with CLI output
     *
     * @param OutputInterface $output
     * @param array $storeIds
     * @return void
     */
    public function cliProcess(OutputInterface $output, array $storeIds = []): void;
}

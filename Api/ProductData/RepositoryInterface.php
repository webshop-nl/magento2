<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Api\ProductData;

/**
 * Product data repository interface
 */
interface RepositoryInterface
{

    /**
     * Get formatted product data
     *
     * @param int $storeId
     * @param array $entityIds
     * @param string $type
     * @return array
     */
    public function getProductData(int $storeId = 0, array $entityIds = [], string $type = 'manual'): array;

    /**
     * Collect all used product attributes
     *
     * @param int $storeId
     * @return array
     */
    public function getProductAttributes(int $storeId = 0): array;
}

<?php
/**
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Api\Webapi;

/**
 * Webhook to receive orders
 */
interface OrderInterface
{

    /**
     * Process order data
     *
     * @param int $storeId
     * @return array
     */
    public function processOrder(int $storeId): array;

    /**
     * Get order status by order id
     *
     * @param int $orderId
     * @return array
     */
    public function getStatus(int $orderId): array;
}

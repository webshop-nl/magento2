<?php
/**
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Api\Webapi;

/**
 * Webhook to retrieve module info
 */
interface InfoInterface
{

    /**
     * Get module info
     *
     * @api
     * @return array
     */
    public function getInfo(): array;
}

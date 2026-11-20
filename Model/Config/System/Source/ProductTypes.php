<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Model\Config\System\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Product Types Option Source model
 */
class ProductTypes implements OptionSourceInterface
{

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => '',
                'label' => __('Simple & Parent Products')
            ],
            [
                'value' => 'simple',
                'label' => __('Only Simple Products')
            ],
            [
                'value' => 'parent',
                'label' => __('Only Parent Products')
            ]
        ];
    }
}

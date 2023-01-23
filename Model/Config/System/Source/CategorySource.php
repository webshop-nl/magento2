<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WebshopNL\Connect\Model\Config\System\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Category Source Option Source model
 */
class CategorySource implements OptionSourceInterface
{

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => '', 'label' => __('Magento Category Tree')],
            ['value' => 'custom', 'label' => __('Custom Category Value')],
            ['value' => 'attribute', 'label' => __('Use Attribute')],
        ];
    }
}

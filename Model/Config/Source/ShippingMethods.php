<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Model\Config\Source;

use Magento\Shipping\Model\Config\Source\Allmethods;

/**
 * ShippingMethods Option Source model
 */
class ShippingMethods extends Allmethods
{

    /**
     * Return array of carriers.
     * If $isActiveOnlyFlag is set to true, will return only active carriers
     *
     * @param bool $isActiveOnlyFlag
     *
     * @return array
     */
    public function toOptionArray($isActiveOnlyFlag = false): array
    {
        $methods = parent::toOptionArray();
        if (isset($methods['Webshopnl']['value'])) {
            $methods['Webshopnl']['value'][] = [
                'value' => 'Webshopnl_custom',
                'label' => '[Webshopnl] Use Custom Logic'
            ];
        }
        return $methods;
    }
}

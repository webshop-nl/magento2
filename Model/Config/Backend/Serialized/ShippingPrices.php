<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WebshopNL\Connect\Model\Config\Backend\Serialized;

use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;

/**
 * Shipping Prices BeforeSave data reformat and unset
 */
class ShippingPrices extends ArraySerialized
{

    /**
     * Reformat Shipping Prices and unset unused.
     *
     * @return ArraySerialized
     */
    public function beforeSave()
    {
        $data = $this->getValue();
        if (is_array($data)) {
            foreach ($data as $key => $row) {
                if (isset($row['price'])) {
                    unset($data[$key]);
                    continue;
                }
                if (is_array($row)) {
                    $data[$key]['price_from'] = $this->formatPrice($row['price_from'] ?? '0');
                    $data[$key]['price_to'] = $this->formatPrice(!empty($row['price_to']) ? $row['price_to'] : '99999');
                    $data[$key]['price'] = $this->formatPrice($row['price']);
                }
            }
        }
        $this->setValue($data);
        return parent::beforeSave();
    }

    /**
     * @param string $price
     * @return string
     */
    private function formatPrice(string $price): string
    {
        $price = (float)str_replace(',', '.', $price);
        return number_format($price, 2, '.', '');
    }
}

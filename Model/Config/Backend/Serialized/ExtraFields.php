<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WebshopNL\Connect\Model\Config\Backend\Serialized;

use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;

/**
 * Extra Fields BeforeSave data reformat and unset
 */
class ExtraFields extends ArraySerialized
{

    /**
     * Unset unused fields.
     *
     * @return ArraySerialized
     */
    public function beforeSave()
    {
        $data = $this->getValue();
        if (is_array($data)) {
            foreach ($data as $key => $row) {
                if (empty($row['name']) || empty($row['attribute'])) {
                    unset($data[$key]);
                    continue;
                }
            }
        }
        $this->setValue($data);
        return parent::beforeSave();
    }
}

<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WebshopNL\Connect\Model\Config\Backend\Serialized;

use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;

/**
 * Filters BeforeSave data reformat and unset
 */
class Filters extends ArraySerialized
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
                if (empty($row['attribute']) || empty($row['condition'])) {
                    unset($data[$key]);
                    continue;
                }
                $data[$key]['value'] = trim($row['value']);
                if (($row['condition'] != 'empty') && ($row['condition'] != 'not-empty')) {
                    if (empty($row['value'])) {
                        unset($data[$key]);
                    }
                }
            }
        }
        $this->setValue($data);
        return parent::beforeSave();
    }
}

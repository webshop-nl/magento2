<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Block\Adminhtml\System\Config\Button;

use Magento\Backend\Block\Template;

/**
 * Button that opens the error log
 */
class ErrorCheck extends Template
{

    /**
     * @var string
     */
    protected $_template = 'WebshopNL_Connect::system/config/button/error.phtml';

    /**
     * @return string
     */
    public function getErrorCheckUrl(): string
    {
        return $this->getUrl('webshopnl/log/stream', ['type' => 'error']);
    }
}

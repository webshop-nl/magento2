<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Block\Adminhtml\System\Config\Button;

use Magento\Backend\Block\Template;

/**
 * Button that opens the debug log
 */
class DebugCheck extends Template
{

    /**
     * @var string
     */
    protected $_template = 'WebshopNL_Connect::system/config/button/debug.phtml';

    /**
     * @return string
     */
    public function getDebugCheckUrl(): string
    {
        return $this->getUrl('webshopnl/log/stream', ['type' => 'debug']);
    }
}

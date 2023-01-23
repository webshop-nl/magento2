<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WebshopNL\Connect\Model\Payment;

use Magento\Payment\Block\Info\Instructions;
use Magento\Payment\Model\Method\AbstractMethod;

class Webshopnl extends AbstractMethod
{

    public const CODE = 'webshopnl';

    /**
     * @var string
     */
    protected $_code = self::CODE;
    /**
     * @var bool
     */
    protected $_isOffline = true;
    /**
     * @var bool
     */
    protected $_canUseCheckout = false;
    /**
     * @var bool
     */
    protected $_canUseInternal = true;
    /**
     * @var string
     */
    protected $_infoBlockType = Instructions::class;
}

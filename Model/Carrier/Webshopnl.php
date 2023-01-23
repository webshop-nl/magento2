<?php
/**
 * Copyright ©  Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WebshopNL\Connect\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;

class Webshopnl extends AbstractCarrier implements CarrierInterface
{

    /**
     * Code of the carrier
     *
     * @var string
     */
    public const CODE = 'webshopnl';

    /**
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * @var ResultFactory
     */
    private $rateResultFactory;
    /**
     * @var MethodFactory
     */
    private $rateMethodFactory;
    /**
     * @var State
     */
    private $state;

    /**
     * Webshopnl constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param State $state
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        State $state,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->state = $state;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @param RateRequest $request
     *
     * @return bool|Result
     * @throws LocalizedException
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active') || !$this->isAreaRest()) {
            return false;
        }

        $method = $this->rateMethodFactory->create();
        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));
        $method->setPrice(0);
        $method->setCost(0);

        $result = $this->rateResultFactory->create();
        $result->append($method);

        return $result;
    }

    /**
     * @return bool
     */
    private function isAreaRest(): bool
    {
        try {
            return $this->state->getAreaCode() == \Magento\Framework\App\Area::AREA_WEBAPI_REST;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @return array
     */
    public function getAllowedMethods(): array
    {
        return ['webshopnl' => $this->getConfigData('name')];
    }
}

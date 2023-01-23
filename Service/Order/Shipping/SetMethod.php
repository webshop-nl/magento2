<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Service\Order\Shipping;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\RateRequestFactory;
use Magento\Shipping\Model\ShippingFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Tax\Model\Calculation as TaxCalculation;
use WebshopNL\Connect\Api\Config\RepositoryInterface as ConfigProvider;

/**
 * Set shipping method to quote
 */
class SetMethod
{

    /**
     * @var ConfigProvider
     */
    private $configProvider;
    /**
     * @var RateRequestFactory
     */
    private $rateRequestFactory;
    /**
     * @var ShippingFactory
     */
    private $shippingFactory;
    /**
     * @var TaxCalculation
     */
    private $taxCalculation;

    /**
     * GetMethod constructor.
     * @param ConfigProvider $configProvider
     * @param RateRequestFactory $rateRequestFactory
     * @param ShippingFactory $shippingFactory
     * @param TaxCalculation $taxCalculation
     */
    public function __construct(
        ConfigProvider $configProvider,
        RateRequestFactory $rateRequestFactory,
        ShippingFactory $shippingFactory,
        TaxCalculation $taxCalculation
    ) {
        $this->configProvider = $configProvider;
        $this->rateRequestFactory = $rateRequestFactory;
        $this->shippingFactory = $shippingFactory;
        $this->taxCalculation = $taxCalculation;
    }

    /**
     * @param Quote $quote
     * @param StoreInterface $store
     * @param int $itemCount
     * @param array $orderData
     * @return Quote
     * @throws LocalizedException
     */
    public function execute(Quote $quote, StoreInterface $store, int $itemCount, array $orderData): Quote
    {
        $price = $this->getShippingPrice($quote, $orderData, $store);
        $shippingMethod = $this->getShippingMethod($quote, $store, $itemCount, $price);
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod($shippingMethod);

        foreach ($shippingAddress->getShippingRatesCollection() as $rate) {
            $rate->setPrice($price);
            $rate->setCost($price);
        }

        return $quote;
    }

    /**
     * @param Quote $quote
     * @param array $orderData
     * @param StoreInterface $store
     * @return float
     */
    public function getShippingPrice(Quote $quote, array $orderData, StoreInterface $store): float
    {
        $taxCalculation = $this->configProvider->getNeedsTaxCalculation('shipping', (int)$store->getId());
        $shippingPriceCal = (float)$orderData['shipping_method']['cost'];

        if (empty($taxCalculation)) {
            $shippingAddress = $quote->getShippingAddress();
            $billingAddress = $quote->getBillingAddress();
            $taxRateId = $this->configProvider->getTaxClassShipping((int)$store->getId());
            $request = $this->taxCalculation->getRateRequest($shippingAddress, $billingAddress, null, $store);
            $percent = $this->taxCalculation->getRate($request->setData('product_tax_class_id', $taxRateId));
            $shippingPriceCal = ($orderData['shipping_method']['cost'] / (100 + $percent) * 100);
        }

        return $shippingPriceCal;
    }

    /**
     * @param Quote $quote
     * @param StoreInterface $store
     * @param int $itemCount
     * @param float $price
     * @return string
     * @throws LocalizedException
     */
    public function getShippingMethod(
        Quote $quote,
        StoreInterface $store,
        int $itemCount,
        float $price
    ): string {
        $shippingMethod = $this->configProvider->getDefaultShippingMethod((int)$store->getId());
        $shippingMethodFallback = $this->configProvider->getFallbackShippingMethod((int)$store->getId());

        $destCountryId = $quote->getShippingAddress()->getCountryId();
        $destPostcode = $quote->getShippingAddress()->getPostcode();
        $total = $quote->getGrandTotal();

        $request = $this->rateRequestFactory->create();
        $request->setAllItems($quote->getAllItems());
        $request->setDestCountryId($destCountryId);
        $request->setDestPostcode($destPostcode);
        $request->setPackageValue($total);
        $request->setPackageValueWithDiscount($total);
        $request->setPackageQty($itemCount);
        $request->setStoreId((int)$store->getId());
        $request->setWebsiteId($store->getWebsiteId());
        $request->setBaseCurrency($store->getBaseCurrency());
        $request->setPackageCurrency($store->getCurrentCurrency());
        $request->setLimitCarrier('');
        $request->setBaseSubtotalInclTax($total);
        $request->setFreeShipping($price <= 0);

        if (!empty($quote->getShippingAddress()->getWeight())) {
            $request->setPackageWeight($quote->getShippingAddress()->getWeight());
        }

        $shipping = $this->shippingFactory->create();
        $result = $shipping->collectRates($request)->getResult();

        if ($result) {
            $shippingRates = $result->getAllRates();
            foreach ($shippingRates as $shippingRate) {
                $method = $shippingRate->getCarrier() . '_' . $shippingRate->getMethod();
                if ($method == $shippingMethod) {
                    return $shippingMethod;
                }
            }
        }

        return $shippingMethodFallback;
    }
}

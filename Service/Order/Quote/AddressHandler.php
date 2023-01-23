<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Service\Order\Quote;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use WebshopNL\Connect\Api\Config\RepositoryInterface as ConfigProvider;

/**
 * Service class to get formatted addresses by Webshopnl Address Array
 */
class AddressHandler
{
    private const PATTERN_NAME = '/(?:[\p{L}\p{M}\,\-\_\.\'’`\s\d]){1,255}+/u';

    /**
     * @var ConfigProvider
     */
    private $configProvider;
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;
    /**
     * @var AddressInterfaceFactory
     */
    private $addressFactory;
    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * AddressHandler constructor.
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressInterfaceFactory $addressFactory
     * @param RegionFactory $regionFactory
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory $addressFactory,
        RegionFactory $regionFactory,
        ConfigProvider $configProvider
    ) {
        $this->addressRepository = $addressRepository;
        $this->addressFactory = $addressFactory;
        $this->regionFactory = $regionFactory;
        $this->configProvider = $configProvider;
    }

    /**
     * Add Billing and Shipping address data to Quote
     *
     * @param Quote $quote
     * @param array $orderData
     *
     * @return Quote
     * @throws LocalizedException
     */
    public function addAddressData(Quote $quote, array $orderData): Quote
    {
        $quote->getBillingAddress()->addData(
            $this->getAddressData('billing', $orderData, $quote)
        );

        $quote->getShippingAddress()->addData(
            $this->getAddressData('shipping', $orderData, $quote)
        );

        return $quote;
    }

    /**
     * Format address data
     *
     * @param string $type
     * @param array $orderData
     * @param Quote $quote
     *
     * @return array
     * @throws LocalizedException
     */
    private function getAddressData(string $type, array $orderData, Quote $quote): array
    {
        $storeId = $quote->getStoreId();
        $customerId = $quote->getCustomerId();

        if ($type == 'billing') {
            $address = $orderData['billing_address'];
        } else {
            $address = $orderData['delivery_address'];
        }

        $telephone = '000';
        if (!empty($orderData['delivery_address']['phone'])) {
            $telephone = $orderData['delivery_address']['phone'];
        }
        if (!empty($orderData['delivery_address']['mobile'])) {
            $telephone = $orderData['delivery_address']['mobile'];
        }

        $email = $this->cleanEmail($address['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = $this->cleanEmail($orderData['customer']['email']);
        }

        $addressData = [
            'customer_id' => $customerId,
            'company' => $address['company'] ?? '-',
            'firstname' => $this->validateName($address['first_name']),
            'lastname' => $this->validateName($address['surname']),
            'street' => $address['street_name'],
            'city' => $address['city'],
            'country_id' => $address['country_code'],
            'region' => !empty($address['state_code'])
                ? $this->getRegionId($address['state_code'], $address['country_code'])
                : null,
            'postcode' => $address['zip_code'],
            'telephone' => $telephone,
            'vat_id' => !empty($address['vat_id']) ? $address['vat_id'] : null,
            'email' => $email
        ];

        if ($this->configProvider->createCustomerOnImport((int)$storeId)) {
            $this->saveAddress($addressData, $customerId, $type);
        }

        return $addressData;
    }

    /**
     * Removed unwanted characters from email.
     * Some Marketplaces add ":" to email what can cause import to fail.
     *
     * @param string $email
     *
     * @return string
     */
    private function cleanEmail(string $email): string
    {
        return str_replace([':'], '', $email);
    }

    /**
     * @param string $nameValue
     * @return string
     */
    private function validateName(string $nameValue): string
    {
        preg_match_all(self::PATTERN_NAME, $nameValue, $matches);
        return implode($matches[0]);
    }

    /**
     * @param string $code
     * @param string $countryId
     * @return mixed
     */
    private function getRegionId(string $code, string $countryId)
    {
        $region = $this->regionFactory->create();
        return $region->loadByCode($code, $countryId)->getId();
    }

    /**
     * Save customer address
     *
     * @param array $addressData
     * @param int $customerId
     * @param string $type
     *
     * @throws LocalizedException
     */
    private function saveAddress(array $addressData, int $customerId, string $type): void
    {
        $address = $this->addressFactory->create();
        $address->setCustomerId($customerId)
            ->setCompany($addressData['company'])
            ->setFirstname($addressData['firstname'])
            ->setLastname($addressData['lastname'])
            ->setStreet(explode("\n", (string)$addressData['street']))
            ->setCity($addressData['city'])
            ->setCountryId($addressData['country_id'])
            ->setRegionId($addressData['region'])
            ->setPostcode($addressData['postcode'])
            ->setVatId($addressData['vat_id'])
            ->setTelephone($addressData['telephone']);

        if ($type == 'billing') {
            $address->setIsDefaultBilling('1');
        } else {
            $address->setIsDefaultShipping('1');
        }

        $this->addressRepository->save($address);
    }

    /**
     * Format address lines based on 'separate house-number' and on the number of streert lines there are available.
     * This number is configurable via 'customer/address/street_lines'.
     *
     * @param array $address
     * @param int $storeId
     *
     * @return string
     */
    private function getStreet(array $address, int $storeId): string
    {
        $separateHouseNumber = $this->configProvider->separateHouseNumber((int)$storeId);
        $numberOfStreetLines = $this->configProvider->getCustomerStreetLines((int)$storeId);

        if ($separateHouseNumber || empty($address['address_line_1'])) {
            $street[] = $address['street'];
            $street[] = $address['house_number'];
            $street[] = $address['house_number_ext'];
        } else {
            $street[] = $address['address_line_1'];
            $street[] = $address['address_line_2'];
            $street[] = null;
        }

        if ($numberOfStreetLines == 1) {
            $street = [trim(implode(' ', $street))];
        }

        if ($numberOfStreetLines == 2) {
            $street = [$street[0], trim($street[1] . ' ' . $street[2])];
        }

        return trim(implode("\n", $street));
    }
}

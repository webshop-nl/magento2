<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Service\Order\Quote;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote;
use WebshopNL\Connect\Api\Config\RepositoryInterface as ConfigProvider;

/**
 * Service class to assign customer to quote
 */
class CustomerHandler
{

    private const PATTERN_NAME = '/(?:[\p{L}\p{M}\,\-\_\.\'’`\s\d]){1,255}+/u';

    /**
     * @var ConfigProvider
     */
    private $configProvider;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * CustomerHandler constructor.
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerInterfaceFactory $customerFactory
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerInterfaceFactory $customerFactory,
        ConfigProvider $configProvider
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->configProvider = $configProvider;
    }

    /**
     * Assign customer / Guest to Quote
     *
     * @param Quote $quote
     * @param array $orderData
     *
     * @return Quote
     * @throws NoSuchEntityException
     * @throws InputException
     * @throws LocalizedException
     * @throws InputMismatchException
     */
    public function assignCustomer(Quote $quote, array $orderData): Quote
    {
        $websiteId = $quote->getStore()->getWebsiteId();
        $storeId = $quote->getStoreId();
        $email = $this->cleanEmail($orderData['delivery_address']['email']);

        if (!$this->configProvider->createCustomerOnImport((int)$storeId)) {
            $quote->setCustomerId(0);
            $quote->setCustomerEmail($email);
            $quote->setCustomerFirstname($this->validateName($orderData['delivery_address']['first_name']));
            $quote->setCustomerLastname($this->validateName($orderData['delivery_address']['surname']));
            $quote->setCustomerIsGuest(1);
            $quote->setCustomerGroupId(GroupInterface::NOT_LOGGED_IN_ID);
            $quote->setCheckoutMethod(CartManagementInterface::METHOD_GUEST);
            return $quote;
        }

        try {
            $customer = $this->customerRepository->get($email, $websiteId);
        } catch (NoSuchEntityException $exception) {
            $customer = $this->customerFactory->create();
            $customer->setWebsiteId($websiteId);
            $customer->setFirstname($this->validateName($orderData['delivery_address']['first_name']));
            $customer->setLastname($this->validateName($orderData['delivery_address']['surname']));
            $customer->setEmail($email);
            $customer->setGroupId($this->configProvider->customerGroupForOrderImport((int)$storeId));
            $this->customerRepository->save($customer);
            $customer = $this->customerRepository->get($email, $websiteId);
        }

        $quote->assignCustomer($customer);
        $quote->setCustomerIsGuest(0);

        return $quote;
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
}

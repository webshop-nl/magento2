<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Service\Order\Items;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote\Item;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Tax\Model\Calculation as TaxCalculation;
use WebshopNL\Connect\Api\Config\RepositoryInterface as ConfigProvider;
use WebshopNL\Connect\Exceptions\CouldNotImportOrder;

/**
 * Add items to quote
 */
class Add
{

    /**
     * Exception messages
     */
    public const PRODUCT_NOT_FOUND_EXCEPTION = 'Product "%1" not found in catalog (ID: %2)';
    public const PRODUCT_EXCEPTION = 'Product "%1" => %2';

    /**
     * @var ConfigProvider
     */
    private $configProvider;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var TaxCalculation
     */
    private $taxCalculation;
    /**
     * @var Item
     */
    private $itemResourceModel;

    /**
     * @param ConfigProvider $configProvider
     * @param ProductRepositoryInterface $productRepository
     * @param TaxCalculation $taxCalculation
     * @param Item $itemResourceModel
     */
    public function __construct(
        ConfigProvider $configProvider,
        ProductRepositoryInterface $productRepository,
        TaxCalculation $taxCalculation,
        Item $itemResourceModel
    ) {
        $this->configProvider = $configProvider;
        $this->productRepository = $productRepository;
        $this->taxCalculation = $taxCalculation;
        $this->itemResourceModel = $itemResourceModel;
    }

    /**
     * Add items to Quote by OrderData array and returns qty
     *
     * @param Quote $quote
     * @param array $data
     * @param StoreInterface $store
     * @return int
     * @throws CouldNotImportOrder
     */
    public function execute(Quote $quote, array $data, StoreInterface $store): int
    {
        $qty = 0;
        try {
            foreach ($data['items']['products'] as $item) {
                $product = $this->getProductById((int)$item['remote_id'], (int)$store->getStoreId());
                $price = $this->getProductPrice($item, $product, $store, $quote);
                $product = $this->setProductData($product, $price);

                switch ($product->getTypeId()) {
                    case 'grouped':
                    case 'bundle':
                        throw new CouldNotImportOrder(
                            __('Import %1 products is not possible', $product->getTypeId())
                        );
                    default:
                        $addedItem = $quote->addProduct($product, (int)$item['quantity']);
                }

                if (is_string($addedItem)) {
                    throw new CouldNotImportOrder(__($addedItem));
                }

                $addedItem->setOriginalCustomPrice($price);
                $this->itemResourceModel->save($addedItem);
                $qty += (int)$item['quantity'];
            }
        } catch (Exception $exception) {
            $exceptionMsg = $this->reformatException($exception, $item, (int)$store->getStoreId());
            throw new CouldNotImportOrder($exceptionMsg);
        }

        return $qty;
    }

    /**
     * Get Product by ID
     *
     * @param int $productId
     * @param int $storeId
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    private function getProductById(int $productId, int $storeId): ProductInterface
    {
        return $this->productRepository->getById($productId, false, $storeId);
    }

    /**
     * Calculate Product Price, depends on Tax Rate and Tax Settings
     *
     * @param array $item
     * @param ProductInterface $product
     * @param StoreInterface $store
     * @param Quote $quote
     *
     * @return float
     */
    private function getProductPrice(array $item, ProductInterface $product, StoreInterface $store, Quote $quote): float
    {
        $price = ((float)$item['per_item']) / 100;
        if (!$this->configProvider->getNeedsTaxCalculation('price', (int)$store->getId())) {
            $request = $this->taxCalculation->getRateRequest(
                $quote->getShippingAddress(),
                $quote->getBillingAddress(),
                null,
                $store
            );
            $percent = $this->taxCalculation->getRate(
                $request->setData('product_class_id', $product->getData('tax_class_id'))
            );
            $price = $price / (100 + $percent) * 100;
        }

        return $price;
    }

    /**
     * Set product data
     *
     * @param ProductInterface $product
     * @param float $price
     *
     * @return ProductInterface
     */
    private function setProductData(ProductInterface $product, float $price): ProductInterface
    {
        $product->setPrice($price)
            ->setFinalPrice($price)
            ->setSpecialPrice($price)
            ->setTierPrice([])
            ->setOriginalCustomPrice($price)
            ->setSpecialFromDate(null)
            ->setSpecialToDate(null);

        return $product;
    }

    /**
     * Generate readable exception message
     *
     * @param Exception $exception
     * @param array $item
     * @param int $storeId
     * @return Phrase
     */
    private function reformatException(Exception $exception, array $item, int $storeId = 0): Phrase
    {
        try {
            $this->getProductById((int)$item['remote_id'], $storeId);
        } catch (NoSuchEntityException $exception) {
            $exceptionMsg = self::PRODUCT_NOT_FOUND_EXCEPTION;
            return __(
                $exceptionMsg,
                !empty($item['name']) ? $item['name'] : __('*unknown*'),
                $item['remote_id']
            );
        }

        $productException = self::PRODUCT_EXCEPTION;
        return __(
            $productException,
            !empty($item['name']) ? $item['name'] : __('*unknown*'),
            $exception->getMessage()
        );
    }
}

<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Service\Order;

use Exception;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use WebshopNL\Connect\Exceptions\CouldNotImportOrder;

/**
 * Class Order Import
 */
class Import
{
    /**
     * Exception messages
     */
    private const COULD_NOT_IMPORT_ORDER = 'Could not import order %1: %2';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var QuoteManagement
     */
    private $quoteManagement;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;
    /**
     * @var Items\Add
     */
    private $addItems;
    /**
     * @var Quote\Create
     */
    private $createQuote;
    /**
     * @var Shipping\SetMethod
     */
    private $setShippingMethod;
    /**
     * @var Process\CreateInvoice
     */
    private $createInvoice;

    /**
     * @param StoreManagerInterface $storeManager
     * @param QuoteManagement $quoteManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param CartRepositoryInterface $cartRepository
     * @param Items\Add $addItems
     * @param Quote\Create $createQuote
     * @param Shipping\SetMethod $setShippingMethod
     * @param Process\CreateInvoice $createInvoice
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        QuoteManagement $quoteManagement,
        OrderRepositoryInterface $orderRepository,
        CartRepositoryInterface $cartRepository,
        Items\Add $addItems,
        Quote\Create $createQuote,
        Shipping\SetMethod $setShippingMethod,
        Process\CreateInvoice $createInvoice
    ) {
        $this->storeManager = $storeManager;
        $this->quoteManagement = $quoteManagement;
        $this->orderRepository = $orderRepository;
        $this->cartRepository = $cartRepository;
        $this->addItems = $addItems;
        $this->createQuote = $createQuote;
        $this->setShippingMethod = $setShippingMethod;
        $this->createInvoice = $createInvoice;
    }

    /**
     * Create a Magento order from Webshopnl order data
     *
     * @param array $orderData
     * @param $storeId
     * @return OrderInterface $order
     * @throws CouldNotImportOrder
     */
    public function execute(array $orderData, $storeId): OrderInterface
    {
        try {
            $store = $this->storeManager->getStore($storeId);
            $store->setCurrentCurrencyCode('EUR');
            $quote = $this->createQuote->createCustomerQuote($orderData, $store);
            $itemCount = $this->addItems->execute($quote, $orderData, $store);
            $quote->collectTotals();

            $quote = $this->setShippingMethod->execute($quote, $store, $itemCount, $orderData);
            $quote->setPaymentMethod('webshopnl');
            $quote->setInventoryProcessed(false);
            $quote->getPayment()->importData(['method' => 'webshopnl']);
            $totals = $quote->getTotals();

            $quote->setTotals($totals);
            $quote->collectTotals();
            $quote->setTotalsCollectedFlag(false)->collectTotals();
            $this->cartRepository->save($quote);
            $order = $this->quoteManagement->submit($quote);
            // submit() is documented to return null when the quote could not be turned into an
            // order. Calling setData() on that is a fatal Error, which the catch below does not
            // see, so the import died without the module's own message. The concrete model is what
            // carries setData(), and it is what Magento hands back here.
            if (!$order instanceof Order) {
                throw new CouldNotImportOrder(__('The quote could not be submitted as an order.'));
            }

            $order->setData('webshopnl_order_id', $orderData['order_id']);
            $store->setCurrentCurrencyCode($store->getBaseCurrencyCode());
            $this->createInvoice->execute($order);
            $this->orderRepository->save($order);
            return $order;
        } catch (Exception $exception) {
            $couldNotImportMsg = self::COULD_NOT_IMPORT_ORDER;
            $message = __(
                $couldNotImportMsg,
                'webshopnl_id',
                $exception->getMessage()
            );
            throw new CouldNotImportOrder($message);
        }
    }
}

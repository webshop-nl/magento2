<?php
/**
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WebshopNL\Connect\Plugin\Sales;

use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class AddWebshopnlOrderIdToOrder
 */
class AddWebshopnlOrderIdToOrder
{
    /**
     * @var OrderExtensionFactory
     */
    private $orderExtensionFactory;

    /**
     * AddWebshopnlOrderIdToOrder constructor.
     * @param OrderExtensionFactory $orderExtensionFactory
     */
    public function __construct(
        OrderExtensionFactory $orderExtensionFactory
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $searchCriteria
     * @return OrderSearchResultInterface
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        OrderSearchResultInterface $searchCriteria
    ) {
        foreach ($searchCriteria->getItems() as $entity) {
            $this->addWebshopnlOrderIdToOrder($entity);
        }

        return $searchCriteria;
    }

    /**
     * @param OrderInterface $entity
     */
    private function addWebshopnlOrderIdToOrder(OrderInterface $entity)
    {
        $extensionAttributes = $this->getExtensionAttributes($entity);
        $extensionAttributes->setWebshopnlOrderId($entity->getData('webshopnl_order_id'));
        $entity->setExtensionAttributes($extensionAttributes);
    }

    /**
     * @param OrderInterface $entity
     * @return \Magento\Sales\Api\Data\OrderExtension|\Magento\Sales\Api\Data\OrderExtensionInterface
     */
    private function getExtensionAttributes(OrderInterface $entity)
    {
        $extensionAttributes = $entity->getExtensionAttributes();
        if ($extensionAttributes) {
            return $extensionAttributes;
        }

        return $this->orderExtensionFactory->create();
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ) {
        $this->addWebshopnlOrderIdToOrder($order);

        return $order;
    }
}

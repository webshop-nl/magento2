<?php
/**
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Model\Webapi;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface as MagentoOrderRepository;
use WebshopNL\Connect\Api\Config\RepositoryInterface as ConfigRepository;
use WebshopNL\Connect\Api\Config\System\OrderInterface as OrderInterfaceAlias;
use WebshopNL\Connect\Api\Log\RepositoryInterface as LogRepository;
use WebshopNL\Connect\Api\Webapi\OrderInterface;
use WebshopNL\Connect\Exceptions\CouldNotImportOrder;
use WebshopNL\Connect\Service\Order\Import as OrderImport;

/**
 * Class Order
 */
class Order implements OrderInterface
{

    public const NO_ORDER_EXIST_EXCEPTION = 'Order id %s does not exists';

    /**
     * @var LogRepository
     */
    private $logRepository;
    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;
    /**
     * @var File
     */
    private $driver;
    /**
     * @var OrderImport
     */
    private $orderImport;
    /**
     * @var WriterInterface
     */
    private $configWriter;
    /**
     * @var TimezoneInterface
     */
    private $date;
    /**
     * @var MagentoOrderRepository
     */
    private $magentoOrderRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * Order constructor.
     *
     * @param LogRepository $logRepository
     * @param JsonSerializer $jsonSerializer
     * @param File $driver
     * @param OrderImport $orderImport
     * @param WriterInterface $configWriter
     * @param TimezoneInterface $date
     * @param MagentoOrderRepository $magentoOrderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param MagentoOrderRepository $orderRepository
     */
    public function __construct(
        LogRepository $logRepository,
        JsonSerializer $jsonSerializer,
        File $driver,
        OrderImport $orderImport,
        WriterInterface $configWriter,
        TimezoneInterface $date,
        MagentoOrderRepository $magentoOrderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        ConfigRepository $configRepository
    ) {
        $this->logRepository = $logRepository;
        $this->jsonSerializer = $jsonSerializer;
        $this->driver = $driver;
        $this->orderImport = $orderImport;
        $this->configWriter = $configWriter;
        $this->date = $date;
        $this->magentoOrderRepository = $magentoOrderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->configRepository = $configRepository;
    }

    /**
     * @inheritDoc
     */
    public function processOrder(int $storeId): array
    {
        try {
            $post = $this->driver->fileGetContents('php://input');
            $orderData = $this->jsonSerializer->unserialize($post);
            if (isset($orderData['order_id'])) {
                $searchCriteria = $this->searchCriteriaBuilder
                    ->addFilter('webshopnl_order_id', $orderData['order_id'])
                    ->create();
                $orders = $this->orderRepository->getList($searchCriteria)->getItems();
                if ($orders) {
                    return [
                        [
                            'module_version' => $this->configRepository->getExtensionVersion(),
                            'order_id' => $orderData['order_id'],
                            'merchant_id' => $orderData['merchant_id'],
                            'status' => "REJECTED",
                            'message' => 'Order already imported'
                        ]
                    ];
                }
            } else {
                return ['order_id missed'];
            }
            $orderData = $this->validateOrderData($orderData);
            $order = $this->orderImport->execute($orderData, $storeId);

            $this->configWriter->save(
                OrderInterfaceAlias::LAST_ORDER_IMPORT,
                $this->date->date()->format('Y-m-d H:i:s')
            );

            if ($order->getIncrementId()) {
                return [
                    [
                        'module_version' => $this->configRepository->getExtensionVersion(),
                        'order_id' => $orderData['order_id'],
                        'remote_order_id' => $order->getId(),
                        'merchant_id' => $orderData['merchant_id'],
                        'status' => "CONFIRMED",
                        'message' => 'Order was imported'
                    ]
                ];
            } else {
                return [
                    [
                        'module_version' => $this->configRepository->getExtensionVersion(),
                        'order_id' => $orderData['order_id'],
                        'merchant_id' => $orderData['merchant_id'],
                        'status' => "REJECTED",
                        'message' => 'Something went wrong'
                    ]
                ];
            }

        } catch (\Exception $exception) {
            $this->logRepository->addErrorLog('Webhook processTransfer postData', $exception->getMessage());
            return [
                [
                    'module_version' => $this->configRepository->getExtensionVersion(),
                    'order_id' => $orderData['order_id'] ?? null,
                    'merchant_id' => $orderData['merchant_id'] ?? null,
                    'status' => "REJECTED",
                    'message' => $exception->getMessage()
                ]
            ];
        }
    }

    /**
     * @param $orderData
     * @return mixed
     * @throws CouldNotImportOrder
     */
    private function validateOrderData($orderData)
    {
        if (!isset($orderData['delivery_address']) || !$orderData['delivery_address']) {
            throw new CouldNotImportOrder(__('delivery_address is missing'));
        }
        if (!isset($orderData['billing_address']) || !$orderData['billing_address']) {
            throw new CouldNotImportOrder(__('billing_address is missing'));
        }
        if (!isset($orderData['delivery_address']['email']) || !$orderData['delivery_address']['email']) {
            throw new CouldNotImportOrder(__('[delivery_address][email] is missing'));
        }
        if (!isset($orderData['delivery_address']['first_name']) || !$orderData['delivery_address']['first_name']) {
            throw new CouldNotImportOrder(__('[delivery_address][first_name] is missing'));
        }
        if (!isset($orderData['delivery_address']['surname']) || !$orderData['delivery_address']['surname']) {
            throw new CouldNotImportOrder(__('[delivery_address][surname] is missing'));
        }
        if (empty($orderData['items']['products'])) {
            throw new CouldNotImportOrder(__('[items][products] are missing'));
        }
        if (!isset($orderData['shipping_method']['cost'])) {
            throw new CouldNotImportOrder(__('[shipping_method][cost] is missing'));
        }

        return $orderData;
    }

    /**
     * @inheritDoc
     */
    public function getStatus(int $orderId): array
    {
        try {
            $order = $this->magentoOrderRepository->get($orderId);
        } catch (NoSuchEntityException $exception) {
            return [sprintf(self::NO_ORDER_EXIST_EXCEPTION, $orderId)];
        }

        return [
            [
                'module_version' => $this->configRepository->getExtensionVersion(),
                'id' => $orderId,
                'status' => $order->getStatus(),
                'shipment_available' => $order->hasShipments(),
                'delivery_data' => $this->getDeliveryData($order)
            ]
        ];
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return array
     */
    private function getDeliveryData(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $deliveryData = [];
        foreach ($order->getShipmentsCollection() as $shipment) {
            /** @var \Magento\Sales\Api\Data\TrackInterface $track */
            foreach ($shipment->getTracksCollection()->getItems() as $track) {
                $deliveryData[] = [
                    'shipment_date' => $shipment->getCreatedAt(),
                    'tracking_code' => $track->getTrackNumber(),
                    'delivery_service' => $track->getTitle()
                ];
            }
        }
        return $deliveryData;
    }
}

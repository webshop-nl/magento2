<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Model\ProductData;

use WebshopNL\Connect\Api\Config\RepositoryInterface as ConfigRepository;
use WebshopNL\Connect\Api\ProductData\RepositoryInterface as ProductData;
use WebshopNL\Connect\Service\ProductData\AttributeCollector\Data\Image;
use WebshopNL\Connect\Service\ProductData\Filter;
use WebshopNL\Connect\Service\ProductData\Type;

/**
 * Selftest repository class
 */
class Repository implements ProductData
{
    public const PREVIEW_QTY = 250;

    /**
     * Base attributes map to pull from product
     *
     * @var array
     */
    private $attributeMap = [
        'product_id' => 'entity_id',
        'sku' => 'sku',
        'visibility' => 'visibility',
        'type_id' => 'type_id',
    ];

    /**
     * Base map of feed structure data. Values as magento data, keys as data for feed
     *
     * @var array
     */
    private $resultMap = [
        'ID' => 'product_id',
        'sku' => 'sku',
        'name' => 'name',
        'family_ID' => 'parent_id',
        'description' => 'description',
        'descriptionLong' => 'description_long',
        'productURL' => 'url',
        'imageURL' => 'image',
        'additionalImages' => 'additional_images',
        'fromPrice' => 'price',
        'price' => 'final_price',
        'discount' => 'discount_perc',
        'EAN' => 'ean',
        'brand' => 'brand',
        'color' => 'color',
        'material' => 'material',
        'size' => 'size',
        'deliveryTime' => 'delivery_time',
        'deliveryCosts' => 'shipping_price',
        'qty' => 'salable_qty',
        'availability' => 'is_in_stock',
        'categoryPath' => 'categoryPath',
        'categories' => 'categories',
        'subcategories' => 'subcategories',
        'subsubcategories' => 'subsubcategories',
        'extra_info' => 'extra_info'
    ];

    /**
     * @var ConfigRepository
     */
    private $configRepository;
    /**
     * @var array
     */
    private $entityIds;
    /**
     * @var Type
     */
    private $type;
    /**
     * @var Filter
     */
    private $filter;
    /**
     * @var Image
     */
    private $image;
    /**
     * @var array
     */
    private $staticFields;
    /**
     * @var array
     */
    private $imageData;

    /**
     * Repository constructor.
     * @param ConfigRepository $configRepository
     * @param Filter $filter
     * @param Type $type
     * @param Image $image
     */
    public function __construct(
        ConfigRepository $configRepository,
        Filter $filter,
        Type $type,
        Image $image
    ) {
        $this->configRepository = $configRepository;
        $this->filter = $filter;
        $this->type = $type;
        $this->image = $image;
    }

    /**
     * @inheritDoc
     */
    public function getProductData(int $storeId = 0, array $entityIds = [], string $type = 'manual'): array
    {
        $this->collectIds($storeId, $entityIds);
        $this->collectAttributes($storeId);
        $this->staticFields = $this->configRepository->getStaticFields($storeId);
        $this->imageData = $this->image->execute($this->entityIds, $storeId);

        $result = [];
        foreach ($this->collectProductData($storeId, $type) as $entityId => $productData) {
            if (empty($productData['product_id'])) {
                continue;
            }
            $productId = (int)$productData['product_id'];
            $this->addImageData($storeId, $entityId, $productData);
            $this->addStaticFields($productData);
            $this->addCategoryData($productData);
            foreach ($this->resultMap as $index => $attr) {
                $result[$entityId][$index] = $this->prepareAttribute($attr, $productData);
            }
        }

        $addDisabled = $this->configRepository->getFilters($storeId)['add_disabled_products'];
        if (!$addDisabled) {
            foreach ($result as $id => &$datum) {
                if ($datum['availability'] == 'out of stock') {
                    unset($result[$id]);
                }
            }
        }

        return $result;
    }

    /**
     * Collect all entity ids for collection
     *
     * @param int $storeId
     * @param array $entityIds
     */
    private function collectIds(int $storeId, array $entityIds = []): void
    {
        $this->entityIds = $this->filter->execute(
            $this->configRepository->getFilters($storeId),
            $storeId
        );
        if ($entityIds) {
            $this->entityIds = array_intersect($entityIds, $this->entityIds);
        }
    }

    /**
     * Collect all atrributes needed for product collection
     *
     * @param int $storeId
     */
    private function collectAttributes(int $storeId = 0): void
    {
        $this->attributeMap += $this->configRepository->getAttributes($storeId);
        $extraFields = $this->configRepository->getExtraFields($storeId);
        foreach ($extraFields as $field) {
            $this->attributeMap[$field['name']] = $field['attribute'];
            $this->resultMap[$field['name']] = $field['attribute'];
        }

        $filters = $this->configRepository->getFilters($storeId);
        $advancedFilters = $filters['advanced_filters'] ?? [];
        foreach ($advancedFilters as $filter) {
            $this->attributeMap[] = $filter['attribute'];
        }

        $this->attributeMap = array_filter($this->attributeMap);
    }

    /**
     * Collect all product data
     *
     * @param int $storeId
     * @param string $type
     * @return array
     */
    private function collectProductData(int $storeId, string $type = 'manual'): array
    {
        $extraParameters = [
            'filters' => [
                'custom' => $this->configRepository->getFilters($storeId)['advanced_filters'],
                'exclude_attribute' => 'tradetracker_exclude',
                'exclude_disabled' => !$this->configRepository->getFilters($storeId)['add_disabled_products']
            ],
            'stock' => [
                'inventory' => true,
            ],
            'category' => [
                'exclude_attribute' => ['code' => 'webshopnl_disable_export', 'value' => 1],
                'replace_attribute' => 'webshopnl_category',
                'include_anchor' => true
            ],
            'behaviour' => [
                'configurable' => $this->configRepository->getConfigProductsBehaviour($storeId),
                'bundle' => $this->configRepository->getBundleProductsBehaviour($storeId),
                'grouped' => $this->configRepository->getGroupedProductsBehaviour($storeId)
            ]
        ];

        return $this->type->execute(
            $this->entityIds,
            $this->attributeMap,
            $extraParameters,
            $storeId,
            $type == 'preview' ? self::PREVIEW_QTY : 100000
        );
    }

    /**
     * Add image data to productData array
     *
     * @param int $storeId
     * @param int $entityId
     * @param array $productData
     */
    private function addImageData(int $storeId, int $entityId, array &$productData): void
    {
        $imageData = $this->imageData[$entityId] ?? null;

        if (!empty($productData['parent_id']) && !empty($productData['image_logic'])) {
            $parentImageData = $this->imageData[$productData['parent_id']] ?? null;
            switch ($productData['image_logic']) {
                case 1:
                    $imageData = $parentImageData;
                    break;
                case 2:
                    $imageData = $imageData ?? $parentImageData;
                    break;
                case 3:
                case 4:
                    if ($parentImageData) {
                        foreach ($parentImageData as $storeId => $parentImageDataStore) {
                            $imageData[$storeId] += $parentImageDataStore;
                        }
                    }
                    break;
            }
        }

        if ($imageData === null) {
            return;
        }

        $imageConfig = $this->configRepository->getImageAttributes($storeId);
        if (!isset($imageData[$storeId])) {
            $storeId = 0;
        }

        ksort($imageData[$storeId]);
        if (count($imageConfig) == 1) {
            foreach ($imageData[$storeId] as $image) {
                if (in_array($imageConfig['image'], $image['types'])) {
                    $productData['image'] = $image['file'];
                }
            }
        } else {
            $productData['image'] = null;
            foreach ($imageData[$storeId] as $index => $image) {
                if ($productData['image'] === null) {
                    $productData['image'] = $image['file'];
                } else {
                    $productData['additional_images'][] = $image['file'];
                }
            }
        }
    }

    /**
     * Add category data to productData array
     *
     * @param array $productData
     */
    private function addStaticFields(array &$productData): void
    {
        foreach ($this->staticFields as $k => $v) {
            if (!is_array($v)) {
                $productData[$k] = $v;
                continue;
            }

            foreach ($v as $kk => $vv) {
                list($attribute, $condition, $value) = explode(' ', $kk);
                if (isset($productData[$attribute])) {
                    $attributeValue = $productData[$attribute];
                    switch ($condition) {
                        case '==':
                            $value = ($attributeValue == $value) ? $vv : null;
                            break;
                        case '!=':
                            $value = ($attributeValue != $value) ? $vv : null;
                            break;
                        case '>=':
                            $value = ($attributeValue >= $value) ? $vv : null;
                            break;
                        case '>':
                            $value = ($attributeValue > $value) ? $vv : null;
                            break;
                        case '<=':
                            $value = ($attributeValue <= $value) ? $vv : null;
                            break;
                        case '<':
                            $value = ($attributeValue < $value) ? $vv : null;
                            break;
                        case 'between':
                            list($from, $to) = explode('/', $value);
                            $value = ($attributeValue >= $from && $attributeValue <= $to) ? $vv : null;
                            break;
                    }
                    if ($value !== null) {
                        $productData[$k] = $value;
                    }
                }
            }
        }
    }

    /**
     * Add category data to productData array
     *
     * @param array $productData
     */
    private function addCategoryData(array &$productData): void
    {
        if (isset($productData['category_custom'])) {
            $path = $productData['category_custom'];
        } else {
            $categories = $productData['category'] ?? [];
            array_multisort(array_column($categories, 'level'), SORT_DESC, $categories);
            $category = reset($categories);
            $path = $category['path'] ?? null;
        }

        if (empty($path)) {
            return;
        }

        $pathExploded = explode(' > ', $path);
        $categoryData = [
            'categoryPath' => $path,
            'categories' => $pathExploded[0] ?? null,
            'subcategories' => $pathExploded[1] ?? null,
            'subsubcategories' => $pathExploded[2] ?? null,
        ];

        $productData += $categoryData;
    }

    /**
     * Attribute data preperation
     *
     * @param string $attribute
     * @param array $productData
     * @return mixed|string|null
     */
    private function prepareAttribute(string $attribute, array $productData)
    {
        $value = $productData[$attribute] ?? null;
        $currency = $productData['currency'] ?? null;
        switch ($attribute) {
            case 'status':
                return ($value) ? 'Enabled' : 'Disabled';
            case 'is_in_stock':
                return ($value) ? 'in stock' : 'out of stock';
            case 'manage_stock':
                return ($value) ? 'true' : 'false';
            case 'url':
                if (!isset($productData['url'])) {
                    return '';
                }
                if (!empty($productData['utm_string'])) {
                    $prefix = strpos($productData['url'], '?') !== false ? '&' : '?';
                    return $productData['url'] . $prefix . $productData['utm_string'];
                }
                return $productData['url'];
            case 'price':
            case 'price_ex':
            case 'final_price':
            case 'final_price_ex':
            case 'min_price':
            case 'max_price':
            case 'sales_price':
                if ($value !== null) {
                    return number_format((float)$value, 2, '.', '') . ' ' . $currency;
                }
            // no break
            case 'visibility':
                switch ($value) {
                    case 1:
                        return 'Not Visible Individually';
                    case 2:
                        return 'Catalog';
                    case 3:
                        return 'Search';
                    case 4:
                        return 'Catalog, Search';
                }
        }
        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getProductAttributes(int $storeId = 0): array
    {
        $this->collectAttributes($storeId);
        return $this->attributeMap;
    }
}

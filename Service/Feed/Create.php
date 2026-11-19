<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Service\Feed;

use Magento\Framework\Filesystem\Io\File;

/**
 * Feed creator service
 */
class Create
{
    public const JSON_FORMAT_FULL = '"%s":"%s",';
    public const JSON_FORMAT_OPENED = '"%s":{';

    /**
     * @var File
     */
    private $file;

    /**
     * Create constructor.
     * @param File $file
     */
    public function __construct(
        File $file
    ) {
        $this->file = $file;
    }

    /**
     * @param array $feed
     * @param int $storeId
     * @param string $path
     */
    public function execute(array $feed, int $storeId, string $path)
    {
        $ndjson = '';
        foreach ($feed as $productsData) {
            foreach ($productsData as $productData) {
                $ndjson .= "{" . $this->createJson($productData) . "}\n";
            }
        }

        $fileInfo = $this->file->getPathInfo($path);
        $this->file->mkdir($fileInfo['dirname']);
        $this->file->write($path, $ndjson);
    }

    /**
     * @param array $data
     * @return string
     */
    public function createJson($data)
    {
        $jsonStr = '';
        foreach ($data as $key => $value) {

            if (!is_array($value)) {
                $jsonStr .= sprintf(self::JSON_FORMAT_FULL, $key, $value);
            } else {
                $jsonStr .= sprintf(self::JSON_FORMAT_OPENED, $key);
                foreach ($value as $nestedKey => $nestedValue) {
                    $jsonStr .= sprintf(self::JSON_FORMAT_FULL, $nestedKey, $nestedValue);
                }
                $jsonStr = rtrim($jsonStr, ','); //remove last comma
                $jsonStr .= '},';
            }
        }
        $jsonStr = rtrim($jsonStr, ','); //remove last comma
        return $jsonStr;
    }
}

<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Logger;

use Magento\Framework\Serialize\Serializer\Json;
use Monolog\Logger;

/**
 * ErrorLogger logger class
 */
class ErrorLogger extends Logger
{

    /**
     * @var Json
     */
    private $json;

    /**
     * ErrorLogger constructor.
     *
     * @param Json $json
     * @param string $name
     * @param array $handlers
     * @param array $processors
     */
    public function __construct(
        Json $json,
        string $name,
        array $handlers = [],
        array $processors = []
    ) {
        $this->json = $json;
        // Monolog 3 types both as lists; di.xml hands over associative arrays keyed by handler name.
        parent::__construct($name, array_values($handlers), array_values($processors));
    }

    /**
     * Add error data to webshopnl Log
     *
     * @param string $type
     * @param mixed $data
     *
     */
    public function addLog($type, $data)
    {
        if (is_array($data) || is_object($data)) {
            $payload = $this->json->serialize(is_object($data) ? (array)$data : $data);
            $this->addRecord(static::ERROR, $type . ': ' . $payload);
        } else {
            $this->addRecord(static::ERROR, $type . ': ' . $data);
        }
    }
}

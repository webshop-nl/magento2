<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Api\Log;

/**
 * Log repository interface
 */
interface RepositoryInterface
{

    /**
     * How many of the most recent lines the log viewer shows.
     */
    public const STREAM_DEFAULT_LIMIT = 100;

    /**
     * Where the loggers write: var/log/webshopnl/<type>.log.
     */
    public const LOG_FILE = '%s/log/webshopnl/%s.log';

    /**
     * Add record to error log
     *
     * @param string $type
     * @param mixed $data
     */
    public function addErrorLog(string $type, $data);

    /**
     * Add record to debug log
     *
     * @param string $type
     * @param mixed $data
     */
    public function addDebugLog(string $type, $data);

    /**
     * Path of the log file of the given type, or null when it cannot be resolved
     *
     * @param string $type
     * @return string|null
     */
    public function getLogFilePath(string $type): ?string;

    /**
     * The most recent entries of a log file, newest first, or null when there is no file
     *
     * @param string $path
     * @param int|null $limit
     * @return array|null
     */
    public function getLogEntriesAsArray(string $path, ?int $limit = null): ?array;
}

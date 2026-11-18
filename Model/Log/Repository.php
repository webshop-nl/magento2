<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Model\Log;

use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Stdlib\DateTime\DateTime;
use WebshopNL\Connect\Api\Log\RepositoryInterface as LogRepositoryInterface;
use WebshopNL\Connect\Logger\DebugLogger;
use WebshopNL\Connect\Logger\ErrorLogger;

/**
 * Logs repository class
 */
class Repository implements LogRepositoryInterface
{

    /**
     * @var DebugLogger
     */
    private $debugLogger;
    /**
     * @var ErrorLogger
     */
    private $errorLogger;
    /**
     * @var DirectoryList
     */
    private $directoryList;
    /**
     * @var File
     */
    private $file;
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * Repository constructor.
     *
     * @param DebugLogger $debugLogger
     * @param ErrorLogger $errorLogger
     * @param DirectoryList $directoryList
     * @param File $file
     * @param DateTime $dateTime
     */
    public function __construct(
        DebugLogger $debugLogger,
        ErrorLogger $errorLogger,
        DirectoryList $directoryList,
        File $file,
        DateTime $dateTime
    ) {
        $this->debugLogger = $debugLogger;
        $this->errorLogger = $errorLogger;
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->dateTime = $dateTime;
    }

    /**
     * @inheritDoc
     */
    public function addErrorLog(string $type, $data)
    {
        $this->errorLogger->addLog($type, $data);
    }

    /**
     * @inheritDoc
     */
    public function addDebugLog(string $type, $data)
    {
        $this->debugLogger->addLog($type, $data);
    }

    /**
     * @inheritDoc
     */
    public function getLogFilePath(string $type): ?string
    {
        try {
            return sprintf(self::LOG_FILE, $this->directoryList->getPath('var'), $type);
        } catch (\Throwable $exception) {
            return null;
        }
    }

    /**
     * @inheritDoc
     *
     * Read through the file driver rather than the plain file functions: the coding standard
     * discourages those, and CI runs the sniffer with annotations ignored, so an exemption in the
     * source would not be honoured there.
     */
    public function getLogEntriesAsArray(string $path, ?int $limit = null): ?array
    {
        $limit = $limit ?: self::STREAM_DEFAULT_LIMIT;

        try {
            if (!$this->file->isExists($path)) {
                return null;
            }

            $stream = $this->file->fileOpen($path, 'r');
            $lines = [];
            while (!$this->file->endOfFile($stream)) {
                $line = $this->file->fileReadLine($stream, 8192);
                if (trim((string)$line) !== '') {
                    $lines[] = $line;
                }
            }
            $this->file->fileClose($stream);

            $entries = [];
            foreach (array_slice($lines, -$limit) as $line) {
                $entries[] = $this->parseLine($line);
            }

            return array_reverse($entries);
        } catch (\Throwable $exception) {
            return null;
        }
    }

    /**
     * Split a Monolog line into the moment it was written and what was written.
     *
     * @param string $line
     * @return array
     */
    private function parseLine(string $line): array
    {
        $parts = explode('] ', $line);
        $date = ltrim((string)array_shift($parts), '[');
        $rest = explode(': ', implode('] ', $parts));
        unset($rest[0]);
        $type = $rest[1] ?? '--';
        array_shift($rest);

        return [
            'date' => $this->dateTime->date('Y-m-d H:i:s', $date) . ' - ' . $type,
            'msg' => trim(implode(': ', $rest))
        ];
    }
}

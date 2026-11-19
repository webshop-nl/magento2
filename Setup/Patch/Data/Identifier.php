<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Ramsey\Uuid\UuidFactory;
use WebshopNL\Connect\Api\Log\RepositoryInterface as LogRepository;
use WebshopNL\Connect\Api\Config\RepositoryInterface as ConfigRepository;

/**
 * Patch to add identifier
 */
class Identifier implements DataPatchInterface
{

    /**
     * @var WriterInterface
     */
    private $configWriter;
    /**
     * @var LogRepository
     */
    private $logRepository;
    /**
     * @var UuidFactory
     */
    private $uuid;

    /**
     * Identifier constructor.
     *
     * @param WriterInterface $configWriter
     * @param LogRepository $logRepository
     */
    public function __construct(
        WriterInterface $configWriter,
        LogRepository $logRepository,
        UuidFactory $uuid
    ) {
        $this->configWriter = $configWriter;
        $this->logRepository = $logRepository;
        $this->uuid = $uuid;
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        try {
            $this->configWriter->save(
                ConfigRepository::XML_PATH_INSTALLATION_ID,
                $this->uuid->uuid6()->toString()
            );
        } catch (\Exception $exception) {
            $this->logRepository->addErrorLog('Identifier patch', $exception->getMessage());
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return [];
    }
}

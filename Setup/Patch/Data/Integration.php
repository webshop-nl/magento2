<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use WebshopNL\Connect\Api\Log\RepositoryInterface as LogRepository;
use WebshopNL\Connect\Service\WebApi\Integration as CreateToken;

/**
 * Patch to add token
 */
class Integration implements DataPatchInterface
{

    /**
     * @var CreateToken
     */
    private $createToken;
    /**
     * @var LogRepository
     */
    private $logRepository;

    /**
     * Integration constructor.
     * @param CreateToken $createToken
     * @param LogRepository $logRepository
     */
    public function __construct(
        CreateToken $createToken,
        LogRepository $logRepository
    ) {
        $this->createToken = $createToken;
        $this->logRepository = $logRepository;
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
    public function apply()
    {
        try {
            $this->createToken->createToken();
        } catch (\Exception $exception) {
            $this->logRepository->addErrorLog('Integration patch', $exception->getMessage());
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return [];
    }
}

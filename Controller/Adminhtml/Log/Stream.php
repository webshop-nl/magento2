<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Controller\Adminhtml\Log;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use WebshopNL\Connect\Api\Log\RepositoryInterface as LogRepository;

/**
 * Serves the last entries of the debug or error log to the admin
 */
class Stream extends Action
{

    public const ADMIN_RESOURCE = 'WebshopNL_Connect::config';

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;
    /**
     * @var LogRepository
     */
    private $logRepository;

    /**
     * @param Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param LogRepository $logRepository
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        LogRepository $logRepository
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logRepository = $logRepository;
        parent::__construct($context);
    }

    /**
     * @return Json
     */
    public function execute(): Json
    {
        $resultJson = $this->resultJsonFactory->create();
        $type = $this->getRequest()->getParam('type') === 'error' ? 'error' : 'debug';
        $path = $this->logRepository->getLogFilePath($type);

        if ($path && $entries = $this->logRepository->getLogEntriesAsArray($path)) {
            return $resultJson->setData(['result' => $entries]);
        }

        return $resultJson->setData(__('Log is empty'));
    }
}

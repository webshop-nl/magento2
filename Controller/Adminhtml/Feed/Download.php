<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Controller\Adminhtml\Feed;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Filesystem\Io\File as IoFile;
use WebshopNL\Connect\Api\Feed\RepositoryInterface as FeedRepository;

/**
 * Download controller for the generated product feed
 */
class Download extends Action
{

    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'WebshopNL_Connect::config';

    /**
     * @var FeedRepository
     */
    private $feedRepository;
    /**
     * @var FileFactory
     */
    private $fileFactory;
    /**
     * @var RedirectInterface
     */
    private $redirect;
    /**
     * @var DirectoryList
     */
    private $directoryList;
    /**
     * @var IoFile
     */
    private $ioFile;

    /**
     * @param Action\Context $context
     * @param FeedRepository $feedRepository
     * @param FileFactory $fileFactory
     * @param RedirectInterface $redirect
     * @param DirectoryList $directoryList
     * @param IoFile $ioFile
     */
    public function __construct(
        Action\Context $context,
        FeedRepository $feedRepository,
        FileFactory $fileFactory,
        RedirectInterface $redirect,
        DirectoryList $directoryList,
        IoFile $ioFile
    ) {
        $this->feedRepository = $feedRepository;
        $this->fileFactory = $fileFactory;
        $this->redirect = $redirect;
        $this->directoryList = $directoryList;
        $this->ioFile = $ioFile;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $storeId = (int)$this->getRequest()->getParam('store_id');
        $location = $this->feedRepository->getFeedLocation($storeId);

        if (empty($location['available'])) {
            $this->messageManager->addErrorMessage(
                (string)__('There is no feed for this store view yet. Generate it first.')
            );

            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
                ->setPath($this->redirect->getRefererUrl());
        }

        // The path is absolute and the file sits outside var/, so it is handed over relative to the
        // root directory rather than to var/, which is what createResponse() assumes by default.
        $path = (string)$location['path'];

        return $this->fileFactory->create(
            (string)($this->ioFile->getPathInfo($path)['basename'] ?? ''),
            [
                'type' => 'filename',
                'value' => ltrim(
                    str_replace($this->directoryList->getPath(DirectoryList::ROOT), '', $path),
                    '/'
                ),
                'rm' => false,
            ],
            DirectoryList::ROOT,
            'application/x-ndjson'
        );
    }
}

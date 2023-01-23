<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Controller\Adminhtml\Feed;

use Magento\Backend\App\Action;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use WebshopNL\Connect\Api\Feed\RepositoryInterface as FeedRepository;

/**
 * Generate controller for product feed
 */
class Generate extends Action
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
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * Generate constructor.
     * @param FeedRepository $feedRepository
     * @param Action\Context $context
     * @param RedirectInterface $redirect
     */
    public function __construct(
        FeedRepository $feedRepository,
        Action\Context $context,
        RedirectInterface $redirect
    ) {
        $this->feedRepository = $feedRepository;
        $this->redirect = $redirect;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $storeId = (int)$this->getRequest()->getParam('store_id');
        $result = $this->feedRepository->generateAndSaveFeed($storeId);
        if ($result['success']) {
            $this->messageManager->addSuccessMessage($result['message']);
        } else {
            $this->messageManager->addErrorMessage($result['message']);
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath(
            $this->redirect->getRefererUrl()
        );
    }
}

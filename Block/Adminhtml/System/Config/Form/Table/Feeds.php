<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WebshopNL\Connect\Block\Adminhtml\System\Config\Form\Table;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use WebshopNL\Connect\Api\Feed\RepositoryInterface as FeedRepositoryInterface;

/**
 * Feeds Render Block
 */
class Feeds extends Field
{

    /**
     * Template file name
     *
     * @var string
     */
    protected $_template = 'WebshopNL_Connect::system/config/fieldset/feeds.phtml';

    /**
     * @var FeedRepositoryInterface
     */
    private $feedRepository;

    /**
     * Feeds constructor.
     *
     * @param Context $context
     * @param FeedRepositoryInterface $feedRepository
     */
    public function __construct(
        Context $context,
        FeedRepositoryInterface $feedRepository
    ) {
        $this->feedRepository = $feedRepository;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function getCacheLifetime()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function render(AbstractElement $element)
    {
        $element->addClass('webshopnl');

        return $this->toHtml();
    }

    /**
     * @return array
     */
    public function getStoreData(): array
    {
        return $this->feedRepository->getStoreData();
    }
}

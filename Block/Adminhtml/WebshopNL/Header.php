<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Block\Adminhtml\WebshopNL;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Serialize\Serializer\Json;
use WebshopNL\Connect\Api\Config\RepositoryInterface as ConfigRepository;

/**
 * System Configuration Module information Block
 */
class Header extends Field
{

    /**
     * @var string
     */
    protected $_template = 'WebshopNL_Connect::system/config/fieldset/header.phtml';

    /**
     * @var ConfigRepository
     */
    private $configRepository;
    /**
     * @var ScopeConfig
     */
    private $scopeConfig;

    private $json;

    /**
     * Header constructor.
     *
     * @param Context $context
     * @param ConfigRepository $config
     * @param ScopeConfig $scopeConfig
     * @param Json $json
     */
    public function __construct(
        Context $context,
        ConfigRepository $config,
        ScopeConfig $scopeConfig,
        Json $json
    ) {
        $this->configRepository = $config;
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
        parent::__construct($context);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $element->addClass('webshopnl');

        return $this->toHtml();
    }

    /**
     * Image with extension and magento version.
     *
     * @return string
     */
    public function getImage(): string
    {
        return sprintf(
            'https://www.magmodules.eu/logo/%s/%s/%s/logo.png',
            $this->configRepository->getExtensionCode(),
            $this->configRepository->getExtensionVersion(),
            $this->configRepository->getMagentoVersion()
        );
    }

    /**
     * Support link for extension.
     *
     * @return string
     */
    public function getSupportLink(): string
    {
        return $this->configRepository->getSupportLink();
    }

    /**
     * Registration links with params
     *
     * @return string
     */
    public function getRegisterLink(): string
    {
        return 'https://dev.partner.shipbox.io/register/?store_data=' . $this->getEncodedStoreData();
    }

    /**
     * @return string
     */
    private function getEncodedStoreData(): string
    {
        return base64_encode($this->json->serialize([
            'company_name' => $this->getStoreData('general/store_information/name'),
            'vat_id' => $this->getStoreData('general/store_information/merchant_vat_number'),
            'address' => $this->getStreet($this->getStoreData('general/store_information/street_line1')),
            'house_number' => $this->getHouseNumber($this->getStoreData('general/store_information/street_line1')),
            'postal_code' => $this->getStoreData('general/store_information/postcode'),
            'city' => $this->getStoreData('general/store_information/city'),
            'country' => $this->getStoreData('general/store_information/country_id')
        ]));
    }

    /**
     * @param $path
     * @return mixed
     */
    private function getStoreData($path)
    {
        return $this->scopeConfig->getValue($path);
    }

    /**
     * @param $address
     * @return string
     */
    private function getStreet($address): string
    {
        return trim(preg_replace('/[0-9]+/', '', (string)$address));
    }

    /**
     * @param $address
     * @return string
     */
    private function getHouseNumber($address): string
    {
        preg_match_all('!\d+!', (string)$address, $matches);
        return implode(' ', $matches[0]);
    }
}

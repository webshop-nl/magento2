<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Mode source class
 */
class Mode implements OptionSourceInterface
{

    public const DEVELOPMENT = 'development';
    public const PRODUCTION = 'production';

    /**
     * Options array
     *
     * @var array
     */
    public $options = null;

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        if (!$this->options) {
            $this->options = [
                ['value' => self::DEVELOPMENT, 'label' => ucfirst(self::DEVELOPMENT)],
                ['value' => self::PRODUCTION, 'label' => ucfirst(self::PRODUCTION)]
            ];
        }
        return $this->options;
    }
}

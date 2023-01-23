<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Console\CommandOptions;

use Symfony\Component\Console\Input\InputOption;

/**
 * Feed creator Options helper
 *
 * This class contains the list options and their related constants,
 * which can be used for webshopnl:feed:create CLI command
 */
class CreateFeedOptions extends OptionKeys
{

    /**
     * Deploy static command options list
     *
     * @return array
     */
    public function getOptionsList(): array
    {
        return array_merge($this->getBasicOptions(), $this->getSkipOptions());
    }

    /**
     * Basic options
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function getBasicOptions(): array
    {
        return [
            new InputOption(
                self::STORE_ID,
                null,
                InputOption::VALUE_OPTIONAL,
                'Define store ID for feed generator'
            )
        ];
    }

    /**
     * Additional options
     *
     * @return array
     */
    private function getSkipOptions(): array
    {
        return [];
    }
}

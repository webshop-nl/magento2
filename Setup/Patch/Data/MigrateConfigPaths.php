<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace WebshopNL\Connect\Setup\Patch\Data;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use WebshopNL\Connect\Api\Config\System\FeedInterface;
use WebshopNL\Connect\Model\Config\Backend\Cron;

/**
 * Bring configuration written by releases before 1.2.0 onto the paths this release reads.
 *
 * Two settings changed shape:
 *
 *   - The Frequency field stored the word "custom" and a backend model copied the expression to a
 *     crontab path of its own. The job now reads the field itself, and Magento's scheduler throws
 *     on "custom", so the stored word becomes the custom expression.
 *   - "Use Parent URL for Simples" under Grouped Products had no <config_path> and so stored under
 *     the section and group ids, which the feed never read.
 *
 * Idempotent: every step only touches rows that still have the old shape.
 */
class MigrateConfigPaths implements DataPatchInterface
{

    /**
     * Where the grouped link setting ended up without a config_path: <section>/<group>/<field>.
     */
    private const OLD_GROUPED_LINK_PATH = 'feed/types/grouped_link';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritDoc
     */
    public function apply(): self
    {
        $connection = $this->moduleDataSetup->getConnection();
        $connection->startSetup();

        $this->resolveCustomCronFrequency($connection);
        $this->moveGroupedLink($connection);

        $connection->endSetup();

        return $this;
    }

    /**
     * A stored "custom" becomes the expression from Custom Frequency of the same scope; without one
     * the row is removed so the config.xml default applies instead of an expression cron rejects.
     *
     * @param AdapterInterface $connection
     * @return void
     */
    private function resolveCustomCronFrequency(AdapterInterface $connection): void
    {
        $table = $this->table();
        $select = $connection->select()
            ->from($table, ['config_id', 'scope', 'scope_id'])
            ->where('path = ?', FeedInterface::XPATH_CRON_FREQUENCY)
            ->where('value = ?', Cron::CUSTOM);

        foreach ($connection->fetchAll($select) as $row) {
            $expression = $connection->fetchOne(
                $connection->select()
                    ->from($table, ['value'])
                    ->where('path = ?', Cron::CUSTOM_FREQUENCY_PATH)
                    ->where('scope = ?', $row['scope'])
                    ->where('scope_id = ?', (int)$row['scope_id'])
            );
            $expression = is_string($expression) ? trim($expression) : '';

            if ($expression === '' || $expression === Cron::CUSTOM) {
                $connection->delete($table, ['config_id = ?' => (int)$row['config_id']]);
                continue;
            }

            $connection->update($table, ['value' => $expression], ['config_id = ?' => (int)$row['config_id']]);
        }
    }

    /**
     * Move the grouped link setting to the path the feed reads, unless that path is already set for
     * the scope.
     *
     * @param AdapterInterface $connection
     * @return void
     */
    private function moveGroupedLink(AdapterInterface $connection): void
    {
        $table = $this->table();
        $select = $connection->select()
            ->from($table, ['config_id', 'scope', 'scope_id'])
            ->where('path = ?', self::OLD_GROUPED_LINK_PATH);

        foreach ($connection->fetchAll($select) as $row) {
            $exists = $connection->fetchOne(
                $connection->select()
                    ->from($table, ['config_id'])
                    ->where('path = ?', FeedInterface::XML_PATH_GROUPED_LINK)
                    ->where('scope = ?', $row['scope'])
                    ->where('scope_id = ?', (int)$row['scope_id'])
            );

            if ($exists) {
                $connection->delete($table, ['config_id = ?' => (int)$row['config_id']]);
                continue;
            }

            $connection->update(
                $table,
                ['path' => FeedInterface::XML_PATH_GROUPED_LINK],
                ['config_id = ?' => (int)$row['config_id']]
            );
        }
    }

    /**
     * @return string
     */
    private function table(): string
    {
        return $this->moduleDataSetup->getTable('core_config_data');
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
}

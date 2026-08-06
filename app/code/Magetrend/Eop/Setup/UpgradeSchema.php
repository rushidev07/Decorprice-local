<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.3 or later
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */

namespace Magetrend\Eop\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade script class
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Schema upgrade script
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.0.1') < 0) {
            $this->upgrade201($setup);
        }

        if (version_compare($context->getVersion(), '2.0.2') < 0) {
            $this->upgrade202($setup);
        }

        if (version_compare($context->getVersion(), '2.0.3') < 0) {
            $this->upgrade203($setup);
        }

        $setup->endSetup();
    }

    /**
     * Upgrade script from 2.0.0 version
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    public function upgrade201(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('mt_eop_campaign');
        $connection = $setup->getConnection();

        $connection->addColumn(
            $tableName,
            'delay_time',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => false,
                'default' => 0,
                'length' => '5',
                'comment' => 'Popup delay time'
            ]
        );

        $connection->addColumn(
            $tableName,
            'show_event',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'length' => '50',
                'comment' => 'Show on event',
            ]
        );

        $connection->addColumn(
            $tableName,
            'show_device',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'length' => '50',
                'comment' => 'Show on device',
            ]
        );
    }

    public function upgrade202($setup)
    {
        $tableName = $setup->getTable('mt_eop_field');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $connection = $setup->getConnection();
            $connection->addColumn($tableName, 'default_value', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Default field value',
            ]);

            $connection->addColumn($tableName, 'frontend_label', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Frontend Label',
            ]);

            $connection->addColumn($tableName, 'error_message', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Error Messages',
            ]);
        }

        $tableName = $setup->getTable('newsletter_subscriber');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $connection = $setup->getConnection();
            $connection->addColumn($tableName, 'created_at', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                'nullable' => true,
                'comment' => 'Created At',
            ]);
        }
    }

    public function upgrade203($setup)
    {
        $tableName = $setup->getTable('mt_eop_campaign');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $connection = $setup->getConnection();
            $connection->addColumn($tableName, 'conditions_serialized', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '2M',
                'comment' => 'Conditions Serialized',
            ]);
        }
    }
}

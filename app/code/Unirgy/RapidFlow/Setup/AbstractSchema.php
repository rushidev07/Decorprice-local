<?php
/**
 * Created by pp
 *
 * @project magento2
 */

namespace Unirgy\RapidFlow\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class AbstractSchema
{
    public function createHistoryTable(SchemaSetupInterface $setup)
    {
        $installer = $setup;
        $tableName = $installer->getTable('urapidflow_history');

        $connection = $installer->getConnection();
        if ($connection->isTableExists($tableName) != true) {
            $table = $connection->newTable($tableName)
                ->addColumn('history_id', Table::TYPE_INTEGER, 10, [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ])
                ->addColumn('profile_id', Table::TYPE_INTEGER, 10, ['nullable' => false,'unsigned' => true])
                ->addColumn('current_activity', Table::TYPE_TEXT, 100, ['nullable' => false])
                ->addColumn('rows_found', Table::TYPE_INTEGER, 10, ['nullable' => false])
                ->addColumn('rows_processed', Table::TYPE_INTEGER, 10, ['nullable' => false])
                ->addColumn('rows_success', Table::TYPE_INTEGER, 10, ['nullable' => false])
                ->addColumn('rows_nochange', Table::TYPE_INTEGER, 10, ['nullable' => false])
                ->addColumn('rows_empty', Table::TYPE_INTEGER, 10, ['nullable' => false])
                ->addColumn('rows_depends', Table::TYPE_INTEGER, 10, ['nullable' => false])
                ->addColumn('rows_errors', Table::TYPE_INTEGER, 10, ['nullable' => false])
                ->addColumn('num_errors', Table::TYPE_INTEGER, 10, ['nullable' => false])
                ->addColumn('num_warnings', Table::TYPE_INTEGER, 10, ['nullable' => false])
                ->addColumn('crunch_rate', Table::TYPE_INTEGER, 10, ['nullable' => false])
                ->addColumn('memory_usage', Table::TYPE_INTEGER, 10, ['nullable' => false])
                ->addColumn('memory_peak_usage', Table::TYPE_INTEGER, 10, ['nullable' => false])
                ->addColumn('runtime', Table::TYPE_INTEGER, 10, ['nullable' => false])
                ->addColumn('percent', Table::TYPE_INTEGER, 10, ['nullable' => false])
                ->addColumn('runtime_string', Table::TYPE_TEXT, 100, ['nullable' => false])
                ->addColumn('crunch_rate_string', Table::TYPE_TEXT, 100, ['nullable' => false])
                ->addColumn('started_at', Table::TYPE_DATETIME, null, ['nullable' => true])
                ->addColumn('snapshot_at', Table::TYPE_DATETIME, null, ['nullable' => true])
                ->addIndex(
                    $installer->getIdxName($tableName, ['profile_id']),
                    ['profile_id']
                )
                ->addForeignKey(
                    $installer->getFkName($tableName, 'profile_id', 'urapidflow_profile', 'profile_id'),
                    'profile_id',
                    $installer->getTable('urapidflow_profile'),
                    'profile_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->setComment('Rapidflow Import History')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $connection->createTable($table);
        }
    }
}


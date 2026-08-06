<?php
/**
 * Created by pp
 *
 * @project magento2
 */

namespace Unirgy\RapidFlow\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema extends AbstractSchema implements UpgradeSchemaInterface
{
    const MEDIUMTEXT_SIZE=16777216;
    const TEXT_SIZE=65536;
    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $tableName = $installer->getTable(\Unirgy\RapidFlow\Model\ResourceModel\AbstractResource::TABLE_CATALOG_PRODUCT_ENTITY_MEDIA_GALLERY);

        $connection = $installer->getConnection();

        $indexList = $connection->getIndexList($tableName);

        if(!in_array('CATALOG_PRODUCT_ENTITY_MEDIA_GALLERY_VALUE', $indexList)) {
            $connection->addIndex($tableName, 'CATALOG_PRODUCT_ENTITY_MEDIA_GALLERY_VALUE', 'value');
        }

        if (version_compare($context->getVersion(), '3.0.76', '<')) {
            $this->createHistoryTable($setup);
        }

        if (version_compare($context->getVersion(), '3.0.116', '<')) {
            $pTableName = $installer->getTable('urapidflow_profile');
            $connection->modifyColumn($pTableName, 'columns_json', ['TYPE' => Table::TYPE_TEXT, 'LENGTH'=>self::MEDIUMTEXT_SIZE, 'COMMENT' => 'columns_json']);
        }
    }
}


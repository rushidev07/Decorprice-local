<?php

namespace Unirgy\Dropship\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    const LONGTEXT_SIZE=4294967295;
    const MEDIUMTEXT_SIZE=16777216;
    const TEXT_SIZE=65536;

    protected $_hlp;
    protected $_moduleManager;
    public function __construct(
        \Unirgy\Dropship\Helper\Data $udropshipHelper,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->_hlp = $udropshipHelper;
        $this->_moduleManager = $moduleManager;
    }
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $installer = $setup;
        $connection = $setup->getConnection();
        if (version_compare($context->getVersion(), '3.2.13', '<')) {
            $lblBatchTable = $setup->getTable('udropship_label_batch');
            $connection->addColumn($lblBatchTable, 'items_count', ['TYPE'=>Table::TYPE_INTEGER,'nullable' => false,'COMMENT'=>'items_count']);
            $connection->addColumn($lblBatchTable, 'items_processed', ['TYPE'=>Table::TYPE_INTEGER,'nullable' => false,'COMMENT'=>'items_processed']);
            $connection->addColumn($lblBatchTable, 'status', ['TYPE'=>Table::TYPE_TEXT,'LENGTH'=>64,'nullable' => false,'DEFAULT'=>\Unirgy\Dropship\Model\Label\Batch::STATUS_COMPLETE,'COMMENT'=>'status']);
        }
        if (version_compare($context->getVersion(), '3.2.48', '<')) {
            $vpAssocTable = $setup->getTable('udropship_vendor_product_assoc');
            $connection->addColumn($vpAssocTable, 'as_parent', ['TYPE'=>Table::TYPE_SMALLINT,'nullable' => false,'COMMENT'=>'as_parent']);
            $connection->addColumn($vpAssocTable, 'as_parent_udmulti', ['TYPE'=>Table::TYPE_SMALLINT,'nullable' => false,'COMMENT'=>'as_parent_udmulti']);
        }
        if (version_compare($context->getVersion(), '3.2.111', '<')) {
            $lblBatchTable = $setup->getTable('udropship_label_batch');
            $connection->addColumn($lblBatchTable, 'po_ids', ['TYPE'=>Table::TYPE_TEXT,'LENGTH'=>self::MEDIUMTEXT_SIZE,'nullable' => false,'COMMENT'=>'items_count']);
        }

        if (version_compare($context->getVersion(), '3.2.119', '<')) {
            $vendorTable = $setup->getTable('udropship_vendor');
            $connection->modifyColumn($vendorTable, 'created_at', ['TYPE'=>Table::TYPE_TIMESTAMP, 'nullable' => false, 'default' => Table::TIMESTAMP_INIT, 'COMMENT'=>'created_at']);
            $connection->addColumn($vendorTable, 'updated_at', ['TYPE'=>Table::TYPE_TIMESTAMP, 'nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE, 'COMMENT'=>'updated_at']);
        }

        $setup->endSetup();
    }
}

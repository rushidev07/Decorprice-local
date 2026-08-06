<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    \Unirgy\Dropship
 * @copyright  Copyright (c) 2015-2016 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\DropshipPo\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $connection = $setup->getConnection();

        if (version_compare($context->getVersion(), '3.1.16', '<')) {
            $udpoGridTable = $setup->getTable('udropship_po_grid');
            $connection->addColumn($udpoGridTable, 'updated_at', ['TYPE'=>Table::TYPE_TIMESTAMP, 'nullable' => true, 'COMMENT'=>'updated_at']);
        }
        if (version_compare($context->getVersion(), '3.2.28', '<')) {
            $udpoCommentTable = $setup->getTable('udropship_po_comment');
            $connection->addColumn($udpoCommentTable, 'is_vendor_notified', ['TYPE'=>Table::TYPE_SMALLINT, 'nullable' => true, 'unsigned'=>true, 'COMMENT'=>'updated_at']);
        }

        $setup->endSetup();
    }
}
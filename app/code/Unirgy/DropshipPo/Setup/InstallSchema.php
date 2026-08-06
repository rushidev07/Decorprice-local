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
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    const MEDIUMTEXT_SIZE=16777216;
    const TEXT_SIZE=65536;
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        $udpoTable = $installer->getTable('udropship_po');
        $table = $connection->newTable($udpoTable)
            ->addColumn('entity_id', Table::TYPE_INTEGER, 10, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true
            ])
            ->addColumn('store_id', Table::TYPE_SMALLINT, null, ['unsigned' => true,'nullable' => false])
            ->addColumn('email_sent', Table::TYPE_SMALLINT, null, ['unsigned' => true,'nullable' => false])
            ->addColumn('udropship_vendor', Table::TYPE_INTEGER, 10, ['unsigned' => true,'nullable' => false])
            ->addColumn('udropship_status', Table::TYPE_SMALLINT, null, ['unsigned' => true,'nullable' => false])
            ->addColumn('order_id', Table::TYPE_INTEGER, 10, ['unsigned' => true,'nullable' => false])
            ->addColumn('customer_id', Table::TYPE_INTEGER, 10, ['unsigned' => true,'nullable' => false])
            ->addColumn('shipping_address_id', Table::TYPE_INTEGER, 10, ['unsigned' => true,'nullable' => false])
            ->addColumn('billing_address_id', Table::TYPE_INTEGER, 10, ['unsigned' => true,'nullable' => false])
            ->addColumn('increment_id', Table::TYPE_TEXT, 50, ['nullable' => false])
            ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false,'default' => Table::TIMESTAMP_INIT])
            ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE])
            ->addColumn('udropship_available_at', Table::TYPE_DATETIME, null, ['nullable' => true])
            ->addColumn('udropship_method', Table::TYPE_TEXT, 64, ['nullable' => false])
            ->addColumn('udropship_method_description', Table::TYPE_TEXT, 128, ['nullable' => false])
            ->addColumn('total_weight', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('total_qty', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('base_total_value', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('total_value', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('base_shipping_amount', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('shipping_amount', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('base_tax_amount', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('total_cost', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('base_discount_amount', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('base_shipping_tax', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('shipping_tax', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('base_shipping_amount_incl', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('shipping_amount_incl', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('base_hidden_tax_amount', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('transaction_fee', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('commission_percent', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('handling_fee', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('udropship_shipcheck', Table::TYPE_TEXT, 5, ['nullable' => true])
            ->addColumn('udropship_vendor_order_id', Table::TYPE_TEXT, 30, ['nullable' => true])
            ->addColumn('udropship_batch_status', Table::TYPE_TEXT, 20, ['nullable' => true])
            ->addColumn('statement_id', Table::TYPE_TEXT, 30, ['nullable' => true])
            ->addColumn('statement_date', Table::TYPE_DATETIME, null, ['nullable' => true])
            ->addColumn('udropship_payout_status', Table::TYPE_TEXT, 20, ['nullable' => true])
            ->addColumn('payout_id', Table::TYPE_INTEGER, 10, ['unsigned' => true,'nullable' => true])
            ->addColumn('is_manual', Table::TYPE_SMALLINT, null, ['unsigned' => true,'nullable' => true])
            ->addColumn('is_virtual', Table::TYPE_SMALLINT, null, ['unsigned' => true,'nullable' => true])
            ->addColumn('is_vendor_notified', Table::TYPE_SMALLINT, null, ['unsigned' => true,'nullable' => true])
            ->addIndex(
                $installer->getIdxName($udpoTable, ['store_id']),
                ['store_id']
            )
            ->addIndex(
                $installer->getIdxName($udpoTable, ['total_qty']),
                ['total_qty']
            )
            ->addIndex(
                $installer->getIdxName($udpoTable, ['increment_id']),
                ['increment_id']
            )
            ->addIndex(
                $installer->getIdxName($udpoTable, ['order_id']),
                ['order_id']
            )
            ->addIndex(
                $installer->getIdxName($udpoTable, ['udropship_vendor']),
                ['udropship_vendor']
            )
            ->addIndex(
                $installer->getIdxName($udpoTable, ['udropship_status']),
                ['udropship_status']
            )
            ->setComment('Advanced PO Purchase Orders Table')
            ->setOption('type', 'InnoDB')
            ->setOption('charset', 'utf8');
        $connection->createTable($table);

        $udpoGridTable = $installer->getTable('udropship_po_grid');
        $table = $connection->newTable($udpoGridTable)
            ->addColumn('entity_id', Table::TYPE_INTEGER, 10, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true
            ])
            ->addColumn('store_id', Table::TYPE_SMALLINT, null, ['unsigned' => true,'nullable' => false])
            ->addColumn('udropship_vendor', Table::TYPE_INTEGER, 10, ['unsigned' => true,'nullable' => false])
            ->addColumn('udropship_status', Table::TYPE_SMALLINT, null, ['unsigned' => true,'nullable' => false])
            ->addColumn('order_id', Table::TYPE_INTEGER, 10, ['unsigned' => true,'nullable' => false])
            ->addColumn('increment_id', Table::TYPE_TEXT, 50, ['nullable' => false])
            ->addColumn('order_increment_id', Table::TYPE_TEXT, 50, ['nullable' => false])
            ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, ['nullable' => true])
            ->addColumn('order_created_at', Table::TYPE_TIMESTAMP, null, ['nullable' => true])
            ->addColumn('shipping_name', Table::TYPE_TEXT, 64, ['nullable' => false])
            ->addColumn('udropship_method', Table::TYPE_TEXT, 64, ['nullable' => false])
            ->addColumn('udropship_method_description', Table::TYPE_TEXT, 128, ['nullable' => true])
            ->addColumn('total_qty', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('base_total_value', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('total_value', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('base_shipping_amount', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('shipping_amount', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('base_tax_amount', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('total_cost', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('base_discount_amount', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('transaction_fee', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('commission_percent', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('statement_id', Table::TYPE_TEXT, 30, ['nullable' => true])
            ->addColumn('statement_date', Table::TYPE_DATETIME, null, ['nullable' => true])
            ->addColumn('is_manual', Table::TYPE_SMALLINT, null, ['unsigned' => true,'nullable' => true])
            ->addColumn('is_virtual', Table::TYPE_SMALLINT, null, ['unsigned' => true,'nullable' => true])
            ->addColumn('udropship_payout_status', Table::TYPE_TEXT, 20, ['nullable' => true])
            ->addColumn('payout_id', Table::TYPE_INTEGER, 10, ['unsigned' => true,'nullable' => true])
            ->addIndex(
                $installer->getIdxName($udpoGridTable, ['store_id']),
                ['store_id']
            )
            ->addIndex(
                $installer->getIdxName($udpoGridTable, ['total_qty']),
                ['total_qty']
            )
            ->addIndex(
                $installer->getIdxName($udpoGridTable, ['increment_id']),
                ['increment_id']
            )
            ->addIndex(
                $installer->getIdxName($udpoGridTable, ['order_id']),
                ['order_id']
            )
            ->addIndex(
                $installer->getIdxName($udpoGridTable, ['order_increment_id']),
                ['order_increment_id']
            )
            ->addIndex(
                $installer->getIdxName($udpoGridTable, ['created_at']),
                ['created_at']
            )
            ->addIndex(
                $installer->getIdxName($udpoGridTable, ['order_created_at']),
                ['order_created_at']
            )
            ->addIndex(
                $installer->getIdxName($udpoGridTable, ['shipping_name']),
                ['shipping_name']
            )
            ->addIndex(
                $installer->getIdxName($udpoGridTable, ['udropship_vendor']),
                ['udropship_vendor']
            )
            ->addIndex(
                $installer->getIdxName($udpoGridTable, ['udropship_status']),
                ['udropship_status']
            )
            ->addIndex(
                $installer->getIdxName($udpoGridTable, ['udropship_method']),
                ['udropship_method']
            )
            ->addIndex(
                $installer->getIdxName($udpoGridTable, ['statement_id']),
                ['statement_id']
            )
            ->addIndex(
                $installer->getIdxName($udpoGridTable, ['statement_date']),
                ['statement_date']
            )
            ->addIndex(
                $installer->getIdxName($udpoGridTable, ['payout_id']),
                ['payout_id']
            )
            ->setComment('Advanced PO Purchase Orders Grid Table')
            ->setOption('type', 'InnoDB')
            ->setOption('charset', 'utf8');
        $connection->createTable($table);

        $udpoItemTable = $installer->getTable('udropship_po_item');
        $table = $connection->newTable($udpoItemTable)
            ->addColumn('entity_id', Table::TYPE_INTEGER, 10, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true
            ])
            ->addColumn('parent_id', Table::TYPE_INTEGER, 10, ['unsigned' => true,'nullable' => false])
            ->addColumn('product_id', Table::TYPE_INTEGER, 10, ['unsigned' => true,'nullable' => false])
            ->addColumn('order_item_id', Table::TYPE_INTEGER, 10, ['unsigned' => true,'nullable' => false])
            ->addColumn('price', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('weight', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('qty', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('qty_shipped', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('base_cost', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('qty_invoiced', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('qty_canceled', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('commission_percent', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('transaction_fee', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('base_tax_amount', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('base_hidden_tax_amount', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('base_discount_amount', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('base_row_total', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('row_total', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('name', Table::TYPE_TEXT, 255, ['nullable' => false])
            ->addColumn('sku', Table::TYPE_TEXT, 255, ['nullable' => false])
            ->addColumn('vendor_sku', Table::TYPE_TEXT, 255, ['nullable' => false])
            ->addColumn('vendor_simple_sku', Table::TYPE_TEXT, 255, ['nullable' => false])
            ->addColumn('additional_data', Table::TYPE_TEXT, self::TEXT_SIZE, ['nullable' => true])
            ->addColumn('description', Table::TYPE_TEXT, self::TEXT_SIZE, ['nullable' => true])
            ->addColumn('is_virtual', Table::TYPE_SMALLINT, null, ['unsigned' => true,'nullable' => true])
            ->addIndex(
                $installer->getIdxName($udpoItemTable, ['parent_id']),
                ['parent_id']
            )
            ->setComment('Advanced PO Purchase Orders Items Table')
            ->setOption('type', 'InnoDB')
            ->setOption('charset', 'utf8');
        $connection->createTable($table);

        $udpoCommentTable = $installer->getTable('udropship_po_comment');
        $table = $connection->newTable($udpoCommentTable)
            ->addColumn('entity_id', Table::TYPE_INTEGER, 10, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true
            ])
            ->addColumn('parent_id', Table::TYPE_INTEGER, 10, ['unsigned' => true,'nullable' => false])
            ->addColumn('is_customer_notified', Table::TYPE_SMALLINT, null, ['unsigned' => true,'nullable' => true])
            ->addColumn('is_vendor_notified', Table::TYPE_SMALLINT, null, ['unsigned' => true,'nullable' => true])
            ->addColumn('is_visible_to_vendor', Table::TYPE_SMALLINT, null, ['unsigned' => true,'nullable' => true])
            ->addColumn('udropship_status', Table::TYPE_TEXT, 64, ['nullable' => true])
            ->addColumn('username', Table::TYPE_TEXT, 40, ['nullable' => true])
            ->addColumn('comment', Table::TYPE_TEXT, self::TEXT_SIZE, ['nullable' => true])
            ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false,'default' => Table::TIMESTAMP_INIT])
            ->addIndex(
                $installer->getIdxName($udpoCommentTable, ['created_at']),
                ['created_at']
            )
            ->addIndex(
                $installer->getIdxName($udpoCommentTable, ['parent_id']),
                ['parent_id']
            )
            ->setComment('Advanced PO Purchase Orders Comments Table')
            ->setOption('type', 'InnoDB')
            ->setOption('charset', 'utf8');
        $connection->createTable($table);

        $orderItemTable = $installer->getTable('sales_order_item');
        $connection->addColumn($orderItemTable, 'locked_do_udpo', ['TYPE'=>Table::TYPE_SMALLINT,'nullable' => true,'COMMENT'=>'locked_do_udpo']);
        $connection->addColumn($orderItemTable, 'qty_udpo', ['TYPE'=>Table::TYPE_DECIMAL,'LENGTH'=>'12,4','nullable' => true,'default'=>0,'COMMENT'=>'qty_udpo']);
        $connection->addColumn($orderItemTable, 'udpo_qty_used', ['TYPE'=>Table::TYPE_DECIMAL,'LENGTH'=>'12,4','nullable' => true,'default'=>0,'COMMENT'=>'udpo_qty_used']);
        $connection->addColumn($orderItemTable, 'udpo_qty_reverted', ['TYPE'=>Table::TYPE_DECIMAL,'LENGTH'=>'12,4','nullable' => true,'default'=>0,'COMMENT'=>'udpo_qty_reverted']);

        $shipmentTable = $installer->getTable('sales_shipment');
        $connection->addColumn($shipmentTable, 'udpo_id', ['TYPE'=>Table::TYPE_INTEGER,'nullable' => true,'unsigned' => true,'COMMENT'=>'udpo_id']);
        $connection->addColumn($shipmentTable, 'udpo_increment_id', ['TYPE'=>Table::TYPE_TEXT,'nullable' => true,'LENGTH'=>50,'COMMENT'=>'udpo_increment_id']);

        $shipmentItemTable = $installer->getTable('sales_shipment_item');
        $connection->addColumn($shipmentItemTable, 'udpo_item_id', ['TYPE'=>Table::TYPE_INTEGER,'nullable' => true,'unsigned' => true,'COMMENT'=>'udpo_item_id']);

        $invoiceTable = $installer->getTable('sales_invoice');
        $connection->addColumn($invoiceTable, 'udpo_id', ['TYPE'=>Table::TYPE_INTEGER,'nullable' => true,'unsigned' => true,'COMMENT'=>'udpo_id']);
        $connection->addColumn($invoiceTable, 'shipment_id', ['TYPE'=>Table::TYPE_INTEGER,'nullable' => true,'unsigned' => true,'COMMENT'=>'shipment_id']);

        $invoiceItemTable = $installer->getTable('sales_invoice_item');
        $connection->addColumn($invoiceItemTable, 'udpo_item_id', ['TYPE'=>Table::TYPE_INTEGER,'nullable' => true,'unsigned' => true,'COMMENT'=>'udpo_item_id']);

        $invoiceGridTable = $installer->getTable('sales_invoice_grid');
        $connection->addColumn($invoiceGridTable, 'udpo_id', ['TYPE'=>Table::TYPE_INTEGER,'nullable' => true,'unsigned' => true,'COMMENT'=>'udpo_id']);

        $shipmentGridTable = $installer->getTable('sales_shipment_grid');
        $connection->addColumn($shipmentGridTable, 'udpo_id', ['TYPE'=>Table::TYPE_INTEGER,'nullable' => true,'unsigned' => true,'COMMENT'=>'udpo_id']);

        $connection->addIndex(
            $setup->getTable($shipmentGridTable),
            $connection->getIndexName(
                $setup->getTable($shipmentGridTable),
                'udpo_id',
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            ),
            'udpo_id'
        );
        $connection->addIndex(
            $setup->getTable($invoiceGridTable),
            $connection->getIndexName(
                $setup->getTable($invoiceGridTable),
                'udpo_id',
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            ),
            'udpo_id'
        );

        $orderTable = $installer->getTable('sales_order');
        $connection->addColumn($orderTable, 'udpo_amount_fields', ['TYPE'=>Table::TYPE_SMALLINT,'nullable' => true,'COMMENT'=>'udpo_id']);

        $orderItemTable = $installer->getTable('sales_order');
        $connection->addColumn($orderItemTable, 'udpo_base_tax_amount', ['TYPE'=>Table::TYPE_DECIMAL,'LENGTH'=>'12,4','nullable' => false,'default'=>0,'COMMENT'=>'udpo_base_tax_amount']);
        $connection->addColumn($orderItemTable, 'udpo_base_hidden_tax_amount', ['TYPE'=>Table::TYPE_DECIMAL,'LENGTH'=>'12,4','nullable' => false,'default'=>0,'COMMENT'=>'udpo_base_hidden_tax_amount']);
        $connection->addColumn($orderItemTable, 'udpo_base_discount_amount', ['TYPE'=>Table::TYPE_DECIMAL,'LENGTH'=>'12,4','nullable' => false,'default'=>0,'COMMENT'=>'udpo_base_discount_amount']);
        $connection->addColumn($orderItemTable, 'udpo_base_row_total', ['TYPE'=>Table::TYPE_DECIMAL,'LENGTH'=>'12,4','nullable' => false,'default'=>0,'COMMENT'=>'udpo_base_row_total']);
        $connection->addColumn($orderItemTable, 'udpo_row_total', ['TYPE'=>Table::TYPE_DECIMAL,'LENGTH'=>'12,4','nullable' => false,'default'=>0,'COMMENT'=>'udpo_row_total']);

        $storeTable = $installer->getTable('store');

        $connection->addForeignKey(
            $installer->getFkName($shipmentTable, 'udpo_id', $udpoTable, 'entity_id'),
            $shipmentTable, 'udpo_id', $udpoTable, 'entity_id'
        );
        $connection->addForeignKey(
            $installer->getFkName($shipmentItemTable, 'udpo_item_id', $udpoItemTable, 'entity_id'),
            $shipmentItemTable, 'udpo_item_id', $udpoItemTable, 'entity_id'
        );
        $connection->addForeignKey(
            $installer->getFkName($udpoTable, 'order_id', $orderTable, 'entity_id'),
            $udpoTable, 'order_id', $orderTable, 'entity_id'
        );
        $connection->addForeignKey(
            $installer->getFkName($udpoTable, 'store_id', $storeTable, 'store_id'),
            $udpoTable, 'store_id', $storeTable, 'store_id'
        );
        $connection->addForeignKey(
            $installer->getFkName($udpoGridTable, 'entity_id', $udpoTable, 'entity_id'),
            $udpoGridTable, 'entity_id', $udpoTable, 'entity_id'
        );
        $connection->addForeignKey(
            $installer->getFkName($udpoGridTable, 'store_id', $storeTable, 'store_id'),
            $udpoGridTable, 'store_id', $storeTable, 'store_id'
        );
        $connection->addForeignKey(
            $installer->getFkName($udpoItemTable, 'parent_id', $udpoTable, 'entity_id'),
            $udpoItemTable, 'parent_id', $udpoTable, 'entity_id'
        );
        $connection->addForeignKey(
            $installer->getFkName($udpoCommentTable, 'parent_id', $udpoTable, 'entity_id'),
            $udpoCommentTable, 'parent_id', $udpoTable, 'entity_id'
        );


        $installer->endSetup();
    }
}

<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RMA
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RMA\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

/**
 * Class InstallSchema
 * @package Mageplaza\RMA\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (!$installer->tableExists('mageplaza_rma_status')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_rma_status'))
                ->addColumn('status_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true,
                ], 'Status ID')
                ->addColumn('name', Table::TYPE_TEXT, 255, ['nullable' => false], 'Status Title')
                ->addColumn('label', Table::TYPE_TEXT, 255, ['nullable' => false], 'Status Label')
                ->addColumn('comment', Table::TYPE_TEXT, 255, [], 'Status Comment')
                ->addColumn('enable_comment', Table::TYPE_SMALLINT, null, [], 'Enable Status Comment')
                ->addColumn('is_active', Table::TYPE_SMALLINT, null, [], 'Is Active')
                ->addColumn('description', Table::TYPE_TEXT, '64k', [], 'Description')
                ->addColumn(
                    'allow_action',
                    Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ],
                    'Allow Action'
                )
                ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, [], 'Updated At')
                ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, [], 'Created At')
                ->setComment('RMA Status Table');

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('mageplaza_rma_status_label')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_rma_status_label'))
                ->addColumn('status_id', Table::TYPE_INTEGER, null, [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false
                ], 'Status ID')
                ->addColumn('store_id', Table::TYPE_SMALLINT, null, [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false
                ], 'Store ID')
                ->addColumn('label', Table::TYPE_TEXT, 255, ['nullable => false'], 'Label')
                ->addIndex($installer->getIdxName('mageplaza_rma_status_label', ['status_id']), ['status_id'])
                ->addIndex($installer->getIdxName('mageplaza_rma_status_label', ['store_id']), ['store_id'])
                ->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_rma_status_label',
                        'status_id',
                        'mageplaza_rma_status',
                        'status_id'
                    ),
                    'status_id',
                    $installer->getTable('mageplaza_rma_status'),
                    'status_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName('mageplaza_rma_status_label', 'store_id', 'store', 'store_id'),
                    'store_id',
                    $installer->getTable('store'),
                    'store_id',
                    Table::ACTION_CASCADE
                )
                ->addIndex(
                    $installer->getIdxName('mageplaza_rma_status_label', [
                        'status_id',
                        'store_id'
                    ], AdapterInterface::INDEX_TYPE_UNIQUE),
                    ['status_id', 'store_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->setComment('RMA Status Label Table');

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('mageplaza_rma_status_comment')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_rma_status_comment'))
                ->addColumn('status_id', Table::TYPE_INTEGER, null, [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false
                ], 'Status ID')
                ->addColumn('store_id', Table::TYPE_SMALLINT, null, [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false
                ], 'Store ID')
                ->addColumn('comment', Table::TYPE_TEXT, 255, ['nullable => false'], 'Comment')
                ->addIndex($installer->getIdxName('mageplaza_rma_status_comment', ['status_id']), ['status_id'])
                ->addIndex($installer->getIdxName('mageplaza_rma_status_comment', ['store_id']), ['store_id'])
                ->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_rma_status_comment',
                        'status_id',
                        'mageplaza_rma_status',
                        'status_id'
                    ),
                    'status_id',
                    $installer->getTable('mageplaza_rma_status'),
                    'status_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName('mageplaza_rma_status_comment', 'store_id', 'store', 'store_id'),
                    'store_id',
                    $installer->getTable('store'),
                    'store_id',
                    Table::ACTION_CASCADE
                )
                ->addIndex(
                    $installer->getIdxName('mageplaza_rma_status_comment', [
                        'status_id',
                        'store_id'
                    ], AdapterInterface::INDEX_TYPE_UNIQUE),
                    ['status_id', 'store_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->setComment('RMA Status Comment Table');

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('mageplaza_rma_rule')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_rma_rule'))
                ->addColumn('rule_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true,
                ], 'Rule ID')
                ->addColumn('name', Table::TYPE_TEXT, 255, ['nullable' => false], 'Rule Name')
                ->addColumn('description', Table::TYPE_TEXT, '64k', [], 'Description')
                ->addColumn('status', Table::TYPE_SMALLINT, null, [], 'Rule Status')
                ->addColumn('websites', Table::TYPE_TEXT, null, [], 'Website Ids')
                ->addColumn('customer_group', Table::TYPE_TEXT, null, [
                    'nullable' => false,
                    'unsigned' => true
                ], 'Customer Groups')
                ->addColumn('conditions_serialized', Table::TYPE_TEXT, '2M', [], 'Conditions Serialized')
                ->addColumn('reason', Table::TYPE_TEXT, null, [], 'Reason')
                ->addColumn('solution', Table::TYPE_TEXT, null, [], 'Solution')
                ->addColumn('additional_field', Table::TYPE_TEXT, null, [], 'Additional Field')
                ->addColumn('priority', Table::TYPE_SMALLINT, null, [
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0'
                ], 'Priority')
                ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, [], 'Updated At')
                ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, [], 'Created At')
                ->setComment('RMA Rule Table');

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('mageplaza_rma_request')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_rma_request'))
                ->addColumn('request_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true,
                ], 'Request ID')
                ->addColumn('order_id', Table::TYPE_INTEGER, null, [
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0'
                ], 'Order Id')
                ->addColumn('order_increment_id', Table::TYPE_TEXT, 32, [], 'Order Increment Id')
                ->addColumn('increment_id', Table::TYPE_TEXT, 50, [], 'Increment Id')
                ->addColumn('status_id', Table::TYPE_INTEGER, null, [
                    'unsigned' => true,
                    'nullable' => false
                ], 'Status ID')
                ->addColumn('is_canceled', Table::TYPE_INTEGER, null, [
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0'
                ], 'Is Canceled')
                ->addColumn('store_id', Table::TYPE_SMALLINT, null, [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false
                ], 'Store ID')
                ->addColumn('comment', Table::TYPE_TEXT, '64k', [], 'Comment')
                ->addColumn('files', Table::TYPE_TEXT, '2M', [], 'Files (Json)')
                ->addColumn('last_responded_by', Table::TYPE_TEXT, 255, [
                    'nullable' => false,
                    'default' => 'No replies'
                ], 'Last Responded')
                ->addColumn('customer_email', Table::TYPE_TEXT, 128, [], 'Customer Email')
                ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, [], 'Updated At')
                ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, [], 'Created At')
                ->addIndex($installer->getIdxName('mageplaza_rma_request', ['order_id']), ['order_id'])
                ->addIndex($installer->getIdxName('mageplaza_rma_request', ['store_id']), ['store_id'])
                ->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_rma_request',
                        'order_id',
                        'sales_order',
                        'entity_id'
                    ),
                    'order_id',
                    $installer->getTable('sales_order'),
                    'entity_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName('mageplaza_rma_request', 'store_id', 'store', 'store_id'),
                    'store_id',
                    $installer->getTable('store'),
                    'store_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('RMA Request Table');

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('mageplaza_rma_request_item')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_rma_request_item'))
                ->addColumn('item_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true,
                ], 'Item ID')
                ->addColumn('request_id', Table::TYPE_INTEGER, null, [
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true,
                ], 'Request ID')
                ->addColumn('product_id', Table::TYPE_INTEGER, null, ['unsigned' => true], 'Product Id')
                ->addColumn('order_item_id', Table::TYPE_INTEGER, null, ['unsigned' => true], 'Order Item Id')
                ->addColumn('name', Table::TYPE_TEXT, 255, [], 'Name')
                ->addColumn('sku', Table::TYPE_TEXT, 255, [], 'Sku')
                ->addColumn('qty_rma', Table::TYPE_DECIMAL, '12,4', ['default' => '0.0000'], 'Qty RMA')
                ->addColumn('price', Table::TYPE_DECIMAL, '12,4', ['nullable' => false, 'default' => '0.0000'], 'Price')
                ->addColumn('price_returned', Table::TYPE_DECIMAL, '12,4', [
                    'nullable' => false,
                    'default' => '0.0000'
                ], 'Price Returned')
                ->addColumn('reason', Table::TYPE_TEXT, 255, [], 'Reason')
                ->addColumn('solution', Table::TYPE_TEXT, 255, [], 'Solution')
                ->addColumn('additional_fields', Table::TYPE_TEXT, '2M', [], 'Additional Fields (Json)')
                ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, [], 'Created At')
                ->addIndex($installer->getIdxName('mageplaza_rma_request_item', ['request_id']), ['request_id'])
                ->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_rma_request_item',
                        'request_id',
                        'mageplaza_rma_request',
                        'request_id'
                    ),
                    'request_id',
                    $installer->getTable('mageplaza_rma_request'),
                    'request_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('RMA Request Item Table');

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('mageplaza_rma_request_shipping_label')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_rma_request_shipping_label'))
                ->addColumn('shipping_label_id', Table::TYPE_INTEGER, null, [
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true,
                ], 'Shipping Label ID')
                ->addColumn('request_id', Table::TYPE_INTEGER, null, [
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true,
                ], 'Request ID')
                ->addIndex($installer->getIdxName(
                    'mageplaza_rma_request_shipping_label',
                    ['request_id']
                ), ['request_id'])
                ->addIndex($installer->getIdxName(
                    'mageplaza_rma_request_shipping_label',
                    ['shipping_label_id']
                ), ['shipping_label_id'])
                ->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_rma_request_shipping_label',
                        'request_id',
                        'mageplaza_rma_request',
                        'request_id'
                    ),
                    'request_id',
                    $installer->getTable('mageplaza_rma_request'),
                    'request_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_rma_request_shipping_label',
                        'shipping_label_id',
                        'mageplaza_rma_shipping_label',
                        'shipping_label_id'
                    ),
                    'shipping_label_id',
                    $installer->getTable('mageplaza_rma_shipping_label'),
                    'shipping_label_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('RMA Request Shipping Label Relation Table');

            $installer->getConnection()->createTable($table);
        }

        if ($installer->tableExists('sales_order_item')) {
            $columns = [
                'mp_qty_rma' => [
                    'type' => Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'default' => '0.0000',
                    'comment' => 'Mageplaza Qty RMA'
                ]
            ];

            $orderItemTable = $installer->getTable('sales_order_item');
            foreach ($columns as $name => $definition) {
                $installer->getConnection()->addColumn($orderItemTable, $name, $definition);
            }
        }

        if (!$installer->tableExists('mageplaza_rma_request_reply')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_rma_request_reply'))
                ->addColumn('reply_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true,
                ], 'Reply ID')
                ->addColumn('request_id', Table::TYPE_INTEGER, null, [
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true,
                ], 'Request ID')
                ->addColumn('is_customer_notified', Table::TYPE_INTEGER, null, [
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0'
                ], 'Is Customer Notified')
                ->addColumn('is_visible_on_front', Table::TYPE_SMALLINT, null, [
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0'
                ], 'Is Visible On Front')
                ->addColumn('author_name', Table::TYPE_TEXT, 255, ['nullable' => false], 'Author Name')
                ->addColumn('type', Table::TYPE_SMALLINT, null, [], 'Reply Type')
                ->addColumn('content', Table::TYPE_TEXT, '64k', [], 'Reply Content')
                ->addColumn('files', Table::TYPE_TEXT, '2M', [], 'Files (Json)')
                ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, [], 'Created At')
                ->addIndex($installer->getIdxName('mageplaza_rma_request_reply', ['request_id']), ['request_id'])
                ->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_rma_request_reply',
                        'request_id',
                        'mageplaza_rma_request',
                        'request_id'
                    ),
                    'request_id',
                    $installer->getTable('mageplaza_rma_request'),
                    'request_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('RMA Request Reply Table');

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('mageplaza_rma_template')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_rma_template'))
                ->addColumn('template_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true,
                ], 'Template ID')
                ->addColumn('title', Table::TYPE_TEXT, 255, ['nullable' => false], 'Template Title')
                ->addColumn('content', Table::TYPE_TEXT, '64k', [], 'Content')
                ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, [], 'Updated At')
                ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, [], 'Created At')
                ->setComment('RMA Template Table');

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('mageplaza_rma_template_content')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_rma_template_content'))
                ->addColumn('template_id', Table::TYPE_INTEGER, null, [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false
                ], 'Template ID')
                ->addColumn('store_id', Table::TYPE_SMALLINT, null, [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false
                ], 'Store ID')
                ->addColumn('content', Table::TYPE_TEXT, '64k', [], 'Content')
                ->addIndex($installer->getIdxName('mageplaza_rma_template_content', ['template_id']), ['template_id'])
                ->addIndex($installer->getIdxName('mageplaza_rma_template_content', ['store_id']), ['store_id'])
                ->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_rma_template_content',
                        'template_id',
                        'mageplaza_rma_template',
                        'template_id'
                    ),
                    'template_id',
                    $installer->getTable('mageplaza_rma_template'),
                    'template_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName('mageplaza_rma_template_content', 'store_id', 'store', 'store_id'),
                    'store_id',
                    $installer->getTable('store'),
                    'store_id',
                    Table::ACTION_CASCADE
                )
                ->addIndex(
                    $installer->getIdxName(
                        'mageplaza_rma_template_content',
                        ['template_id', 'store_id'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['template_id', 'store_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->setComment('RMA Template Content Table');

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('mageplaza_rma_shipping_label')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_rma_shipping_label'))
                ->addColumn('shipping_label_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true,
                ], 'Shipping Label ID')
                ->addColumn('label', Table::TYPE_TEXT, 255, ['nullable' => false], 'Shipping Label')
                ->addColumn('name', Table::TYPE_TEXT, 255, ['nullable' => false], 'Shipping Name')
                ->addColumn('status', Table::TYPE_SMALLINT, null, [], 'Status')
                ->addColumn('description', Table::TYPE_TEXT, '64k', [], 'Description')
                ->addColumn('image', Table::TYPE_TEXT, 255, [], 'Shipping Logo')
                ->addColumn('barcode', Table::TYPE_TEXT, 255, [], 'Shipping Barcode')
                ->addColumn('information', Table::TYPE_TEXT, null, ['nullable' => false], 'Information')
                ->addColumn('conditions_serialized', Table::TYPE_TEXT, '2M', [], 'Conditions Serialized')
                ->addColumn('return_address', Table::TYPE_TEXT, '64k', [], 'Return Shipping Address')
                ->addColumn('store_id', Table::TYPE_TEXT, null, ['nullable' => false, 'unsigned' => true], 'Store Id')
                ->addColumn('priority', Table::TYPE_SMALLINT, null, [
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0'
                ], 'Priority')
                ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, [], 'Updated At')
                ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, [], 'Created At')
                ->setComment('RMA Shipping Table');

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('mageplaza_rma_shipping_label_label')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_rma_shipping_label_label'))
                ->addColumn('shipping_label_id', Table::TYPE_INTEGER, null, [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false
                ], 'Shipping Label ID')
                ->addColumn('store_id', Table::TYPE_SMALLINT, null, [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false
                ], 'Store ID')
                ->addColumn('label', Table::TYPE_TEXT, 255, ['nullable' => false], 'Shipping Label')
                ->addIndex($installer->getIdxName(
                    'mageplaza_rma_shipping_label_label',
                    ['shipping_label_id']
                ), ['shipping_label_id'])
                ->addIndex($installer->getIdxName('mageplaza_rma_shipping_label_label', ['store_id']), ['store_id'])
                ->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_rma_shipping_label_label',
                        'shipping_label_id',
                        'mageplaza_rma_shipping_label',
                        'shipping_label_id'
                    ),
                    'shipping_label_id',
                    $installer->getTable('mageplaza_rma_shipping_label'),
                    'shipping_label_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName('mageplaza_rma_shipping_label_label', 'store_id', 'store', 'store_id'),
                    'store_id',
                    $installer->getTable('store'),
                    'store_id',
                    Table::ACTION_CASCADE
                )
                ->addIndex(
                    $installer->getIdxName(
                        'mageplaza_rma_shipping_label_label',
                        ['shipping_label_id', 'store_id'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['shipping_label_id', 'store_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->setComment('RMA Shipping Label - Label Table');

            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}

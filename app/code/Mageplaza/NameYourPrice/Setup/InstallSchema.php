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
 * @package     Mageplaza_NameYourPrice
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\NameYourPrice\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

/**
 * Class InstallSchema
 * @package Mageplaza\NameYourPrice\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws Zend_Db_Exception
     * @SuppressWarnings(Unused)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        if ($installer->tableExists('mageplaza_mppricebargain_requests')) {
            $connection->dropTable($installer->getTable('mageplaza_mppricebargain_requests'));
        }
        $table = $connection
            ->newTable($installer->getTable('mageplaza_mppricebargain_requests'))
            ->addColumn('request_id', Table::TYPE_INTEGER, null, [
                'identity' => true,
                'nullable' => false,
                'primary' => true,
                'unsigned' => true
            ], 'Request ID')
            ->addColumn('product_id', Table::TYPE_INTEGER, null, [], 'Product Id')
            ->addColumn('product_name', Table::TYPE_TEXT, '2M', ['nullable => false'], 'Product Name')
            ->addColumn('sku', Table::TYPE_TEXT, 255, ['nullable => false'], 'Product Sku')
            ->addColumn('options_bundle', Table::TYPE_TEXT, '2M', ['nullable => false'], 'Selected Bundle Product')
            ->addColumn('serialize_bundle', Table::TYPE_TEXT, '2M', ['nullable => false'], 'Serialize Bundle Product')
            ->addColumn(
                'options_configurable',
                Table::TYPE_TEXT,
                '2M',
                ['nullable => false'],
                'Options Configurable Product'
            )
            ->addColumn('original_price', Table::TYPE_DECIMAL, '12,4', [], 'Original Price')
            ->addColumn('bargain_price', Table::TYPE_DECIMAL, '12,2', [], 'Bargain Price')
            ->addColumn('bargain_qty', Table::TYPE_INTEGER, null, [], 'Bargain Qty')
            ->addColumn('status', Table::TYPE_TEXT, 255, ['nullable => false'], 'Status')
            ->addColumn('customer_name', Table::TYPE_TEXT, 255, ['nullable => false'], 'Customer Name')
            ->addColumn('phone', Table::TYPE_TEXT, 255, ['nullable => false'], 'Customer Phone')
            ->addColumn('customer_email', Table::TYPE_TEXT, 255, ['nullable => false'], 'Customer Email')
            ->addColumn('store_ids', Table::TYPE_TEXT, null, [], 'Store Id')
            ->addColumn('customer_message', Table::TYPE_TEXT, '2M', ['nullable => false'], 'Customer Message')
            ->addColumn('admin_message', Table::TYPE_TEXT, '2M', ['nullable => false'], 'Admin Message')
            ->addColumn('submitted_date', Table::TYPE_TIMESTAMP, null, [], 'Created Date')
            ->addColumn('time_use', Table::TYPE_TIMESTAMP, null, [], 'Time Admin Approved')
            ->setComment('Bargain Price Requests');
        $connection->createTable($table);

        $installer->endSetup();
    }
}

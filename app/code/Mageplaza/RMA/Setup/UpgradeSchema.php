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

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 * @package Mageplaza\RMA\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $connection = $setup->getConnection();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            if ($setup->tableExists('mageplaza_rma_request')) {
                $connection->modifyColumn(
                    $setup->getTable('mageplaza_rma_request'),
                    'created_at',
                    [
                        'type' => Table::TYPE_TIMESTAMP,
                        'default' => Table::TIMESTAMP_INIT
                    ]
                )->modifyColumn(
                    $setup->getTable('mageplaza_rma_request'),
                    'updated_at',
                    [
                        'type' => Table::TYPE_TIMESTAMP,
                        'default' => Table::TIMESTAMP_INIT_UPDATE
                    ]
                );
            }

            if ($setup->tableExists('mageplaza_rma_request_item')) {
                $connection->modifyColumn(
                    $setup->getTable('mageplaza_rma_request_item'),
                    'created_at',
                    [
                        'type' => Table::TYPE_TIMESTAMP,
                        'default' => Table::TIMESTAMP_INIT
                    ]
                );
            }

            if ($setup->tableExists('mageplaza_rma_request_reply')) {
                $connection->modifyColumn(
                    $setup->getTable('mageplaza_rma_request_reply'),
                    'created_at',
                    [
                        'type' => Table::TYPE_TIMESTAMP,
                        'default' => Table::TIMESTAMP_INIT
                    ]
                );
            }

            if ($setup->tableExists('mageplaza_rma_rule')) {
                $connection->modifyColumn(
                    $setup->getTable('mageplaza_rma_rule'),
                    'created_at',
                    [
                        'type' => Table::TYPE_TIMESTAMP,
                        'default' => Table::TIMESTAMP_INIT
                    ]
                )->modifyColumn(
                    $setup->getTable('mageplaza_rma_rule'),
                    'updated_at',
                    [
                        'type' => Table::TYPE_TIMESTAMP,
                        'default' => Table::TIMESTAMP_INIT_UPDATE
                    ]
                );
            }

            if ($setup->tableExists('mageplaza_rma_shipping_label')) {
                $connection->modifyColumn(
                    $setup->getTable('mageplaza_rma_shipping_label'),
                    'created_at',
                    [
                        'type' => Table::TYPE_TIMESTAMP,
                        'default' => Table::TIMESTAMP_INIT
                    ]
                )->modifyColumn(
                    $setup->getTable('mageplaza_rma_shipping_label'),
                    'updated_at',
                    [
                        'type' => Table::TYPE_TIMESTAMP,
                        'default' => Table::TIMESTAMP_INIT_UPDATE
                    ]
                );
            }

            if ($setup->tableExists('mageplaza_rma_status')) {
                $connection->modifyColumn(
                    $setup->getTable('mageplaza_rma_status'),
                    'created_at',
                    [
                        'type' => Table::TYPE_TIMESTAMP,
                        'default' => Table::TIMESTAMP_INIT
                    ]
                )->modifyColumn(
                    $setup->getTable('mageplaza_rma_status'),
                    'updated_at',
                    [
                        'type' => Table::TYPE_TIMESTAMP,
                        'default' => Table::TIMESTAMP_INIT_UPDATE
                    ]
                );
            }

            if ($setup->tableExists('mageplaza_rma_template')) {
                $connection->modifyColumn(
                    $setup->getTable('mageplaza_rma_template'),
                    'created_at',
                    [
                        'type' => Table::TYPE_TIMESTAMP,
                        'default' => Table::TIMESTAMP_INIT
                    ]
                )->modifyColumn(
                    $setup->getTable('mageplaza_rma_template'),
                    'updated_at',
                    [
                        'type' => Table::TYPE_TIMESTAMP,
                        'default' => Table::TIMESTAMP_INIT_UPDATE
                    ]
                );
            }
        }

        $setup->endSetup();
    }
}

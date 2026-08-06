<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @version      v1.0.0
 * @since        v1.12.11
 * @created      2025-03-24
 */

namespace WeSupply\Toolbox\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class AddIsEpsiColumn
 *
 * @package WeSupply\Toolbox\Setup\Patch\Schema
 */
class AddEpsiColumns implements SchemaPatchInterface
{
    /**
     * @var string
     */
    private const EPSI_FLAG = 'is_epsi';

    /**
     * @var string
     */
    private const EPSI_AMOUNT = 'epsi_amount';

    /**
     * @var SchemaSetupInterface $schemaSetup
     */
    private SchemaSetupInterface $schemaSetup;

    /**
     * AddIsEpsiColumn constructor.
     *
     * @param SchemaSetupInterface $schemaSetup
     */
    public function __construct(SchemaSetupInterface $schemaSetup)
    {
        $this->schemaSetup = $schemaSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $setup = $this->schemaSetup;
        $this->schemaSetup->startSetup();

        $this->addTableColumns($setup, 'quote');
        $this->addTableColumns($setup, 'sales_order');
        $this->addTableColumns($setup, 'sales_invoice');
        $this->addTableColumns($setup, 'sales_creditmemo');

        $this->schemaSetup->endSetup();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    private function addTableColumns($setup, $tableName)
    {
        $table = $setup->getTable($tableName);

        if (!$setup->getConnection()->tableColumnExists($table, self::EPSI_FLAG)) {
            $setup->getConnection()->addColumn(
                $table,
                self::EPSI_FLAG,
                [
                    'type' => Table::TYPE_BOOLEAN,
                    'nullable' => true,
                    'default' => false,
                    'comment' =>'NSG Package Protection State'
                ]
            );
        }

        if (!$setup->getConnection()->tableColumnExists($table, self::EPSI_AMOUNT)) {
            $setup->getConnection()->addColumn(
                $table,
                self::EPSI_AMOUNT,
                [
                    'type' => Table::TYPE_DECIMAL,
                    'length' => '20,4',
                    'nullable' => true,
                    'default' => 0.0000,
                    'comment' =>'NSG Package Protection Amount'
                ]
            );
        }
    }
}

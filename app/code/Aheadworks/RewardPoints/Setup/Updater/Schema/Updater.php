<?php
namespace Aheadworks\RewardPoints\Setup\Updater\Schema;

use Aheadworks\RewardPoints\Model\ResourceModel\EarnRule as EarnRuleResource;
use Aheadworks\RewardPoints\Model\ResourceModel\StorefrontLabels\Repository as StorefrontLabelsRepository;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class Updater
 * @package Aheadworks\RewardPoints\Setup\Updater\Schema
 */
class Updater
{
    /**
     * Update to 1.4.4 version
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    public function update144(SchemaSetupInterface $setup)
    {
        $this->addMonthlySharePointsDateColumnToSummaryTable($setup);
        return $this;
    }

    /**
     * Adding column monthly share points date to summary table
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    private function addMonthlySharePointsDateColumnToSummaryTable(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $summaryTableName = $setup->getTable('aw_rp_points_summary');
        if (!$connection->tableColumnExists($summaryTableName, 'monthly_share_points_date')) {
            $connection->addColumn(
                $summaryTableName,
                'monthly_share_points_date',
                [
                    'type'     => Table::TYPE_DATE,
                    'nullable' => true,
                    'default' => null,
                    'after' => 'daily_share_points_date',
                    'comment' => 'Monthly Reward Points Date for Share'
                ]
            );
        }

        return $this;
    }

    /**
     * Update to 1.5.0 version
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     * @throws \Zend_Db_Exception
     */
    public function update150(SchemaSetupInterface $setup)
    {
        $this->addRuleTables($setup);
        $this->addRuleIndexTable($setup, EarnRuleResource::PRODUCT_TABLE_NAME);
        $this->addRuleIndexTable($setup, EarnRuleResource::PRODUCT_IDX_TABLE_NAME);
        $this->addCommentToAminPlaceholderColumnToTransactionTable($setup);
        $this->addLabelTable($setup);
        return $this;
    }

    /**
     * Update to 1.7.0 version
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    public function update170(SchemaSetupInterface $setup)
    {
        $this->addDobUpdateDateColumnToSummaryTable($setup);
        return $this;
    }

    /**
     * Add rule tables
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     * @throws \Zend_Db_Exception
     */
    private function addRuleTables(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $earnRuleTableName = $setup->getTable(EarnRuleResource::MAIN_TABLE_NAME);
        $earnRuleTable = $connection
            ->newTable($earnRuleTableName)
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Rule Id'
            )
            ->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Name'
            )
            ->addColumn(
                'description',
                Table::TYPE_TEXT,
                '64k',
                [],
                'Description'
            )
            ->addColumn(
                'priority',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => '0'],
                'Priority'
            )
            ->addColumn(
                'discard_subsequent_rules',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '0'],
                'Discard Subsequent Rules'
            )
            ->addColumn(
                'from_date',
                Table::TYPE_DATE,
                null,
                ['nullable' => true, 'default' => null],
                'From Date'
            )->addColumn(
                'to_date',
                Table::TYPE_DATE,
                null,
                ['nullable' => true, 'default' => null],
                'To Date'
            )
            ->addColumn(
                'status',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '0'],
                'Status'
            )
            ->addColumn(
                'condition',
                Table::TYPE_TEXT,
                '2M',
                [],
                'Condition Serialized'
            )
            ->addColumn(
                'action',
                Table::TYPE_TEXT,
                '2M',
                [],
                'Action Serialized'
            )
            ->addIndex(
                $setup->getIdxName(EarnRuleResource::MAIN_TABLE_NAME, ['name']),
                ['name']
            )
            ->addIndex(
                $setup->getIdxName(EarnRuleResource::MAIN_TABLE_NAME, ['from_date']),
                ['from_date']
            )
            ->addIndex(
                $setup->getIdxName(EarnRuleResource::MAIN_TABLE_NAME, ['to_date']),
                ['to_date']
            )
            ->addIndex(
                $setup->getIdxName(EarnRuleResource::MAIN_TABLE_NAME, ['status']),
                ['status']
            )
            ->addIndex(
                $setup->getIdxName(EarnRuleResource::MAIN_TABLE_NAME, ['priority']),
                ['priority']
            )
            ->setComment('Aheadworks Reward Points Earn Rule Table');
        $setup->getConnection()->createTable($earnRuleTable);

        /**
         * Create table 'aw_rp_earn_rule_website'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable(EarnRuleResource::WEBSITE_TABLE_NAME))
            ->addColumn(
                'rule_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Rule Id'
            )
            ->addColumn(
                'website_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Website Id'
            )
            ->addIndex(
                $setup->getIdxName(EarnRuleResource::WEBSITE_TABLE_NAME, ['website_id']),
                ['website_id']
            )
            ->addForeignKey(
                $setup->getFkName(
                    EarnRuleResource::WEBSITE_TABLE_NAME,
                    'rule_id',
                    EarnRuleResource::MAIN_TABLE_NAME,
                    'id'
                ),
                'rule_id',
                $setup->getTable(EarnRuleResource::MAIN_TABLE_NAME),
                'id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName(
                    EarnRuleResource::WEBSITE_TABLE_NAME,
                    'website_id',
                    'store_website',
                    'website_id'
                ),
                'website_id',
                $setup->getTable('store_website'),
                'website_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Aheadworks Reward Points Earn Rule To Website Relations');
        $setup->getConnection()->createTable($table);

        $customerGroupTable = $setup->getConnection()->describeTable($setup->getTable('customer_group'));
        $customerGroupIdType = $customerGroupTable['customer_group_id']['DATA_TYPE'] == 'int'
            ? Table::TYPE_INTEGER
            : $customerGroupTable['customer_group_id']['DATA_TYPE'];

        /**
         * Create table 'aw_rp_earn_rule_customer_group'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable(EarnRuleResource::CUSTOMER_GROUP_TABLE_NAME))
            ->addColumn(
                'rule_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Rule Id'
            )
            ->addColumn(
                'customer_group_id',
                $customerGroupIdType,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Customer Group Id'
            )
            ->addIndex(
                $setup->getIdxName(EarnRuleResource::CUSTOMER_GROUP_TABLE_NAME, ['customer_group_id']),
                ['customer_group_id']
            )
            ->addForeignKey(
                $setup->getFkName(
                    EarnRuleResource::CUSTOMER_GROUP_TABLE_NAME,
                    'rule_id',
                    EarnRuleResource::MAIN_TABLE_NAME,
                    'id'
                ),
                'rule_id',
                $setup->getTable(EarnRuleResource::MAIN_TABLE_NAME),
                'id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName(
                    EarnRuleResource::CUSTOMER_GROUP_TABLE_NAME,
                    'customer_group_id',
                    'customer_group',
                    'customer_group_id'
                ),
                'customer_group_id',
                $setup->getTable('customer_group'),
                'customer_group_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Aheadworks Reward Points Earn Rule To Customer Groups Relations');
        $setup->getConnection()->createTable($table);

        return $this;
    }

    /**
     * Add rule index table
     *
     * @param SchemaSetupInterface $setup
     * @param string $tableName
     * @return $this
     * @throws \Zend_Db_Exception
     */
    private function addRuleIndexTable(SchemaSetupInterface $setup, $tableName)
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable($tableName))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'rule_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Rule Id'
            )
            ->addColumn(
                'from_date',
                Table::TYPE_DATE,
                null,
                [],
                'Rule is Active From'
            )
            ->addColumn(
                'to_date',
                Table::TYPE_DATE,
                null,
                [],
                'Rule is Active To'
            )
            ->addColumn(
                'customer_group_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Customer Group Id'
            )
            ->addColumn(
                'website_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Website Id'
            )
            ->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Product Id'
            )
            ->addColumn(
                'priority',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Priority'
            )
            ->addIndex(
                $setup->getIdxName(
                    $tableName,
                    ['rule_id', 'from_date', 'to_date', 'customer_group_id', 'website_id', 'product_id', 'priority'],
                    true
                ),
                ['rule_id', 'from_date', 'to_date', 'customer_group_id', 'website_id', 'product_id', 'priority'],
                ['type' => 'unique']
            )
            ->addIndex(
                $setup->getIdxName($tableName, ['customer_group_id']),
                ['customer_group_id']
            )
            ->addIndex(
                $setup->getIdxName($tableName, ['website_id']),
                ['website_id']
            )
            ->addIndex(
                $setup->getIdxName($tableName, ['from_date']),
                ['from_date']
            )
            ->addIndex(
                $setup->getIdxName($tableName, ['to_date']),
                ['to_date']
            )
            ->addIndex(
                $setup->getIdxName($tableName, ['product_id']),
                ['product_id']
            )
            ->setComment('AW Reward Points Earn Rule Product');
        $setup->getConnection()->createTable($table);

        return $this;
    }

    /**
     * Adding comment to admin placeholder column to transaction table
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    private function addCommentToAminPlaceholderColumnToTransactionTable(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $summaryTableName = $setup->getTable('aw_rp_transaction');
        if (!$connection->tableColumnExists($summaryTableName, 'comment_to_admin_placeholder')) {
            $connection->addColumn(
                $summaryTableName,
                'comment_to_admin_placeholder',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'after' => 'comment_to_admin',
                    'comment' => 'Admin Comments Placeholder'
                ]
            );
        }

        return $this;
    }

    /**
     * Add table for storefront description of entities
     *
     * @param SchemaSetupInterface $installer
     * @throws \Zend_Db_Exception
     * @return $this
     */
    private function addLabelTable(SchemaSetupInterface $installer)
    {
        $labelTable = $installer->getConnection()
            ->newTable($installer->getTable(StorefrontLabelsRepository::MAIN_TABLE_NAME))
            ->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Store Id'
            )
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Entity Id'
            )
            ->addColumn(
                'entity_type',
                Table::TYPE_TEXT,
                100,
                [
                    'nullable' => false,
                    'primary' => true
                ],
                'Entity Type'
            )
            ->addColumn(
                'product_promo_text',
                Table::TYPE_TEXT,
                '64k',
                [],
                'Products Promo Text'
            )
            ->addColumn(
                'category_promo_text',
                Table::TYPE_TEXT,
                '64k',
                [],
                'Category Products Promo Text'
            )
            ->addIndex(
                $installer->getIdxName(StorefrontLabelsRepository::MAIN_TABLE_NAME, ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    StorefrontLabelsRepository::MAIN_TABLE_NAME,
                    'store_id',
                    'store',
                    'store_id'
                ),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )->setComment('Aheadworks Reward Points Entity Label To Store Relation Table');
        $installer->getConnection()->createTable($labelTable);
        return $this;
    }

    /**
     * Adding column dob update date to summary table
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    private function addDobUpdateDateColumnToSummaryTable(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $summaryTableName = $setup->getTable('aw_rp_points_summary');
        if (!$connection->tableColumnExists($summaryTableName, 'dob_update_date')) {
            $connection->addColumn(
                $summaryTableName,
                'dob_update_date',
                [
                    'type' => Table::TYPE_DATE,
                    'nullable' => true,
                    'default' => null,
                    'after' => 'monthly_share_points_date',
                    'comment' => 'Dob Update date'
                ]
            );
        }

        return $this;
    }
}

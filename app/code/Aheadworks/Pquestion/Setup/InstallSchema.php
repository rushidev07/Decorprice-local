<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var \Aheadworks\Pquestion\Helper\Bookmark
     */
    protected $definedBookmarkHelper;

    /**
     * @var \Magento\User\Model\ResourceModel\User\Collection
     */
    protected $userCollection;

    /**
     * @param \Aheadworks\Pquestion\Helper\Bookmark $bookmarkHelper
     * @param \Magento\User\Model\ResourceModel\User\Collection $userCollection
     */
    public function __construct(
        \Aheadworks\Pquestion\Helper\Bookmark $bookmarkHelper,
        \Magento\User\Model\ResourceModel\User\Collection $userCollection
    ) {
        $this->definedBookmarkHelper = $bookmarkHelper;
        $this->userCollection = $userCollection;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'question'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('aw_pq_question')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Question ID'
        )->addColumn(
            'author_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Author Name'
        )->addColumn(
            'author_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Author Email'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => 0],
            'Customer ID'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            [],
            'Created At'
        )->addColumn(
            'content',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Content'
        )->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Product ID'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Store ID'
        )->addColumn(
            'show_in_store_ids',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['default' => 0],
            'Show in store ids '
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Status'
        )->addColumn(
            'visibility',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Visibility'
        )->addColumn(
            'sharing_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Sharing Type'
        )->addColumn(
            'sharing_value',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Sharing Value'
        )->addColumn(
            'helpfulness',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            255,
            ['nullable' => false, 'default' => 0],
            'Helpfulness'
        )->addColumn(
            'is_admin',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            255,
            ['nullable' => false, 'default' => 0],
            'Is Admin'
        )->setComment(
            'Question Table'
        );

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'answer'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('aw_pq_answer')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Answer ID'
        )->addColumn(
            'question_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Question ID'
        )->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Product ID'
        )->addColumn(
            'author_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Author Name'
        )->addColumn(
            'author_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Author Email'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => 0],
            'Customer ID'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            5,
            ['nullable' => false],
            'Status'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            [],
            'Created At'
        )->addColumn(
            'content',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Content'
        )->addColumn(
            'helpfulness',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            255,
            ['nullable' => false, 'default' => 0],
            'Helpfulness'
        )->addColumn(
            'is_admin',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            255,
            ['nullable' => false, 'default' => 0],
            'Is Admin'
        )->addForeignKey(
            $installer->getFkName('aw_pq_answer', 'question_id', 'aw_pq_question', 'entity_id'),
            'question_id',
            $installer->getTable('aw_pq_question'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Answer Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'summary_question'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('aw_pq_summary_question')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Summary Question ID'
        )->addColumn(
            'question_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Question ID'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => 0],
            'Customer ID'
        )->addColumn(
            'visitor_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Visitor  ID'
        )->addColumn(
            'helpful',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Helpful'
        )->addForeignKey(
            $installer->getFkName('aw_pq_summary_question', 'question_id', 'aw_pq_question', 'entity_id'),
            'question_id',
            $installer->getTable('aw_pq_question'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Question summary Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'summary_answer'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('aw_pq_summary_answer')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Summary Question ID'
        )->addColumn(
            'answer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Answer ID'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => 0],
            'Customer ID'
        )->addColumn(
            'visitor_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Visitor  ID'
        )->addColumn(
            'helpful',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Helpful'
        )->addForeignKey(
            $installer->getFkName('aw_pq_summary_answer', 'answer_id', 'aw_pq_answer', 'entity_id'),
            'answer_id',
            $installer->getTable('aw_pq_answer'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Answer summary Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'notification_queue'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('aw_pq_notification_queue')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Queue ID'
        )->addColumn(
            'notification_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Notification Type'
        )->addColumn(
            'recipient_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Recipient Email'
        )->addColumn(
            'recipient_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Recipient Name'
        )->addColumn(
            'sender_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Sender Email'
        )->addColumn(
            'sender_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Sender Name'
        )->addColumn(
            'subject',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Subject'
        )->addColumn(
            'body',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Body'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            [],
            'Created At'
        )->addColumn(
            'sent_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            [],
            'Sent At'
        )->setComment(
            'Notification Queue Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'notification_queue'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('aw_pq_notification_subscriber')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Subscriber ID'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Customer ID'
        )->addColumn(
            'customer_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Customer Email'
        )->addColumn(
            'website_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Website ID'
        )->addColumn(
            'notification_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Notification Type'
        )->addColumn(
            'value',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => 1],
            'Website ID'
        )->addIndex(
            $installer->getIdxName(
                'aw_pq_notification_subscriber',
                ['customer_email', 'website_id', 'notification_type'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['customer_email', 'website_id', 'notification_type'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->setComment(
            'Notification Subscriber Table'
        );
        $installer->getConnection()->createTable($table);

        $this->_installBookmarks();

        $installer->endSetup();
    }

    /**
     * @return void
     */
    protected function _installBookmarks()
    {
        foreach ($this->userCollection as $user) {
            $this->definedBookmarkHelper->proceedAll($user);
        }
    }
}

<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.3 or later
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
/**
 * @codingStandardsIgnoreFile
 */
namespace Magetrend\Eop\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use \Magento\Framework\DB\Ddl\Table;

/**
 * Installation script class
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Table mt_eop_popup columns source
     *
     * @var array
     */
    private $__popupColumns = [
        'entity_id'                 => ['type' => Table::TYPE_INTEGER,    'length'=> 10, 'primary' => 1],
        'is_active'                 => ['type' => Table::TYPE_SMALLINT,   'length'=> 1],
        'name'                      => ['type' => Table::TYPE_TEXT,       'length'=> 255],
        'theme'                     => ['type' => Table::TYPE_TEXT,       'length'=> 50],
        'content_type'              => ['type' => Table::TYPE_TEXT,       'length'=> 50],
        'mobile_auto_position'      => ['type' => Table::TYPE_SMALLINT,   'length'=> 1],
        'text_1'                    => ['type' => Table::TYPE_TEXT,       'length'=> null],
        'text_2'                    => ['type' => Table::TYPE_TEXT,       'length'=> null],
        'text_3'                    => ['type' => Table::TYPE_TEXT,       'length'=> null],
        'text_4'                    => ['type' => Table::TYPE_TEXT,       'length'=> null],
        'text_5'                    => ['type' => Table::TYPE_TEXT,       'length'=> null],
        'text_6'                    => ['type' => Table::TYPE_TEXT,       'length'=> null],
        'text_7'                    => ['type' => Table::TYPE_TEXT,       'length'=> null],
        'text_8'                    => ['type' => Table::TYPE_TEXT,       'length'=> null],
        'color_1'                   => ['type' => Table::TYPE_TEXT,       'length'=> 7],
        'color_2'                   => ['type' => Table::TYPE_TEXT,       'length'=> 7],
        'color_3'                   => ['type' => Table::TYPE_TEXT,       'length'=> 7],
        'icon'                      => ['type' => Table::TYPE_TEXT,       'length'=> 50],
        'link_facebook'             => ['type' => Table::TYPE_TEXT,       'length'=> 255],
        'link_twitter'              => ['type' => Table::TYPE_TEXT,       'length'=> 255],
        'link_pinterest'            => ['type' => Table::TYPE_TEXT,       'length'=> 255],
        'link_gplus'                => ['type' => Table::TYPE_TEXT,       'length'=> 255],
        'link_instagram'            => ['type' => Table::TYPE_TEXT,       'length'=> 255],
        'link_tumblr'               => ['type' => Table::TYPE_TEXT,       'length'=> 255],
        'link_linkedin'             => ['type' => Table::TYPE_TEXT,       'length'=> 255],
        'link_youtube'              => ['type' => Table::TYPE_TEXT,       'length'=> 255],
        'attachment_type'           => ['type' => Table::TYPE_TEXT,       'length'=> 255],
        'attachment'                => ['type' => Table::TYPE_TEXT,       'length'=> 255],
        'show_in_popup'             => ['type' => Table::TYPE_SMALLINT,   'length'=> 1],
        'coupon_is_active'          => ['type' => Table::TYPE_SMALLINT,   'length'=> 1],
        'coupon_rule_id'            => ['type' => Table::TYPE_INTEGER,    'length'=> 10],
        'coupon_length'             => ['type' => Table::TYPE_INTEGER,    'length'=> 3],
        'coupon_format'             => ['type' => Table::TYPE_TEXT,       'length'=> 50],
        'coupon_prefix'             => ['type' => Table::TYPE_TEXT,       'length'=> 50],
        'coupon_suffix'             => ['type' => Table::TYPE_TEXT,       'length'=> 50],
        'coupon_dash'               => ['type' => Table::TYPE_SMALLINT,   'length'=> 2],
        'static_block_id'           => ['type' => Table::TYPE_INTEGER,    'length'=> 10],
        'button_is_active'          => ['type' => Table::TYPE_SMALLINT,   'length'=> 1],
        'button_selector'           => ['type' => Table::TYPE_TEXT,       'length'=> 255],
        'button_text'               => ['type' => Table::TYPE_TEXT,       'length'=> 255],
        'button_only'               => ['type' => Table::TYPE_SMALLINT,   'length'=> 1],
        'button_position'           => ['type' => Table::TYPE_TEXT,       'length'=> 50],
        'button_top'                => ['type' => Table::TYPE_TEXT,       'length'=> 10],
        'email_template'            => ['type' => Table::TYPE_TEXT,       'length'=> 255],
        'send_to'                   => ['type' => Table::TYPE_TEXT,       'length'=> 255],
        'sender_name'               => ['type' => Table::TYPE_TEXT,       'length'=> 255],
        'subject'                   => ['type' => Table::TYPE_TEXT,       'length'=> 255],
    ];

    /**
     * Table mt_eop_field columns source
     *
     * @var array
     */
    private $__fieldColumns =  [
        'entity_id'                 => ['type' => Table::TYPE_INTEGER,    'length'=> 10, 'primary' => 1],
        'popup_id'                  => [
            'type' => Table::TYPE_INTEGER,
            'length'=> 10,
            'index' => true,
            'foreign_key' => ['mt_eop_popup', 'entity_id']
        ],
        'type'                      => ['type' => Table::TYPE_TEXT,       'length'=> 20],
        'name'                      => ['type' => Table::TYPE_TEXT,       'length'=> 255],
        'label'                     => ['type' => Table::TYPE_TEXT,       'length'=> 255],
        'is_required'               => ['type' => Table::TYPE_SMALLINT,   'length'=> 1],
        'position'                  => ['type' => Table::TYPE_SMALLINT,   'length'=> 6],
        'after_email_field'         => ['type' => Table::TYPE_SMALLINT,   'length'=> 1],
    ];

    /**
     * Table mt_eop_field_option columns source
     *
     * @var array
     */
    private $__fieldOptionColumns =  [
        'entity_id'                 => ['type' => Table::TYPE_INTEGER,    'length'=> 10, 'primary' => 1],
        'field_id'                  => [
            'type' => Table::TYPE_INTEGER,
            'length'=> 10,
            'index' => true,
            'foreign_key' => ['mt_eop_field', 'entity_id']
        ],
        'value'                     => ['type' => Table::TYPE_TEXT,       'length'=> 20],
        'label'                     => ['type' => Table::TYPE_TEXT,       'length'=> 255],
        'position'                  => ['type' => Table::TYPE_SMALLINT,   'length'=> 6],
    ];

    /**
     * Table mt_eop_campaign columns source
     *
     * @var array
     */
    private $__campaignColumns = [
        'entity_id'                 => ['type' => Table::TYPE_INTEGER,    'length'=> 10, 'primary' => 1],
        'popup_id'                  => [
            'type' => Table::TYPE_INTEGER,
            'length'=> 10,
            'index' => true,
            'foreign_key' => ['mt_eop_popup', 'entity_id']
        ],
        'is_active'                 => ['type' => Table::TYPE_SMALLINT,   'length'=> 1],
        'name'                      => ['type' => Table::TYPE_TEXT,       'length'=> 255],
        'popup_delay'               => ['type' => Table::TYPE_INTEGER,    'length'=> 3],
        'layer_close'               => ['type' => Table::TYPE_SMALLINT,   'length'=> 1],
        'show_in_last_tab'          => ['type' => Table::TYPE_SMALLINT,   'length'=> 1],
        'show_event'                => ['type' => Table::TYPE_TEXT,       'length'=> 20],
        'show_device'               => ['type' => Table::TYPE_TEXT,       'length'=> 20],
        'delay_time'                => ['type' => Table::TYPE_INTEGER,    'length'=> 5],
        'cookie_lifetime'           => ['type' => Table::TYPE_INTEGER,    'length'=> 4],
        'params'                    => ['type' => Table::TYPE_TEXT,       'length'=> 255],
        'start_date'                => ['type' => Table::TYPE_DATETIME,   'length'=> null],
        'end_date'                  => ['type' => Table::TYPE_DATETIME,   'length'=> null],
    ];

    /**
     * Table mt_eop_campaign_page columns source
     *
     * @var array
     */
    private $__campaignPageColumns = [
        'entity_id'                 => ['type' => Table::TYPE_INTEGER,    'length'=> 10, 'primary' => 1],
        'campaign_id'               => [
            'type' => Table::TYPE_INTEGER,
            'length'=> 10,
            'index' => true,
            'foreign_key' => ['mt_eop_campaign', 'entity_id']
        ],
        'page_id'                   => ['type' => Table::TYPE_TEXT,       'length'=> 50],
    ];

    /**
     * Table mt_eop_campaign_store columns source
     *
     * @var array
     */
    private $__campaignStoreColumns = [
        'entity_id'                 => ['type' => Table::TYPE_INTEGER,    'length'=> 10, 'primary' => 1],
        'campaign_id' => [
            'type' => Table::TYPE_INTEGER,
            'length'=> 10,
            'index' => true,
            'foreign_key' => ['mt_eop_campaign', 'entity_id']
        ],
        'store_id' => [
            'type' => Table::TYPE_SMALLINT,
            'length' => null,
            'index' => true,
            'options' => ['unsigned' => true, 'nullable' => true],
            'foreign_key' => ['store', 'store_id']
        ],
    ];

    /**
     * Execute installation script
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $this->createTable(
            $installer,
            $installer->getTable('mt_eop_popup'),
            $this->__popupColumns
        );

        $this->createTable(
            $installer,
            $installer->getTable('mt_eop_field'),
            $this->__fieldColumns
        );

        $this->createTable(
            $installer,
            $installer->getTable('mt_eop_field_option'),
            $this->__fieldOptionColumns
        );

        $this->createTable(
            $installer,
            $installer->getTable('mt_eop_campaign'),
            $this->__campaignColumns
        );

        $this->createTable(
            $installer,
            $installer->getTable('mt_eop_campaign_page'),
            $this->__campaignPageColumns
        );

        $this->createTable(
            $installer,
            $installer->getTable('mt_eop_campaign_store'),
            $this->__campaignStoreColumns
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('newsletter_subscriber'),
            \Magetrend\Eop\Model\Popup::DISCOUNT_CODE_FIELD,
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'comment' => 'Exit offer coupon code'
            ]
        );

        $installer->endSetup();
    }

    /**
     * Create database table
     *
     * @param $installer
     * @param $tableName
     * @param $columns
     */
    public function createTable($installer, $tableName, $columns)
    {
        $db = $installer->getConnection();
        $table = $db->newTable($tableName);

        foreach ($columns as $name => $info) {
            $options = [];
            if (isset($info['options'])) {
                $options = $info['options'];
            }

            if (isset($info['primary']) && $info['primary'] == 1) {
                $options = ['identity' => true, 'nullable' => false, 'primary' => true];
            }

            $table->addColumn(
                $name,
                $info['type'],
                $info['length'],
                $options,
                $name
            );

            if (isset($info['index'])) {
                $table->addIndex(
                    $installer->getIdxName($tableName, [$name]),
                    [$name]
                );
            }

            if (isset($info['foreign_key'])) {
                $table->addForeignKey(
                    $installer->getFkName($tableName, $name, $info['foreign_key'][0], $info['foreign_key'][1]),
                    $name,
                    $installer->getTable($info['foreign_key'][0]),
                    $info['foreign_key'][1],
                    Table::ACTION_NO_ACTION
                );
            }
        }

        $db->createTable($table);
    }
}


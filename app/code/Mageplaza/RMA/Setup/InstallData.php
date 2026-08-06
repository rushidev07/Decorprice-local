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

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageplaza\RMA\Model\Config\Source\RMAStatus\Action;

/**
 * Class InstallData
 * @package Mageplaza\RMA\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var DateTime
     */
    protected $_dateTime;

    /**
     * InstallData constructor.
     *
     * @param DateTime $dateTime
     */
    public function __construct(DateTime $dateTime)
    {
        $this->_dateTime = $dateTime;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /** Add default RMA status */
        $data = [
            [
                'name' => __('Pending'),
                'label' => __('Pending'),
                'comment' => '',
                'enable_comment' => 0,
                'is_active' => 1,
                'description' => '',
                'allow_action' => 0,
                'updated_at' => $this->_dateTime->date(),
                'created_at' => $this->_dateTime->date()
            ],
            [
                'name' => __('Processing'),
                'label' => __('Processing'),
                'comment' => '',
                'enable_comment' => 0,
                'is_active' => 1,
                'description' => '',
                'allow_action' => Action::SHIPPING_LABEL,
                'updated_at' => $this->_dateTime->date(),
                'created_at' => $this->_dateTime->date()
            ],
            [
                'name' => __('Rejected'),
                'label' => __('Rejected'),
                'comment' => '',
                'enable_comment' => 0,
                'is_active' => 1,
                'description' => '',
                'allow_action' => 0,
                'updated_at' => $this->_dateTime->date(),
                'created_at' => $this->_dateTime->date()
            ],
            [
                'name' => __('Completed'),
                'label' => __('Completed'),
                'comment' => '',
                'enable_comment' => 0,
                'is_active' => 1,
                'description' => '',
                'allow_action' => Action::SHIPPING_LABEL . ',' . Action::CREDIT_MEMO . ',' . Action::REORDER,
                'updated_at' => $this->_dateTime->date(),
                'created_at' => $this->_dateTime->date()
            ],
            [
                'name' => __('Canceled'),
                'label' => __('Canceled'),
                'comment' => '',
                'enable_comment' => 0,
                'is_active' => 1,
                'description' => '',
                'allow_action' => 0,
                'updated_at' => $this->_dateTime->date(),
                'created_at' => $this->_dateTime->date()
            ]
        ];
        $setup->getConnection()->insertMultiple($setup->getTable('mageplaza_rma_status'), $data);

        $installer->endSetup();
    }
}

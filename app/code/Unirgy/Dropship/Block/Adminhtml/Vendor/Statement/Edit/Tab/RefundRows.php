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

namespace Unirgy\Dropship\Block\Adminhtml\Vendor\Statement\Edit\Tab;

use \Magento\Backend\Block\Template\Context;
use \Magento\Backend\Block\Widget\Grid;
use \Magento\Backend\Helper\Data as HelperData;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\Registry;
use \Unirgy\Dropship\Model\Vendor\Statement;
use \Unirgy\Dropship\Helper\Data as DropshipHelperData;

class RefundRows extends \Magento\Backend\Block\Widget\Grid\Extended implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var DropshipHelperData
     */
    protected $_hlp;

    public function __construct(
        Registry $registry,
        DropshipHelperData $helperData,
        Context $context,
        HelperData $backendHelper,
        array $data = []
    ) {
    
        $this->_hlp = $helperData;
        $this->_registry = $registry;

        parent::__construct($context, $backendHelper, $data);
        $this->setId('statement_refund_rows');
        $this->setDefaultSort('row_id');
        $this->setUseAjax(true);
    }

    public function getStatement()
    {
        $statement = $this->_registry->registry('statement_data');
        if (!$statement) {
            /** @var \Unirgy\Dropship\Model\Vendor\Statement $statement */
            $statement = $this->_hlp->createObj('\Unirgy\Dropship\Model\Vendor\Statement')->load($this->getStatementId());
            $this->_registry->register('statement_data', $statement);
        }
        return $statement;
    }

    protected function _prepareCollection()
    {
        /** @var \Unirgy\Dropship\Model\ResourceModel\Vendor\Statement\RefundRow\Collection $collection */
        $collection = $this->_hlp->createObj('\Unirgy\Dropship\Model\Vendor\Statement\RefundRow')->getCollection()
            ->addFieldToFilter('statement_id', $this->getStatement()->getId());
        ;

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('row_id', [
            'header'    => __('ID'),
            'sortable'  => true,
            'width'     => '60',
            'index'     => 'row_id'
        ]);
        $this->addColumn('order_increment_id', [
            'header'    => __('Order ID'),
            'index'     => 'order_increment_id'
        ]);
        $this->addColumn('order_created_at', [
            'header'    => __('Order Date'),
            'index'     => 'order_created_at'
        ]);
        $this->addColumn('refund_increment_id', [
            'header'    => __('Refund ID'),
            'index'     => 'refund_increment_id'
        ]);
        $this->addColumn('refund_created_at', [
            'header'    => __('Refund Date'),
            'index'     => 'refund_created_at'
        ]);
        $this->addColumn('po_increment_id', [
            'header'    => __('PO ID'),
            'index'     => 'po_increment_id'
        ]);
        $this->addColumn('po_created_at', [
            'header'    => __('PO Date'),
            'index'     => 'po_created_at'
        ]);
        $this->addColumn('subtotal', [
            'header'    => __('Subtotal'),
            'index' => 'subtotal',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => $this->_scopeConfig->getValue('currency/options/base'),
        ]);
        $this->addColumn('com_amount', [
            'header'    => __('Com Amount'),
            'index' => 'com_amount',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => $this->_scopeConfig->getValue('currency/options/base'),
        ]);
        if ($this->getStatement()->getVendor()->getStatementTaxInPayout() != 'exclude_hide') {
            $this->addColumn('tax', [
                'header'    => __('Tax'),
                'index' => 'tax',
                'type'  => 'price',
                'currency' => 'base_currency_code',
                'currency_code' => $this->_scopeConfig->getValue('currency/options/base'),
            ]);
        }
        if ($this->getStatement()->getVendor()->getStatementShippingInPayout() != 'exclude_hide') {
            $this->addColumn('shipping', [
                'header'    => __('Shipping'),
                'index' => 'shipping',
                'type'  => 'price',
                'currency' => 'base_currency_code',
                'currency_code' => $this->_scopeConfig->getValue('currency/options/base'),
            ]);
        }
        if ($this->getStatement()->getVendor()->getStatementDiscountInPayout() != 'exclude_hide') {
            $this->addColumn('discount', [
                'header'    => __('Discount'),
                'index' => 'discount',
                'type'  => 'price',
                'currency' => 'base_currency_code',
                'currency_code' => $this->_scopeConfig->getValue('currency/options/base'),
            ]);
        }
        $this->addColumn('total_refund', [
            'header'    => __('Total Refund'),
            'index' => 'total_refund',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => $this->_scopeConfig->getValue('currency/options/base'),
        ]);
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/refundRowGrid', ['_current'=>true]);
    }

    public function getTabLabel()
    {
        return __('Refunds');
    }
    public function getTabTitle()
    {
        return __('Refunds');
    }
    public function canShowTab()
    {
        return true;
    }
    public function isHidden()
    {
        return false;
    }
}

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

namespace Unirgy\Dropship\Block\Adminhtml\Report;

use \Magento\Backend\Block\Template\Context;
use \Magento\Backend\Block\Widget\Grid as WidgetGrid;
use \Magento\Backend\Helper\Data as HelperData;
use \Magento\Eav\Model\Config;
use \Magento\Sales\Model\Order\Config as OrderConfig;
use \Unirgy\Dropship\Helper\Data as DropshipHelperData;
use \Unirgy\Dropship\Model\Source;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var Resource
     */
    protected $_rHlp;

    /**
     * @var DropshipHelperData
     */
    protected $_hlp;

    /**
     * @var Config
     */
    protected $_eavConfig;

    /**
     * @var OrderConfig
     */
    protected $_orderConfig;

    public function __construct(
        \Unirgy\Dropship\Model\ResourceModel\Helper $resourceHelper,
        DropshipHelperData $helperData,
        OrderConfig $orderConfig,
        Config $eavConfig,
        Context $context,
        HelperData $backendHelper,
        array $data = []
    ) {

        $this->_rHlp = $resourceHelper;
        $this->_hlp = $helperData;
        $this->_eavConfig = $eavConfig;
        $this->_orderConfig = $orderConfig;

        parent::__construct($context, $backendHelper, $data);
        $this->setId('reportGrid');
        $this->setDefaultSort('order_created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    public function t($table)
    {
        return $this->_rHlp->getTableName($table);
    }

    protected $_couponCodeColumn;

    protected function _getFlatExpressionColumn($key, $bypass = true)
    {
        $result = $bypass ? $key : null;
        switch ($key) {
            case 'tracking_price':
                $result = new \Zend_Db_Expr("(select sum(IFNULL(st.final_price,0)) from {$this->t('sales_shipment_track')} st where parent_id=main_table.entity_id)");
                break;
            case 'tracking_ids':
                $result = new \Zend_Db_Expr("(select group_concat(concat(st.".$this->_hlp->trackNumberField().", ' (', IFNULL(round(st.final_price,2),'N/A'), ')') separator '\n') from {$this->t('sales_shipment_track')} st where parent_id=main_table.entity_id)");
                break;
            case 'base_tax_amount':
                $result = new \Zend_Db_Expr("(select sum(oi.base_tax_amount) from {$this->t('sales_order_item')} oi inner join {$this->t('sales_shipment_item')} si where si.order_item_id=oi.item_id and si.parent_id=main_table.entity_id and oi.order_id=main_table.order_id)");
                break;
            case 'coupon_codes':
                if ($this->_hlp->isModuleActive('Unirgy_Giftcert')) {
                    $result = new \Zend_Db_Expr("concat(
						IF(o.coupon_code is not null and o.coupon_code!='', concat('Coupon: ',o.coupon_code), ''),
						IF(o.giftcert_code is not null and o.giftcert_code!='',
							CONCAT(
								IF(o.coupon_code is not null and o.coupon_code!='', '\n', ''),
								concat('Giftcert: ',o.giftcert_code)
							),
							'')
					)");
                } else {
                    $result = new \Zend_Db_Expr("
						IF(o.coupon_code is not null and o.coupon_code!='', concat('Coupon: ',o.coupon_code), '')
					");
                }
                break;
        }
        return $result;
    }

    protected function _prepareCollection()
    {
        $res = $this->_rHlp;

        $collection = $this->_hlp->createObj('\Magento\Sales\Model\ResourceModel\Order\Shipment\Collection');
        $collection->getSelect()
            ->join(['grid'=>$res->getTableName('sales_shipment_grid')], 'grid.entity_id=main_table.entity_id', ['order_increment_id','order_created_at','increment_id','created_at','udropship_status','base_shipping_amount','total_qty'])
            ->join(['o'=>$res->getTableName('sales_order')], 'o.entity_id=main_table.order_id', ['base_grand_total', 'order_status'=>'o.status'])
            ->join(['a'=>$res->getTableName('sales_order_address')], 'a.parent_id=o.entity_id and a.address_type="shipping"', ['region_id'])
            ->columns(['udropship_vendor', 'udropship_available_at', 'udropship_method', 'udropship_method_description', 'udropship_status', 'base_shipping_amount', 'base_subtotal'=>'base_total_value', 'total_cost'], 'main_table')
            ->columns([
                'tracking_price'=>$this->_getFlatExpressionColumn('tracking_price'),
                'tracking_ids'=>$this->_getFlatExpressionColumn('tracking_ids'),
                //'subtotal'=>$subtotal,
                'base_tax_amount'=>$this->_getFlatExpressionColumn('base_tax_amount'),
                'coupon_codes' => $this->_getFlatExpressionColumn('coupon_codes')
            ]);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $poStr = $this->_hlp->isUdpoActive() ? 'Shipment' : 'PO';

        $hlp = $this->_hlp;

        $this->addColumn('order_increment_id', [
            'header'    => __('Order #'),
            'index'     => 'order_increment_id',
            'filter_index' => 'grid.order_increment_id'
        ]);

        $this->addColumn('order_created_at', [
            'header'    => __('Order Date'),
            'index'     => 'order_created_at',
            'filter_index' => 'grid.order_created_at',
            'type'      => 'datetime',
        ]);

        $this->addColumn('order_status', [
            'header'    => __('Order Status'),
            'index'     => 'order_status',
            'filter_index' => 'o.status',
            'type' => 'options',
            'options' => $this->_orderConfig->getStatuses(),
        ]);

        $this->addColumn('base_grand_total', [
            'header' => __('Order Total'),
            'index' => 'base_grand_total',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => $this->_scopeConfig->getValue('currency/options/base'),
        ]);

        $this->addColumn('increment_id', [
            'header'    => __("$poStr #"),
            'index'     => 'increment_id',
            'filter_index' => 'grid.increment_id',
            'type'      => 'text',
        ]);

        $this->addColumn('created_at', [
            'header'    => __("$poStr Date"),
            'index'     => 'created_at',
            'filter_index' => 'grid.created_at',
            'type'      => 'datetime',
        ]);

        $this->addColumn('udropship_status', [
            'header' => __("$poStr Status"),
            'index' => 'udropship_status',
            'filter_index' => 'grid.udropship_status',
            'type' => 'options',
            'options' => $this->_hlp->src()->setPath('shipment_statuses')->toOptionHash(),
        ]);

        $this->addColumn('base_subtotal', [
            'header' => __("$poStr Subtotal"),
            'index' => 'base_subtotal',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => $this->_scopeConfig->getValue('currency/options/base'),
        ]);

        $this->addColumn('total_cost', [
            'header' => __("$poStr Total Cost"),
            'index' => 'total_cost',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => $this->_scopeConfig->getValue('currency/options/base'),
        ]);

        $this->addColumn('base_tax_amount', [
            'header' => __("$poStr Tax Amount"),
            'index' => 'base_tax_amount',
            'filter_index' => $this->_getFlatExpressionColumn('base_tax_amount'),
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => $this->_scopeConfig->getValue('currency/options/base'),
        ]);

        $this->addColumn('base_shipping_amount', [
            'header' => __("$poStr Shipping Price"),
            'index' => 'base_shipping_amount',
            'filter_index' => 'grid.base_shipping_amount',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => $this->_scopeConfig->getValue('currency/options/base'),
        ]);

        $this->addColumn('total_qty', [
            'header'    => __("$poStr Total Qty"),
            'index'     => 'total_qty',
            'filter_index' => 'grid.total_qty',
            'type'      => 'number',
        ]);

        $this->addColumn('udropship_vendor', [
            'header' => __('Vendor'),
            'index' => 'udropship_vendor',
            'filter_index' => 'main_table.udropship_vendor',
            'type' => 'options',
            'options' => $this->_hlp->src()->setPath('vendors')->toOptionHash(),
            'filter' => '\Unirgy\Dropship\Block\Vendor\GridColumnFilter'
        ]);

        $this->addColumn('tracking_ids', [
            'header' => __('Tracking #'),
            'index' => 'tracking_ids',
            'filter_index' => $this->_getFlatExpressionColumn('tracking_ids'),
        ]);

        $this->addColumn('tracking_price', [
            'header' => __('Tracking Total'),
            'index' => 'tracking_price',
            'filter_index' => $this->_getFlatExpressionColumn('tracking_price'),
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => $this->_scopeConfig->getValue('currency/options/base'),
        ]);

        $this->addColumn('region_id', [
            'header' => __('Tax State'),
            'index' => 'region_id',
            'type' => 'options',
            'options' => $this->_hlp->src()->getTaxRegions(),
            'filter'    => false,
            'sortable'  => false,
        ]);

        $this->addColumn('coupon_codes', [
            'header' => __('Order coupon codes'),
            'index' => 'coupon_codes',
            'filter_index' => $this->_getFlatExpressionColumn('coupon_codes'),
            'type' => 'text',
            'nl2br' => true,
        ]);

        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportXml', __('XML'));
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current'=>true]);
    }
}

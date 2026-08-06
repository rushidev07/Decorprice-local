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
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\DropshipPo\Block\Adminhtml\ReportItem;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid as WidgetGrid;
use Magento\Backend\Helper\Data as HelperData;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order\Config;
use Magento\Store\Model\ScopeInterface;
use Unirgy\DropshipPo\Model\ResourceModel\Po\Item\Collection;
use Unirgy\Dropship\Helper\Data as DropshipHelperData;
use Unirgy\Dropship\Model\Source;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var Collection
     */
    protected $_itemCollection;

    /**
     * @var DropshipHelperData
     */
    protected $_hlp;

    /**
     * @var Config
     */
    protected $_orderConfig;

    /**
     * @var Source
     */
    protected $_modelSource;

    protected $_useBaseCostColumn = false;
    public function __construct(Context $context,
        HelperData $backendHelper,
        Collection $itemCollection,
        DropshipHelperData $helperData,
        Config $orderConfig,
        Source $modelSource,
        array $data = [])
    {
        $this->_itemCollection = $itemCollection;
        $this->_hlp = $helperData;
        $this->_orderConfig = $orderConfig;
        $this->_modelSource = $modelSource;

        parent::__construct($context, $backendHelper, $data);
        $this->setId('reportItemGrid');
        $this->setDefaultSort('order_created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $res  = $this->_hlp->rHlp();
        $conn = $res->getConnection();
        $this->_useBaseCostColumn = $conn->tableColumnExists($res->getTableName('sales_order_item'), 'base_cost');
    }

    public function t($table)
    {
        return $this->_hlp->rHlp()->getTableName($table);
    }

    protected function _getFlatExpressionColumn($key, $bypass=true)
    {
        $result = $bypass ? $key : null;
        switch ($key) {
            case 'tax_amount':
                $result = new \Zend_Db_Expr("main_table.qty*((oi.base_tax_amount)/(if(oi.qty_ordered!=0&&oi.qty_ordered is not null, oi.qty_ordered, 1)))");
                break;
            case 'po_row_total':
                $result = new \Zend_Db_Expr("main_table.qty*((oi.base_row_total+oi.base_tax_amount)/(if(oi.qty_ordered!=0&&oi.qty_ordered is not null, oi.qty_ordered, 1)))");
                break;
        }
        return $result;
    }

    protected function _prepareCollection()
    {
        $res = $this->_hlp->rHlp();

        $collection = $this->_itemCollection;
        $collection->getSelect()
            ->join(['oi'=>$res->getTableName('sales_order_item')], 'oi.item_id=main_table.order_item_id', [])
            ->join(['t'=>$res->getTableName('udropship_po')], 't.entity_id=main_table.parent_id', ['udropship_vendor', 'udropship_available_at', 'udropship_method', 'udropship_method_description', 'udropship_status', 'base_shipping_amount', 'po_increment_id'=>'increment_id', 'created_at'])
            ->join(['o'=>$res->getTableName('sales_order')], 'o.entity_id=oi.order_id', ['order_status'=>'o.status', 'order_increment_id'=>'o.increment_id', 'order_created_at'=>'o.created_at'])
        ;

        $collection->getSelect()->where(
        	'oi.parent_item_id is null'
        );

        $this->setCollection($collection);

        $this->_eventManager->dispatch('udpo_adminhtml_report_item_grid_prepare_collection', ['grid'=>$this]);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $hlp = $this->_hlp;

        $this->addColumn('order_increment_id', [
            'header'    => __('Order #'),
            'index'     => 'order_increment_id',
        	'filter_index' => 'o.increment_id',
            'type'      => 'text',
        ]);

        $this->addColumn('order_created_at', [
            'header'    => __('Order Date'),
            'index'     => 'order_created_at',
        	'filter_index' => 'o.created_at',
            'type'      => 'datetime',
        ]);

        $this->addColumn('order_status', [
            'header'    => __('Order Status'),
            'index'     => 'order_status',
            'filter_index' => 'o.status',
            'type' => 'options',
            'options' => $this->_orderConfig->getStatuses(),
        ]);

        $this->addColumn('po_increment_id', [
            'header'    => __('PO #'),
            'index'     => 'po_increment_id',
            'filter_index' => 't.increment_id',
            'type'      => 'text',
        ]);

        $this->addColumn('created_at', [
            'header'    => __('PO Date'),
            'index'     => 'created_at',
            'filter_index' => 't.created_at',
            'type'      => 'datetime',
        ]);

        $this->addColumn('udropship_status', [
            'header' => __('PO Status'),
            'index' => 'udropship_status',
            'filter_index' => 't.udropship_status',
            'type' => 'options',
            'options' => $this->_modelSource->setPath('shipment_statuses')->toOptionHash(),
        ]);

        /*
        $this->addColumn('base_shipping_amount', array(
            'header' => __('PO Shipping Price'),
            'index' => 'base_shipping_amount',
            'filter_index' => 't.base_shipping_amount',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => $this->_configScopeConfigInterface->getValue('currency/options/base', ScopeInterface::SCOPE_STORE),
        ));
        */

        $this->addColumn('udropship_vendor', [
            'header' => __('Vendor'),
            'index' => 'udropship_vendor',
        	'filter_index' => 't.udropship_vendor',
            'type' => 'options',
            'options' => $this->_modelSource->setPath('vendors')->toOptionHash(),
            'filter' => '\Unirgy\Dropship\Block\Vendor\GridColumnFilter'
        ]);

        $this->addColumn('sku', [
            'header' => __('PO Item SKU'),
            'index' => 'sku',
        	'filter_index' => 'main_table.sku',
        ]);

        $this->addColumn('name', [
            'header' => __('PO Item Name'),
            'index' => 'name',
        	'filter_index' => 'main_table.name',
        ]);

        $this->addColumn('price', [
            'header' => __('PO Item Price'),
            'index' => 'price',
        	'filter_index' => 'main_table.price',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => $this->_scopeConfig->getValue('currency/options/base', ScopeInterface::SCOPE_STORE),
        ]);

        $this->addColumn('base_discount_amount', [
            'header' => __('PO Item Discount'),
            'index' => 'base_discount_amount',
        	'filter_index' => 'main_table.base_discount_amount',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => $this->_scopeConfig->getValue('currency/options/base', ScopeInterface::SCOPE_STORE),
        ]);

        $this->addColumn('base_cost', [
            'header' => __('PO Item Cost'),
            'index' => 'base_cost',
        	'filter_index' => 'main_table.base_cost',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => $this->_scopeConfig->getValue('currency/options/base', ScopeInterface::SCOPE_STORE),
        ]);

        $this->addColumn('qty', [
            'header'    => __('PO Item Qty'),
            'index'     => 'qty',
            'type'      => 'number',
        ]);

        $this->addColumn('base_tax_amount', [
            'header' => __('PO Item Tax'),
            'index' => 'base_tax_amount',
        	'filter_index' => 'main_table.base_tax_amount',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => $this->_scopeConfig->getValue('currency/options/base', ScopeInterface::SCOPE_STORE),
        ]);

        $this->addColumn('base_row_total', [
            'header' => __('PO Item Row Total'),
            'index' => 'base_row_total',
            'filter_index' => 'main_table.base_row_total',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => $this->_scopeConfig->getValue('currency/options/base', ScopeInterface::SCOPE_STORE),
        ]);

        $this->addExportType('*/*/itemExportCsv', __('CSV'));
        $this->addExportType('*/*/itemExportXml', __('XML'));
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/itemGrid', ['_current'=>true]);
    }
}

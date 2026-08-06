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
 * @package    Unirgy_DropshipPo
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */
 
namespace Unirgy\DropshipPo\Block\Adminhtml\SalesOrderViewTab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Helper\Data as HelperData;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Unirgy\DropshipPo\Model\Source as ModelSource;
use Unirgy\Dropship\Helper\Data as DropshipHelperData;
use Unirgy\Dropship\Model\Source;

class Udpos
    extends \Magento\Backend\Block\Widget\Grid\Extended
    implements TabInterface
{
    /**
     * @var DropshipHelperData
     */
    protected $_hlp;

    /**
     * @var Source
     */
    protected $_src;

    /**
     * @var ModelSource
     */
    protected $_poSrc;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    public function __construct(Context $context,
        HelperData $backendHelper, 
        DropshipHelperData $helperData, 
        Source $modelSource, 
        ModelSource $dropshipPoModelSource,
        Registry $frameworkRegistry,
        array $data = [])
    {
        $this->_hlp = $helperData;
        $this->_src = $modelSource;
        $this->_poSrc = $dropshipPoModelSource;
        $this->_coreRegistry = $frameworkRegistry;

        parent::__construct($context, $backendHelper, $data);
        $this->setId('sales_order_udposgrid');
        $this->setUseAjax(true);
    }

    protected function _getCollectionClass()
    {
        return '\Unirgy\DropshipPo\Model\ResourceModel\Po\GridCollection';
    }

    protected function _prepareCollection()
    {
        $collection = $this->_hlp->createObj($this->_getCollectionClass())
            ->addFieldToSelect('entity_id')
            ->addFieldToSelect('created_at')
            ->addFieldToSelect('increment_id')
            ->addFieldToSelect('total_qty')
            ->addFieldToSelect('shipping_name')
            ->addFieldToSelect('base_shipping_amount')
            ->addFieldToSelect('total_cost')
            ->addFieldToSelect('udropship_status')
            ->addFieldToSelect('udropship_vendor')
            ->addFieldToSelect('udropship_method_description')
            ->setOrderFilter($this->getOrder())
        ;
        if ($this->_hlp->isModuleActive('Unirgy_DropshipStockPo')) {
            $collection->addFieldToSelect('ustock_vendor');
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', [
            'header' => __('Purchase Order #'),
            'index' => 'increment_id',
        ]);

        $this->addColumn('shipping_name', [
            'header' => __('Ship to Name'),
            'index' => 'shipping_name',
        ]);

        $this->addColumn('created_at', [
            'header' => __('Date Created'),
            'index' => 'created_at',
            'type' => 'datetime',
        ]);

        $this->addColumn('udropship_vendor', [
            'header' => __('Vendor'),
            'index' => 'udropship_vendor',
            'type' => 'options',
            'options' => $this->_src->setPath('vendors')->toOptionHash(),
            'filter' => '\Unirgy\Dropship\Block\Vendor\GridColumnFilter'
        ]);
        
        if ($this->_hlp->isModuleActive('Unirgy_DropshipStockPo')) {
            $this->addColumn('ustock_vendor', [
                'header' => __('Stock Vendor'),
                'index' => 'ustock_vendor',
                'type' => 'options',
                'options' => $this->_src->setPath('vendors')->toOptionHash(),
                'filter' => '\Unirgy\Dropship\Block\Vendor\GridColumnFilter'
            ]);
        }
        
        $this->addColumn('udropship_method_description', [
            'header' => __('Method'),
            'index' => 'udropship_method_description',
        ]);
        
        $this->addColumn('base_shipping_amount', [
            'header' => __('Shipping Price'),
            'index' => 'base_shipping_amount',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => $this->_scopeConfig->getValue('currency/options/base', ScopeInterface::SCOPE_STORE),
        ]);

        if ($this->_authorization->isAllowed('Unirgy_DropshipPo::action_view_cost')
            && $this->_authorization->isAllowed('Unirgy_DropshipPo::action_view_order_cost')
        ) {
        $this->addColumn('total_cost', [
            'header' => __('Total Cost'),
            'index' => 'total_cost',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => $this->_scopeConfig->getValue('currency/options/base', ScopeInterface::SCOPE_STORE),
        ]);
        }

        $this->addColumn('total_qty', [
            'header' => __('Total Qty'),
            'index' => 'total_qty',
            'type'  => 'number',
        ]);

        $this->addColumn('udropship_status', [
            'header' => __('Status'),
            'index' => 'udropship_status',
            'type' => 'options',
            //'renderer' => 'udpo/adminhtml_po_gridRenderer_status',
            'options' => $this->_poSrc->setPath('po_statuses')->toOptionHash(),
        ]);

        return parent::_prepareColumns();
    }

    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    public function getRowUrl($row)
    {
        return $this->getUrl(
            'udpo/order_po/view',
            [
                'udpo_id'=> $row->getId(),
                'order_id'  => $row->getOrderId()
             ]);
    }

    public function getGridUrl()
    {
        return $this->getUrl('udpo/order_po/udposTab', ['_current' => true]);
    }

    public function getTabLabel()
    {
        return __('Purchase Orders');
    }

    public function getTabTitle()
    {
        return __('Purchase Orders');
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

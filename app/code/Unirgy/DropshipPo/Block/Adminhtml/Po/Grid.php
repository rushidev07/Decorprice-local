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

namespace Unirgy\DropshipPo\Block\Adminhtml\Po;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid as WidgetGrid;
use Magento\Backend\Helper\Data as HelperData;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Unirgy\DropshipPo\Model\Source as ModelSource;
use Unirgy\Dropship\Helper\Data as DropshipHelperData;
use Unirgy\Dropship\Model\Source;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var Source
     */
    protected $_src;

    /**
     * @var DropshipHelperData
     */
    protected $_hlp;

    /**
     * @var ModelSource
     */
    protected $_poSrc;

    public function __construct(Context $context,
        HelperData $backendHelper,
        Source $modelSource,
        DropshipHelperData $helperData,
        ModelSource $dropshipPoModelSource,
        array $data = []
    )
    {
        $this->_src = $modelSource;
        $this->_hlp = $helperData;
        $this->_poSrc = $dropshipPoModelSource;

        parent::__construct($context, $backendHelper, $data);
        $this->setId('udpo_po_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
    }

    protected function _getCollectionClass()
    {
        return '\Unirgy\DropshipPo\Model\ResourceModel\Po\GridCollection';
    }

    protected function _prepareCollection()
    {
        $collection = $this->_hlp->createObj($this->_getCollectionClass());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', [
            'header'    => __('Purchase Order #'),
            'index'     => 'increment_id',
            'type'      => 'text',
        ]);

        $this->addColumn('created_at', [
            'header'    => __('Purchase Order Created'),
            'index'     => 'created_at',
            'type'      => 'datetime',
        ]);

        $this->addColumn('order_increment_id', [
            'header'    => __('Order #'),
            'index'     => 'order_increment_id',
            'type'      => 'text',
        ]);

        $this->addColumn('order_created_at', [
            'header'    => __('Order Date'),
            'index'     => 'order_created_at',
            'type'      => 'datetime',
        ]);

        $this->addColumn('shipping_name', [
            'header' => __('Ship to Name'),
            'index' => 'shipping_name',
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

        if ($this->_authorization->isAllowed('Unirgy_DropshipPo::action_view_cost')) {
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

        $this->addColumn('statement_date', [
            'header' => __('Statement Ready At'),
            'index' => 'statement_date',
            'type'  => 'date',
        ]);

        $this->addColumn('udropship_status', [
            'header' => __('Status'),
            'index' => 'udropship_status',
            'type' => 'options',
            //'renderer' => 'udpo/adminhtml_po_gridRenderer_status',
            'options' => $this->_poSrc->setPath('po_statuses')->toOptionHash(),
        ]);

        $this->addColumn('action',
            [
                'header'    => __('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => [
                    [
                        'caption' => __('View'),
                        'url'     => ['base'=>'udpo/po/view'],
                        'field'   => 'udpo_id'
                    ]
                ],
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true
        ]);

        $this->_eventManager->dispatch('adminhtml_udpo_grid_prepare_columns', ['grid'=>$this]);

        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportXml', __('XML'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        if (!$this->_authorization->isAllowed('Unirgy_DropshipPo::udpo')) {
            return false;
        }

        return $this->getUrl('udpo/po/view',
            [
                'udpo_id'=> $row->getId(),
            ]
        );
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('udpo_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        $this->getMassactionBlock()->addItem('pdf_udpos', [
             'label'=> __('PDF Purchase Orders'),
             'url'  => $this->getUrl('*/po/pdfUdpos'),
        ]);

        $this->getMassactionBlock()->addItem('pesend_udpos', [
             'label'=> __('Resend Vendor PO Notification'),
             'url'  => $this->getUrl('*/po/resendUdpos'),
        ]);

        if ($this->_scopeConfig->isSetFlag('udropship/purchase_order/allow_delete_po', ScopeInterface::SCOPE_STORE)) {
            $this->getMassactionBlock()->addItem('delete_udpos', [
                'label'=> __('Delete POs and Notify Vendors'),
                'url'  => $this->getUrl('*/po/massDelete'),
            ]);
        }

        $this->_eventManager->dispatch('udpo_adminhtml_po_grid_prepare_massaction', ['grid'=>$this]);

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/*', ['_current' => true]);
    }

}

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

namespace Unirgy\Dropship\Block\Adminhtml\Vendor;

use \Magento\Backend\Block\Template\Context;
use \Magento\Backend\Helper\Data as HelperData;
use \Magento\Framework\Event\ManagerInterface;
use \Unirgy\Dropship\Helper\Data as DropshipHelperData;
use \Unirgy\Dropship\Model\Source;
use \Unirgy\Dropship\Model\Vendor;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var DropshipHelperData
     */
    protected $_hlp;

    const FORM_FIELD_NAME = 'vendor';
    const FORM_FIELD_IDNAME = 'vendor_id';
    const FORM_FIELD_NAME_INTERNAL = 'internal_vendor';

    public function __construct(
        DropshipHelperData $helperData,
        Context $context,
        HelperData $backendHelper,
        array $data = []
    ) {
    
        $this->_hlp = $helperData;

        parent::__construct($context, $backendHelper, $data);
        $this->setId('vendorGrid');
        $this->setDefaultSort('vendor_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('vendor_filter');
    }

    protected function _prepareCollection()
    {
        $collection = $this->_hlp->createObj('\Unirgy\Dropship\Model\Vendor')->getCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();
        if ($this->_isExport && ($selectedIds = $this->getSelectedIds())) {
            $collection->addVendorFilter($selectedIds);
        }
        return $this;
    }

    protected function getSelectedIds()
    {
        $selectedIds = $this->getRequest()->getParam(self::FORM_FIELD_NAME_INTERNAL);
        $selectedIds = is_array($selectedIds) ? $selectedIds : explode(',',$selectedIds);
        return $selectedIds;
    }

    protected function _prepareColumns()
    {
        $hlp = $this->_hlp;
        $this->addColumn('vendor_id', [
            'header'    => __('Vendor ID'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'vendor_id',
            'type'      => 'number',
        ]);

        $this->addColumn('vendor_name', [
            'header'    => __('Vendor Name'),
            'index'     => 'vendor_name',
        ]);

        $this->addColumn('email', [
            'header'    => __('Email'),
            'index'     => 'email',
        ]);

        if ($hlp->isModuleActive('Unirgy_DropshipStockPo')) {
            $this->addColumn('distributor_id', [
                'header' => __('Distributor'),
                'index' => 'distributor_id',
                'type' => 'options',
                'options' => $this->_hlp->src()->setPath('vendors')->toOptionHash(),
            ]);
        }

        $this->addColumn('carrier_code', [
            'header'    => __('Used Carrier'),
            'index'     => 'carrier_code',
            'type'      => 'options',
            'options'   => $this->_hlp->src()->setPath('carriers')->toOptionHash(),
        ]);

        if ($this->_hlp->isUdsprofileActive()) {
            $this->addColumn('shipping_profile', [
                'header'    => __('Shipping Profile'),
                'index'     => 'shipping_profile',
                'type'      => 'options',
                'options'   => $this->_hlp->getObj('\Unirgy\DropshipShippingProfile\Model\Source')->setPath('profiles')->toOptionHash(),
            ]);
        }

        $this->addColumn('status', [
            'header'    => __('Status'),
            'index'     => 'status',
            'type'      => 'options',
            'options'   => $this->_hlp->src()->setPath('vendor_statuses')->toOptionHash(),
        ]);

        $this->addColumn(
            'action',
            [
                'header'    => __('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => [
                    [
                        'caption' => __('View'),
                        'url'     => ['base'=>'udropship/vendor/edit'],
                        'field'   => 'id'
                    ]
                ],
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true
            ]
        );

        $this->_eventManager->dispatch('udropship_adminhtml_vendor_grid_prepare_columns', ['grid'=>$this]);

        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportXml', __('XML'));
        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField(self::FORM_FIELD_IDNAME);
        $this->getMassactionBlock()->setFormFieldName(self::FORM_FIELD_NAME);

        $this->getMassactionBlock()->addItem('delete', [
             'label'=> __('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => __('Are you sure?')
        ]);

        $this->getMassactionBlock()->addItem('status', [
             'label'=> __('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', ['_current'=>true]),
             'additional' => [
                    'status' => [
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => __('Status'),
                         'values' => $this->_hlp->src()->setPath('vendor_statuses')->toOptionArray(true),
                     ]
             ]
        ]);

        $this->getMassactionBlock()->addItem('carrier_code', [
            'label'=> __('Change Preferred Carrier'),
            'url'  => $this->getUrl('*/*/massCarrierCode', ['_current'=>true]),
            'additional' => [
                'carrier_code' => [
                    'name' => 'carrier_code',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => __('Preferred Carrier'),
                    'values' => $this->_hlp->src()->setPath('carriers')->toOptionHash(true),
                ]
            ]
        ]);

        if ($this->_hlp->isUdsprofileActive()) {
            $this->getMassactionBlock()->addItem('shipping_profile', [
                'label'=> __('Change Shipping Profile'),
                'url'  => $this->getUrl('*/*/massShippingProfile', ['_current'=>true]),
                'additional' => [
                    'shipping_profile' => [
                        'name' => 'shipping_profile',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Shipping Profile'),
                        'values' => $this->_hlp->getObj('\Unirgy\DropshipShippingProfile\Model\Source')->setPath('profiles')->toOptionHash(true),
                    ]
                ]
            ]);
        }

        $this->_eventManager->dispatch('udropship_adminhtml_vendor_grid_prepare_massaction', ['grid'=>$this]);

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current'=>true]);
    }
}

<?php

namespace Unirgy\Dropship\Block\Adminhtml\Vendor\Statement;

use \Magento\Backend\Block\Template\Context;
use \Magento\Backend\Block\Widget\Grid as WidgetGrid;
use \Magento\Backend\Helper\Data as HelperData;
use \Magento\Store\Model\StoreManagerInterface;
use \Unirgy\Dropship\Helper\Data as DropshipHelperData;
use \Unirgy\Dropship\Model\Source;
use \Unirgy\Dropship\Model\Vendor\Statement;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var DropshipHelperData
     */
    protected $_hlp;

    public function __construct(
        DropshipHelperData $helperData,
        Context $context,
        HelperData $backendHelper,
        array $data = []
    ) {
    
        $this->_hlp = $helperData;

        parent::__construct($context, $backendHelper, $data);
        $this->setId('statementGrid');
        $this->setDefaultSort('vendor_statement_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('statement_filter');
    }

    protected function _prepareCollection()
    {
        $collection = $this->_hlp->createObj('\Unirgy\Dropship\Model\Vendor\Statement')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $hlp = $this->_hlp;
        $baseUrl = $this->getUrl();

        $this->addColumn('vendor_statement_id', [
            'header'    => __('ID'),
            'index'     => 'vendor_statement_id',
            'width'     => 10,
            'type'      => 'number',
        ]);

        $this->addColumn('created_at', [
            'header'    => __('Created At'),
            'index'     => 'created_at',
            'type'      => 'datetime',
            'width'     => 150,
        ]);

        $this->addColumn('statement_id', [
            'header'    => __('Statement ID'),
            'index'     => 'statement_id',
        ]);

        $this->addColumn('vendor_id', [
            'header' => __('Vendor'),
            'index' => 'vendor_id',
            'type' => 'options',
            'options' => $this->_hlp->src()->setPath('vendors')->toOptionHash(),
            'filter' => '\Unirgy\Dropship\Block\Vendor\GridColumnFilter'
        ]);

        $this->addColumn('statement_period', [
            'header' => __('Period'),
            'index' => 'statement_period',
        ]);

        $this->addColumn('total_orders', [
            'header'    => __('# of Orders'),
            'index'     => 'total_orders',
            'type'      => 'number',
        ]);

        if (!$hlp->isStatementAsInvoice()) {
            $this->addColumn('total_payout', [
                'header'    => __('Total Payment'),
                'index'     => 'total_payout',
                'type'      => 'price',
                'currency_code' => $this->_storeManager->getStore()->getBaseCurrency()->getCode(),
            ]);

            if ($hlp->isUdpayoutActive()) {
                $this->addColumn('total_paid', [
                    'header'    => __('Total Paid'),
                    'index'     => 'total_paid',
                    'type'      => 'price',
                    'currency_code' => $this->_storeManager->getStore()->getBaseCurrency()->getCode(),
                ]);
                $this->addColumn('total_due', [
                    'header'    => __('Total Due'),
                    'index'     => 'total_due',
                    'type'      => 'price',
                    'currency_code' => $this->_storeManager->getStore()->getBaseCurrency()->getCode(),
                ]);
            }
        } else {
            $this->addColumn('total_invoice', [
                'header'    => __('Total Invoice'),
                'index'     => 'total_invoice',
                'type'      => 'price',
                'currency_code' => $this->_storeManager->getStore()->getBaseCurrency()->getCode(),
            ]);
        }

        $this->addColumn('email_sent', [
            'header' => __('Sent'),
            'index' => 'email_sent',
            'type' => 'options',
            'options' => $this->_hlp->src()->setPath('yesno')->toOptionHash(),
        ]);

        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportXml', __('XML'));
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('vendor_statement_id');
        $this->getMassactionBlock()->setFormFieldName('statement');

        $this->getMassactionBlock()->addItem('delete', [
             'label'=> __('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => __('Deleting selected statement(s). Are you sure?')
        ]);
        
        $this->getMassactionBlock()->addItem('refresh', [
             'label'=> __('Refresh'),
             'url'  => $this->getUrl('*/*/massRefresh', ['_current'=>true]),
        ]);

        $this->getMassactionBlock()->addItem('download', [
             'label'=> __('Download/Print'),
             'url'  => $this->getUrl('*/*/massDownload', ['_current'=>true]),
        ]);

        $this->getMassactionBlock()->addItem('email', [
             'label'=> __('Send Emails'),
             'url'  => $this->getUrl('*/*/massEmail', ['_current'=>true]),
             'confirm' => __('Emailing selected statement(s) to vendors. Are you sure?')
        ]);

        return $this;
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current'=>true]);
    }
}

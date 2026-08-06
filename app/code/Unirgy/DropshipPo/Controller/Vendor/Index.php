<?php

namespace Unirgy\DropshipPo\Controller\Vendor;

use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\HTTP\Header;
use Magento\Framework\Model\Date;
use Magento\Framework\Registry;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Unirgy\DropshipPo\Helper\Data as DropshipPoHelperData;
use Unirgy\Dropship\Helper\Data as HelperData;

class Index extends AbstractVendor
{

    public function execute()
    {
    	$_hlp = $this->_hlp;
        switch ($this->getRequest()->getParam('submit_action')) {
            case 'updateUdpoStatus':
                $this->_forward('updateUdpoStatus', 'vendor', 'udpo');
                return;
            case 'udpoMultiPdf':
                $this->_forward('udpoMultiPdf', 'vendor', 'udpo');
                return;
            case 'udpoLabelBatch':
                $this->_forward('udpoLabelBatch', 'vendor', 'udpo');
                return;
	        case 'labelBatch':
	        case __('Create and Download Labels Batch'):
	            $this->_forward('labelBatch', 'vendor', 'udpo');
	            return;

	        case 'existingLabelBatch':
	            $this->_forward('existingLabelBatch', 'vendor', 'udpo');
	            return;

	        case 'packingSlips':
	        case __('Download Packing Slips'):
	            $this->_forward('packingSlips', 'vendor', 'udpo');
	            return;

	        case 'updateShipmentsStatus':
	            $this->_forward('updateShipmentsStatus', 'vendor', 'udpo');
	            return;
            case 'udbatchExport':
                $this->_forward('exportOrders', 'vendor_batch', 'udbatch');
                return;

            case 'createShipmentAndEmail':
                $this->getRequest()->setParam('send_customer_notification', true);
            case 'createShipment':
                $this->_forward('createShipment', 'vendor', 'udpo');
                return;

            default:
                return $this->_renderPage(null, 'dashboard');
        }
    }
}

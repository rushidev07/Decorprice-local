<?php

namespace Unirgy\DropshipPo\Controller\Adminhtml\Order\Po;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Unirgy\DropshipPo\Helper\Data as HelperData;
use Unirgy\DropshipPo\Model\PoFactory;
use Unirgy\Dropship\Helper\Data as DropshipHelperData;
use Unirgy\Dropship\Helper\ProtectedCode;

class Invoices extends AbstractPo
{
    public function execute()
    {
        $this->_initPo(false);
        $this->_view->loadLayout();
        $this->_view->getLayout()
            ->getBlock('udpo.po.invoicesgrid');
        $this->_view->renderLayout();
    }
}

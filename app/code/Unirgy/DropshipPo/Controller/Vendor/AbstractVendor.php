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


namespace Unirgy\DropshipPo\Controller\Vendor;

use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\HTTP\Header;
use Magento\Framework\Registry;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Unirgy\DropshipPo\Helper\Data as DropshipPoHelperData;
use Unirgy\DropshipPo\Model\Source;
use Unirgy\Dropship\Controller\Vendor\AbstractVendor as VendorAbstractVendor;
use Unirgy\Dropship\Helper\Data as HelperData;

abstract class AbstractVendor extends VendorAbstractVendor
{
    /**
     * @var DropshipPoHelperData
     */
    protected $_poHlp;

    protected $_poFactory;
    protected $_trackFactory;
    protected $_labelBatchFactory;

    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig, 
        DesignInterface $viewDesignInterface, 
        StoreManagerInterface $storeManager, 
        LayoutFactory $viewLayoutFactory, 
        Registry $registry, 
        ForwardFactory $resultForwardFactory, 
        HelperData $helper, 
        PageFactory $resultPageFactory, 
        RawFactory $resultRawFactory, 
        Header $httpHeader, 
        DropshipPoHelperData $dropshipPoHelperData,
        \Unirgy\DropshipPo\Model\PoFactory $poFactory,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \Unirgy\Dropship\Model\Label\BatchFactory $labelBatchFactory
    )
    {
        $this->_poHlp = $dropshipPoHelperData;
        $this->_poFactory = $poFactory;
        $this->_trackFactory = $trackFactory;
        $this->_labelBatchFactory = $labelBatchFactory;

        parent::__construct($context, $scopeConfig, $viewDesignInterface, $storeManager, $viewLayoutFactory, $registry, $resultForwardFactory, $helper, $resultPageFactory, $resultRawFactory, $httpHeader);
    }

    protected function _preparePoMultiPdf($udpos)
    {
        $vendorId = $this->_getSession()->getId();
        $vendor = $this->_hlp->getVendor($vendorId);

        foreach ($udpos as $udpo) {
            if ($udpo->getUdropshipVendor()!=$vendorId) {
                throw new \Exception('You are not authorized to print this purchase order');
            }
        }

        if ($this->_hlp->getScopeConfig('udropship/purchase_order/ready_on_pdf')) {
            $udpoHlp = $this->_poHlp;
            foreach ($udpos as $udpo) {
                $udpo->addComment(__('%1 printed purchase order pdf', $vendor->getVendorName()), false, true);
                if ($udpo->getUdropshipStatus()==Source::UDPO_STATUS_PENDING) {
                    $udpoHlp->processPoStatusSave($udpo, Source::UDPO_STATUS_READY, true);
                }
                $udpo->save();
            }
        }

        foreach ($udpos as $udpo) {
            $order = $udpo->getOrder();
            $order->setData('__orig_shipping_amount', $order->getShippingAmount());
            $order->setData('__orig_base_shipping_amount', $order->getBaseShippingAmount());
            $order->setShippingAmount($udpo->getShippingAmount());
            $order->setBaseShippingAmount($udpo->getBaseShippingAmount());
        }

        $theme = $this->_hlp->getScopeConfig('udropship/admin/interface_theme', 0);
        if (empty($theme)) {
            $theme = null;
        }
        $this->_hlp->setDesignStore(0, 'adminhtml', $theme);

        $pdf = $this->_poHlp->getVendorPoMultiPdf($udpos);
        $filename = 'purchase_order_'.$this->_hlp->now().'.pdf';

        foreach ($udpos as $udpo) {
            $order = $udpo->getOrder();
            $order->setShippingAmount($order->getData('__orig_shipping_amount'));
            $order->setBaseShippingAmount($order->getData('__orig_base_shipping_amount'));
        }
        $this->_hlp->setDesignStore();

        $this->_hlp->sendDownload($filename, $pdf->render(), 'application/x-pdf');
    }



    public function getVendorPoCollection()
    {
        return $this->_poHlp->getVendorPoCollection();
    }

    public function getVendorShipmentCollection()
    {
        return $this->_poHlp->getVendorShipmentCollection();
    }

}

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
 
namespace Unirgy\DropshipPo\Controller\Adminhtml\Po;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Unirgy\DropshipPo\Helper\Data as HelperData;

abstract class AbstractPo extends Action
{
    protected $_poHlp;
    protected $_hlp;
    protected $resultForwardFactory;
    protected $resultPageFactory;
    protected $_resultRedirectFactory;
    protected $_fileFactory;
    protected $_logger;

    public function __construct(
        \Unirgy\DropshipPo\Helper\Data $udpoHelper,
        \Unirgy\Dropship\Helper\Data $udropshipHelper,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Backend\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_poHlp = $udpoHelper;
        $this->_hlp = $udropshipHelper;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->_resultRedirectFactory = $resultRedirectFactory;
        $this->_fileFactory = $fileFactory;
        $this->_logger = $logger;

        parent::__construct($context);
    }

    protected function _prepareUdpoPdf($udpos)
    {
        foreach ($udpos as $udpo) {
            $order = $udpo->getOrder();
            $order->setData('__orig_shipping_amount', $order->getShippingAmount());
            $order->setData('__orig_base_shipping_amount', $order->getBaseShippingAmount());
            $order->setShippingAmount($udpo->getShippingAmount());
            $order->setBaseShippingAmount($udpo->getBaseShippingAmount());
        }
        $pdf = $this->_poHlp->getVendorPoMultiPdf($udpos);
        foreach ($udpos as $udpo) {
            $order = $udpo->getOrder();
            $order->setShippingAmount($order->getData('__orig_shipping_amount'));
            $order->setBaseShippingAmount($order->getData('__orig_base_shipping_amount'));
        }
        return $pdf;
    }


    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Unirgy_DropshipPo::udpo');
    }
    protected function _initAction()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Unirgy_DropshipPo::udpo');
        $resultPage->getConfig()->getTitle()->prepend(__('Purchase Orders'));
        $resultPage->addBreadcrumb(__('Sales'), __('Sales'));
        $resultPage->addBreadcrumb(__('Dropship'), __('Dropship'));
        $resultPage->addBreadcrumb(__('Purchase Orders'), __('Purchase Orders'));
        return $resultPage;
    }


}
<?php

namespace Unirgy\DropshipPo\Observer;

use Magento\Backend\Model\UrlFactory;
use Magento\CatalogInventory\Model\StockFactory;
use Magento\Catalog\Model\ProductFactory as ModelProductFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Layout;
use Magento\Quote\Model\Quote\Config;
use Psr\Log\LoggerInterface;
use Unirgy\DropshipPayout\Model\PayoutFactory;
use Unirgy\DropshipPo\Helper\Data as DropshipPoHelperData;
use Unirgy\Dropship\Helper\Data as HelperData;
use Unirgy\Dropship\Model\Vendor\ProductFactory;
use Unirgy\Dropship\Model\Vendor\StatementFactory;

class UdpoadminOrderPoView extends AbstractObserver implements ObserverInterface
{
    /**
     * @var Layout
     */
    protected $_viewLayout;

    /**
     * @var Registry
     */
    protected $_frameworkRegistry;

    /**
     * @var StatementFactory
     */
    protected $_vendorStatementFactory;

    /**
     * @var UrlFactory
     */
    protected $_modelUrlFactory;

    public function __construct(
        Layout $viewLayout, 
        Registry $frameworkRegistry, 
        StatementFactory $vendorStatementFactory, 
        UrlFactory $modelUrlFactory, 
        \Unirgy\Dropship\Helper\Data $udropshipHelper,
        \Unirgy\DropshipPo\Helper\Data $udpoHelper,
        \Psr\Log\LoggerInterface $logger,
        \Unirgy\Dropship\Model\Vendor\ProductFactory $vendorProductFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Quote\Model\Quote\Config $quoteConfig
    )
    {
        $this->_viewLayout = $viewLayout;
        $this->_frameworkRegistry = $frameworkRegistry;
        $this->_vendorStatementFactory = $vendorStatementFactory;
        $this->_modelUrlFactory = $modelUrlFactory;

        parent::__construct($udropshipHelper, $udpoHelper, $logger, $vendorProductFactory, $productFactory, $quoteConfig);
    }

    public function execute(Observer $observer)
    {
        if (($soi = $this->_viewLayout->getBlock('order_info'))
            && ($po = $this->_frameworkRegistry->registry('current_udpo'))
            && ($vName = $this->_hlp->getVendorName($po->getUdropshipVendor()))
        ) {
            if ($this->_hlp->isModuleActive('Unirgy_DropshipStockPo')
                && (($svName = $this->_hlp->getVendorName($po->getUstockVendor())))
            ) {
                $soi->setStockVendorName($svName);
            }
            $soi->setVendorName($vName);
            if (($stId = $po->getStatementId())) {
                $soi->setStatementId($stId);
                if (($st = $this->_vendorStatementFactory->create()->load($stId, 'statement_id')) && $st->getId()) {
                    $soi->setStatementUrl($this->_modelUrlFactory->create()->getUrl('udropship/vendor_statement/edit', ['id'=>$st->getId()]));
                }
            }
            if ($this->_hlp->isUdpayoutActive() && ($ptId = $po->getPayoutId())) {
                $soi->setPayoutId($ptId);
                if (($pt = $this->_hlp->createObj('Unirgy\DropshipPayout\Model\Payout')->load($ptId)) && $pt->getId()) {
                    $soi->setPayoutUrl($this->_modelUrlFactory->create()->getUrl('udpayout/payout/edit', ['id'=>$pt->getId()]));
                }
            }
        }
    }
}

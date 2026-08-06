<?php

namespace Unirgy\DropshipPo\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Config;
use Unirgy\DropshipPo\Block\Adminhtml\Po\Create\Items;
use Unirgy\DropshipPo\Block\Adminhtml\Po\Editcosts\Items as EditcostsItems;
use Unirgy\DropshipPo\Block\Adminhtml\Po\View\Items as ViewItems;
use Unirgy\Dropship\Helper\Item;

class CoreBlockAbstractToHtmlBefore extends AbstractObserver implements ObserverInterface
{
    /**
     * @var Item
     */
    protected $_iHlp;

    public function __construct(
        Item $helperItem,
        \Unirgy\Dropship\Helper\Data $udropshipHelper,
        \Unirgy\DropshipPo\Helper\Data $udpoHelper,
        \Psr\Log\LoggerInterface $logger,
        \Unirgy\Dropship\Model\Vendor\ProductFactory $vendorProductFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Quote\Model\Quote\Config $quoteConfig
    )
    {
        $this->_iHlp = $helperItem;
        parent::__construct($udropshipHelper, $udpoHelper, $logger, $vendorProductFactory, $productFactory, $quoteConfig);
    }

    public function execute(Observer $observer)
    {
        $block = $observer->getBlock();
        if (($block instanceof Items)
            && ($order = $block->getOrder())
        ) {
            foreach ($order->getAllItems() as $item) {
                $item->setOrderItem($item);
            }
            $this->_hlp->addVendorSkus($order);
            foreach ($order->getAllItems() as $item) {
                if ($item->isDummy(true)) continue;
                $this->_iHlp->attachOrderItemVendorSkuInfo($item, $item);
            }
        }
        if (($block instanceof ViewItems
                || $block instanceof EditcostsItems
            )
            && (($po = $block->getPo())
                || $block->getParentBlock() && ($order = $block->getParentBlock()->getPo()))
        ) {
            $this->_hlp->addVendorSkus($po);
            foreach ($po->getAllItems() as $item) {
                $oItem = $item->getOrderItem();
                if ($oItem->isDummy(true)) continue;
                $this->_iHlp->attachOrderItemVendorSkuInfo($item, $oItem);
            }
        }
    }
}

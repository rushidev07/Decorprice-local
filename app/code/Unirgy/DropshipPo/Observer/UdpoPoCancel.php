<?php

namespace Unirgy\DropshipPo\Observer;

use Magento\CatalogInventory\Model\StockFactory;
use Magento\Catalog\Model\ProductFactory as ModelProductFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Config;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;
use Unirgy\DropshipPo\Helper\Data as DropshipPoHelperData;
use Unirgy\Dropship\Helper\Data as HelperData;
use Unirgy\Dropship\Model\Vendor\ProductFactory;

class UdpoPoCancel extends AbstractObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $this->_isPoCancel=true;
        $order = $observer->getOrder();
        $udpo = $observer->getUdpo();
        $sId = $order->getStoreId();
        $isMulti = $this->_hlp->isUdmultiActive();
        $localVid = $this->_hlp->getLocalVendorId($sId);
        $isLocalIfInStock = 'local_if_in_stock'==$this->_hlp->getScopeConfig('udropship/stock/availability', $sId);
        $this->_attachVendorProducts([$udpo], $order);
        $result = new DataObject([
            'oiQtyReverts' => [],
            'oiQtyUsed' => [],
            'siQtyCors' => [],
            'vpQtyCors' => [],
        ]);
        $itemsToSave = [];
        $parentItems = [];
        foreach ($udpo->getAllItems() as $item) {
            $oItem = $item->getOrderItem();
            $product = $item->getProduct();
            $children = $oItem->getChildrenItems() ? $oItem->getChildrenItems() : $oItem->getChildren();
            if ($children) {
                $parentItems[$oItem->getId()] = $item;
            } else {
                $qty = $this->_correctItemQty($item, $parentItems, 'getCurrentlyCanceledQty');
                if ($isMulti || !$isLocalIfInStock || $localVid==$item->getUdropshipVendor()) {
                    $this->_applyQtyCorrection($oItem, $item, $qty, $result, false);
                }
            }
        }
        $this->_saveQtyCorrections($result);
        $this->_isPoCancel=false;
    }
}

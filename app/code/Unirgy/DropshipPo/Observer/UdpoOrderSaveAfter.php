<?php

namespace Unirgy\DropshipPo\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Config;

class UdpoOrderSaveAfter extends AbstractObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        $udpos = $observer->getUdpos();
        $sId = $order->getStoreId();
        $isMulti = $this->_hlp->isUdmultiActive();
        $localVid = $this->_hlp->getLocalVendorId($sId);
        $isLocalIfInStock = 'local_if_in_stock'==$this->_hlp->getScopeConfig('udropship/stock/availability', $sId);
        $this->_attachVendorProducts($udpos, $order);
        $result = new DataObject([
            'oiQtyUsed' => [],
            'oiQtyReverts' => [],
            'siQtyCors' => [],
            'vpQtyCors' => [],
        ]);
        foreach ($udpos as $udpo) {
            $parentItems = [];
            foreach ($udpo->getAllItems() as $item) {
                $oItem = $item->getOrderItem();
                $product = $item->getProduct();
                $children = $oItem->getChildrenItems() ? $oItem->getChildrenItems() : $oItem->getChildren();
                if ($children) {
                    $parentItems[$oItem->getId()] = $item;
                } else {
                    $qty = $this->_correctItemQty($item, $parentItems);
                    if ($oItem->getUdropshipVendor()!=$item->getUdropshipVendor()
                        && ($isMulti || !$isLocalIfInStock || $localVid==$oItem->getUdropshipVendor())
                    ) {
                        $this->_applyQtyCorrection($oItem, $item, $qty, $result, true);
                    }
                    if ($isMulti || !$isLocalIfInStock || $localVid==$item->getUdropshipVendor()) {
                        $this->_applyQtyCorrection($oItem, $item, -$qty, $result, false);
                    }
                }
            }
        }
        $this->_saveQtyCorrections($result);
    }
}

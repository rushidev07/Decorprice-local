<?php

namespace Unirgy\DropshipPo\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SalesModelServiceQuoteSubmitBefore extends AbstractObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        foreach ($order->getAllItems() as $item) {
            if ($item->getParentItem()) {
                $item->setUdpoSeqNumber($item->getParentItem()->getUdpoSeqNumber());
            }
        }
    }
}

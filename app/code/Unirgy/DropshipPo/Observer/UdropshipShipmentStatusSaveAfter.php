<?php

namespace Unirgy\DropshipPo\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Config;

class UdropshipShipmentStatusSaveAfter extends AbstractObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $this->_poHlp->invoiceShipment($observer->getEvent()->getShipment());
    }
}

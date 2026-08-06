<?php

namespace Unirgy\Dropship\Observer;

use \Magento\Framework\DataObject;
use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;
use \Unirgy\Dropship\Observer\AbstractObserver;

class SalesOrderItemColletionLoadAfter extends AbstractObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $items = $observer->getEvent()->getOrderItemCollection();
        foreach ($items as $item) {
            $prodOptions = $item->getProductOptions();
            $checkTimes = 0;
            while (!is_array($prodOptions) && ++$checkTimes<10) {
                $prodOptions = $this->_hlp->unserialize($prodOptions);
            }
            if (!$this->_hlp->hasMageFeature('serialize')) {
                $item->setData('product_options', $this->_hlp->serialize($prodOptions));
            } else {
                $item->setProductOptions($prodOptions);
            }
        }
    }
}

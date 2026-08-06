<?php

namespace Unirgy\Dropship\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AdminSessionUserLoginSuccess extends AbstractObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $user = $observer->getEvent()->getUser();
        $this->_vendorAsAdmin->loginAdminVendor($user);
    }
}

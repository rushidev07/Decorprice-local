<?php

namespace Unirgy\Dropship\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ControllerActionPostdispatchAdminhtmlIndexLogout extends AbstractObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $vendorId = $this->_vendorAsAdmin->getVendorId();
        if (!$vendorId) {
            return;
        }
        $this->_vendorAsAdmin->logoutAdminVendor($vendorId);
        return;
    }
}

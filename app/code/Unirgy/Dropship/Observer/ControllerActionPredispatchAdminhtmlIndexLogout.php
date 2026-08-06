<?php

namespace Unirgy\Dropship\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ControllerActionPredispatchAdminhtmlIndexLogout extends AbstractObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        /** @var \Magento\Backend\Model\Auth $auth */
        $auth = $this->_hlp->getObj('\Magento\Backend\Model\Auth');
        $user = $auth->getUser();
        if ($user) {
            $this->_vendorAsAdmin->setVendorId($user->getUdropshipVendor());
        }
    }
}

<?php

namespace Unirgy\Dropship\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class UdropshipVendorLogout extends AbstractObserver implements ObserverInterface
{
    /**
     * @return \Unirgy\Dropship\Helper\Data
     */
    protected function _hlp()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get('Unirgy\Dropship\Helper\Data');
    }

    public function execute(Observer $observer)
    {
        $vendor = $observer->getEvent()->getVendor();
        $this->_hlp()->getObj('Unirgy\Dropship\Model\VendorAsAdminInterface')->logoutAsAdmin($vendor);
    }
}

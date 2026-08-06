<?php

namespace Unirgy\Dropship\Block\Vendor;

use \Magento\Framework\View\Element\Template;

class HtmlHeader extends Template
{
    /**
     * @return \Unirgy\Dropship\Helper\Data
     */
    protected function _hlp()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get('Unirgy\Dropship\Helper\Data');
    }
    public function getVendorMenu()
    {
        $isLoggedIn = $this->_hlp()->session()->isLoggedIn();
        $vendorMenu = $this->_hlp()->config()->getVendorMenu();
        $removeIds = [];
        foreach ($vendorMenu as $item) {
            if (!$isLoggedIn && !$item->getAllowGuest()) {
                $removeIds[] = $item->getId();
            }
        }
        foreach ($removeIds as $id) {
            $vendorMenu->removeItemByKey($id);
        }
        return $vendorMenu;
    }
}

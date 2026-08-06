<?php

namespace Unirgy\DropshipPo\Controller\Vendor;

use Magento\Store\Model\StoreManagerInterface;

class PackingSlips extends \Unirgy\Dropship\Controller\Vendor\PackingSlips
{
    public function getVendorShipmentCollection()
    {
        return $this->_hlp->getObj('\Unirgy\DropshipPo\Helper\Data')->getVendorShipmentCollection();
    }
}
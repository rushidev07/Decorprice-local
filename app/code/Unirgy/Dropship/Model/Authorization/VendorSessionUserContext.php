<?php

namespace Unirgy\Dropship\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;
use Unirgy\Dropship\Model\Vendor;

class VendorSessionUserContext implements UserContextInterface
{
    /**
     * @var \Unirgy\Dropship\Helper\Data
     */
    protected $_hlp;

    public function __construct(
        \Unirgy\Dropship\Helper\Data $uDropshipHelper
    ) {
        $this->_hlp = $uDropshipHelper;
    }

    public function getUserId()
    {
        return $this->_hlp->isVendorPortalAction() && $this->_hlp->session()->getVendorId()
            ? $this->_hlp->session()->getVendorId()
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserType()
    {
        return Vendor::USER_TYPE_VENDOR;
    }
}
<?php

namespace Unirgy\Dropship\Plugin;

class BackendAuthSession
{
    /**
     * @var \Unirgy\Dropship\Helper\Data
     */
    protected $_hlp;

    /**
     * @var \Unirgy\Dropship\Model\VendorAsAdminInterface
     */
    protected $_vendorAsAdmin;

    public function __construct(
        \Unirgy\Dropship\Helper\Data $udropshipHelper,
        \Unirgy\Dropship\Model\VendorAsAdminInterface $vendorAsAdmin
    ) {
        $this->_hlp = $udropshipHelper;
        $this->_vendorAsAdmin = $vendorAsAdmin;
    }
    public function beforeStart(
        \Magento\Backend\Model\Auth\Session $subject
    ) {
        $this->_vendorAsAdmin->hookBackendSessionStart($subject);
    }
}
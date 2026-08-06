<?php

namespace Unirgy\Dropship\Model;

class VendorAsAdmin implements \Unirgy\Dropship\Model\VendorAsAdminInterface
{
    protected $vendorId;
    public function getVendorId()
    {
        return $this->vendorId;
    }
    public function setVendorId($vendorId)
    {
        $this->vendorId = $vendorId;
        return $this;
    }
    public function loginAsAdmin($vendor)
    {
        return $this;
    }
    public function logoutAsAdmin($vendor)
    {
        return $this;
    }
    public function loginAdminVendor($user)
    {
        return $this;
    }
    public function logoutAdminVendor($vendor)
    {
        return $this;
    }
    public function redirectAdminVendor()
    {
        return false;
    }
    public function hookBackendSessionStart($session)
    {
        return $this;
    }
}

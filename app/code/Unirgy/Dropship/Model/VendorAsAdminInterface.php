<?php

namespace Unirgy\Dropship\Model;

interface VendorAsAdminInterface
{
    public function getVendorId();
    public function setVendorId($vendorId);
    public function loginAsAdmin($vendor);
    public function logoutAsAdmin($vendor);
    public function loginAdminVendor($user);
    public function logoutAdminVendor($vendor);
    public function redirectAdminVendor();
    public function hookBackendSessionStart($session);
}

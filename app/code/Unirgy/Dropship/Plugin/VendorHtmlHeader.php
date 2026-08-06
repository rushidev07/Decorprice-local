<?php

namespace Unirgy\Dropship\Plugin;

class VendorHtmlHeader
{
    /**
     * @var \Unirgy\Dropship\Helper\Data
     */
    protected $_hlp;

    public function __construct(
        \Unirgy\Dropship\Helper\Data $udropshipHelper
    ) {
        $this->_hlp = $udropshipHelper;
    }
    public function afterGetVendorMenu(
        \Unirgy\Dropship\Block\Vendor\HtmlHeader $subject,
        $result
    ) {
        return $result;
    }
}
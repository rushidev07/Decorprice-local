<?php

namespace Unirgy\Dropship\Plugin;

use \Magento\Quote\Model\Quote\Address\Item as AddressItem;

class ShippingCarrierAbstract
{
    protected $_hlp;
    public function __construct(
        \Unirgy\Dropship\Helper\Data $udropshipHelper
    ) {
        $this->_hlp = $udropshipHelper;
    }
    public function aroundProccessAdditionalValidation(
        \Magento\Shipping\Model\Carrier\AbstractCarrierInterface $subject,
        \Closure $next,
        \Magento\Framework\DataObject $request
    ) {
    
        $result = $next($request);
        $address = null;
        $items = $request->getAllItems();
        if (is_array($items) || $items instanceof \Traversable) {
            foreach ($items as $item) {
                if ($item->getAddress()) {
                    $address = $item->getAddress();
                }
                break;
            }
        }
        if ($address && $address->getQuote()
            && !$subject instanceof \Unirgy\Dropship\Model\Carrier
            && !$subject instanceof \Unirgy\DropshipSplit\Model\Carrier
        ) {
            $vendors = [];
            foreach ($items as $item) {
                $_item = $item;
                if ($item instanceof AddressItem && $item->getQuoteItem()) {
                    $_item = $item->getQuoteItem();
                }
                $vId = $_item->getUdropshipVendor();
                if ($vId) {
                    $vendors[$vId] =  $this->_hlp->getVendor($vId);
                }
            }
            $hasAddressError = false;
            foreach ($vendors as $vendor) {
                $countryId = $address->getCountryId();
                if ($this->_hlp->returnCountryOnlyWhenHaveZip && !$address->getPostcode()) {
                    $countryId = null;
                }
                if (!$vendor->isCountryMatch($countryId)
                    || !$vendor->isAddressMatch($address)
                    || !$vendor->isZipcodeMatch($address->getPostcode())
                ) {
                    $hasAddressError = true;
                    break;
                }
            }
            if ($hasAddressError) {
                $result = false;
            }
        }
        return $result;
    }
}

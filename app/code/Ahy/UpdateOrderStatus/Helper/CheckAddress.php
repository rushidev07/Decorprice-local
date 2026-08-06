<?php
/**
 * Company Ahy Consulting
 * @Developer   Ameer Potrik
 * @email    ameer.potrik@ahytech.com
 */

namespace Ahy\UpdateOrderStatus\Helper;

use Psr\Log\LoggerInterface;


class CheckAddress
{
	
	protected $_logger;
	
	public function __construct(
		LoggerInterface $logger
    ) {
		$this->_logger = $logger;
    }
   
	public function isBillingAddressSameAsShippingAddress($order){
		if (isset($order)) {
			$billingAddress = $order->getBillingAddress();
			$billingRegionId = $billingAddress->getRegionId();
			$billingPostalCode = $billingAddress->getPostcode();
			$billingName = $billingAddress->getFirstname().' '.$billingAddress->getLastname();
			$billingCity = $billingAddress->getCity();
			$billingStreet = $billingAddress->getStreet();
			$billingCountry = $billingAddress->getCountryId();

			$shippingAddress = $order->getShippingAddress();
			$shippingRegionId = $shippingAddress->getRegionId();
			$shippingPostalCode = $shippingAddress->getPostcode();
			$shippingName = $shippingAddress->getFirstname().' '.$shippingAddress->getLastname();
			$shippingCity = $shippingAddress->getCity();
			$shippingStreet = $shippingAddress->getStreet();
			$shippingCountry = $shippingAddress->getCountryId();
			//var_dump($shippingCountry);exit;
			
			$isStreetSame = false;
			if(is_array($billingStreet) && is_array($shippingStreet)){
				$billingStreet1 = isset($billingStreet[0]) ? $billingStreet[0] : '';
				$billingStreet2 = isset($billingStreet[1]) ? $billingStreet[1] : '';
				$billingStreet3 = isset($billingStreet[2]) ? $billingStreet[2] : '';
				$shippingStreet1 = isset($shippingStreet[0]) ? $shippingStreet[0] : '';
				$shippingStreet2 = isset($shippingStreet[1]) ? $shippingStreet[1] : '';
				$shippingStreet3 = isset($shippingStreet[2]) ? $shippingStreet[2] : '';
				if($billingStreet1 == $shippingStreet1 && $billingStreet2 == $shippingStreet2 && $billingStreet3 == $shippingStreet3){
					$isStreetSame = true;
				} 
			} else{
				if($billingStreet == $shippingStreet){
					$isStreetSame = true;
				}
			}
			
			if(
				//$billingRegionId !== $shippingRegionId ||
				$billingName !== $shippingName ||
				$billingCity !== $shippingCity ||
				!$isStreetSame ||
				$billingCountry !== $shippingCountry ||
				$billingPostalCode !== $shippingPostalCode
				)
			{
				return false;
			} else {
				return true;
			}
		}
	}
}


?>
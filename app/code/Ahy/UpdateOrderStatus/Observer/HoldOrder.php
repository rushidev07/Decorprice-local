<?php

/**
 * Copyright   2020 Ahy Consulting (https://www.ahyconsulting.com).
 * Author      Ameer Potrik <ameer.potrik@ahytech.com>
 */


namespace Ahy\UpdateOrderStatus\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Ahy\UpdateOrderStatus\Helper\CheckAddress as CheckAddress;

class HoldOrder implements ObserverInterface
{
	protected $_ahyHelper;
	
	public function __construct(
        CheckAddress $checkAddress
    ) {
        $this->_ahyHelper = $checkAddress;
    }
	
	public function execute(Observer $observer) {
		$order = $observer->getEvent()->getOrder();
		$paymentMethodTitle = $order->getPayment()->getMethodInstance()->getTitle();
		
		$isBillingSameAsShipping = $this->_ahyHelper->isBillingAddressSameAsShippingAddress($order);
		//var_dump($isBillingSameAsShipping);exit;
		if(($paymentMethodTitle == 'Credit Card') && !$isBillingSameAsShipping)
		{
			$order->setState(\Magento\Sales\Model\Order::STATE_HOLDED, true);
		    $order->setStatus(\Magento\Sales\Model\Order::STATUS_FRAUD, true);
		    $order->addStatusHistoryComment('Suspected Fraud as Shipping and Billing addresses are different with Credit Card');
			$order->save();
		}
	}
}
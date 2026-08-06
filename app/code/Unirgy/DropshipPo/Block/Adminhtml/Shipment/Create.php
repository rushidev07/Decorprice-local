<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_DropshipPo
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */
 
namespace Unirgy\DropshipPo\Block\Adminhtml\Shipment;

class Create extends \Magento\Shipping\Block\Adminhtml\Create
{
	public function getHeaderText()
    {
        $header = __('New Shipment for PO #%1 [Order #%2]',
        	$this->getShipment()->getUdpo()->getIncrementId(), 
        	$this->getShipment()->getOrder()->getRealOrderId()
        );
        return $header;
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/*/view', ['udpo_id'=>$this->getShipment()->getUdpoId()]);
    }
}
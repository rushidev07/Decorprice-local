<?php

namespace Unirgy\DropshipPo\Block\Adminhtml\Shipment;

class CreateForm extends \Magento\Shipping\Block\Adminhtml\Create\Form
{
	public function getSaveUrl()
    {
        return $this->getUrl('*/*/saveShipment', ['udpo_id' => $this->getShipment()->getUdpoId()]);
    }
}
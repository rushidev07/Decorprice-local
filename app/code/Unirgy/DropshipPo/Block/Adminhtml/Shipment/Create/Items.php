<?php

namespace Unirgy\DropshipPo\Block\Adminhtml\Shipment\Create;

class Items extends \Magento\Shipping\Block\Adminhtml\Create\Items
{
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
        $this->getChildBlock('submit_button')->setData('onclick', 'disableElements(\'submit-button\');$(\'edit_form\').submit()');
        return $this;
    }
}
<?php

namespace Unirgy\DropshipPo\Controller\Adminhtml\Order\Po;

class Start extends AbstractPo
{
    public function execute()
    {
        $this->_redirect('*/*/new', ['order_id'=>$this->getRequest()->getParam('order_id')]);
    }
}

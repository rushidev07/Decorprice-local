<?php

namespace Unirgy\DropshipPo\Controller\Adminhtml\Order\Po;

class UdposTab extends AbstractPo
{
    public function execute()
    {
        $this->__initOrder();
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}

<?php

namespace Unirgy\DropshipPo\Controller\Adminhtml\Po;



class View extends AbstractPo
{
    public function execute()
    {
        if ($shipmentId = $this->getRequest()->getParam('udpo_id')) {
            $this->_forward('view', 'order_po', null, ['come_from'=>'udpo']);
        } else {
            $this->_forward('noRoute');
        }
    }
}

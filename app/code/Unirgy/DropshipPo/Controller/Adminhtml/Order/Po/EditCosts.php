<?php

namespace Unirgy\DropshipPo\Controller\Adminhtml\Order\Po;



class EditCosts extends AbstractPo
{
    public function execute()
    {
        if ($po = $this->_initPo(false)) {
            $resultPage = $this->_initAction();
            $resultPage->addBreadcrumb(
                "#" . $po->getIncrementId(),
                "#" . $po->getIncrementId()
            );
            $resultPage->getConfig()->getTitle()->prepend(
                "#" . $po->getIncrementId()
            );
            return $resultPage;
        } else {
            $this->_redirect('sales/order/view', ['order_id'=>$this->getRequest()->getParam('order_id')]);
        }
    }
}

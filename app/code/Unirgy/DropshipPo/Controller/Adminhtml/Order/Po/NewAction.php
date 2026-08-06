<?php

namespace Unirgy\DropshipPo\Controller\Adminhtml\Order\Po;



class NewAction extends AbstractPo
{
    public function execute()
    {
        if ($order = $this->_initOrder()) {

            $resultPage = $this->_initAction();
            $resultPage->addBreadcrumb(
                __('New Purchase Order'),
                __('New Purchase Order')
            );
            $resultPage->getConfig()->getTitle()->prepend(
                __('New Purchase Order')
            );

            return $resultPage;
        } else {
            return $this->_redirect('sales/order/view', ['order_id'=>$this->getRequest()->getParam('order_id')]);
        }
    }
}

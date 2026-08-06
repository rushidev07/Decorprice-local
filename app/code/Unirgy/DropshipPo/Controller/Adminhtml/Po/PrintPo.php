<?php

namespace Unirgy\DropshipPo\Controller\Adminhtml\Po;

class PrintPo extends AbstractPo
{
    public function execute()
    {
        if ($udoId = $this->getRequest()->getParam('udpo_id')) { 
            if (($udpo = $this->_hlp->createObj('\Unirgy\DropshipPo\Model\Po')->load($udoId)) && $udpo->getId()) {
                $pdf = $this->_prepareUdpoPdf([$udpo]);
                $this->_hlp->sendDownload('purchase_order_'.$this->_hlp->now().'.pdf', $pdf->render(), 'application/pdf');
            }
        }
        else {
            $this->_forward('noRoute');
        }
    }
}

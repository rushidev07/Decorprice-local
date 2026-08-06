<?php

namespace Unirgy\DropshipPo\Controller\Adminhtml\Po;

class PdfUdpos extends AbstractPo
{
    public function execute(){
        $udpoIds = $this->getRequest()->getPost('udpo_ids');
        if (!empty($udpoIds)) {
            $udpos = $this->_hlp->createObj('\Unirgy\DropshipPo\Model\ResourceModel\Po\Collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', ['in' => $udpoIds])
                ->load();
            $pdf = $this->_prepareUdpoPdf($udpos);

            $this->_hlp->sendDownload('purchase_order_'.$this->_hlp->now().'.pdf', $pdf->render(), 'application/pdf');
        }
        return $this->_redirect('*/*/');
    }
}

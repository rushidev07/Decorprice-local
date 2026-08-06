<?php

namespace Unirgy\DropshipPo\Controller\Vendor;

use Magento\Framework\App\ObjectManager;

class UdpoLabelBatchCreate extends AbstractVendor
{
    public function execute()
    {
    	$result = [];
        try {
            $labelBatch = $this->_labelBatchFactory->create()
                ->setVendor($this->_hlp->session()->getVendor());
            $labelBatch->initSave(0);
            if (!$labelBatch->getId()) {
                throw new \Exception('Label batch not found');
            }
            $result = [
                'id' => $labelBatch->getId()
            ];

        } catch (\Exception $e) {
            $this->_hlp->createObj('\Psr\Log\LoggerInterface')->error($e);
            $result = [
                'error'=>true,
                'message'=>$e->getMessage()
            ];
        }
        return $this->_resultRawFactory->create()->setContents(
            $this->_hlp->jsonEncode($result)
        );
    }
}

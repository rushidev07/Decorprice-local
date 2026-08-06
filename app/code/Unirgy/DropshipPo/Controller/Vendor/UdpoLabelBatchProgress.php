<?php

namespace Unirgy\DropshipPo\Controller\Vendor;

use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Unirgy\Dropship\Model\Source;

class UdpoLabelBatchProgress extends AbstractVendor
{
    public function execute()
    {
    	$result = [];
        try {
            //$id = $this->getRequest()->getParam('id');
            $batchId = $this->getRequest()->getParam('batch_id');
            /** @var \Unirgy\Dropship\Model\Label\Batch $batch */
            $batch = $this->_hlp->createObj('\Unirgy\Dropship\Model\Label\Batch')->load($batchId);
            if (!$batch->getId()) {
                throw new \Exception('Label batch not found');
            }
            $result = [
                'id' => $batch->getId(),
                'status' => $batch->getStatus(),
                'items_count' => $batch->getItemsCount(),
                'items_processed' => $batch->getItemsProcessed(),
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

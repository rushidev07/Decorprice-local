<?php

namespace Unirgy\DropshipPo\Controller\Vendor;

use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Unirgy\Dropship\Model\Source;

class UdpoLabelBatch extends AbstractVendor
{
    public function execute()
    {
        $isBackground = false;
    	$result = [];
        try {
            $batchId = $this->getRequest()->getParam('batch_id');
            $udpoHlp = $this->_poHlp;
            $udpos = $this->getVendorPoCollection();
            if (!$udpos->getSize()) {
                throw new \Exception('No purchase orders found for these criteria');
            }
            /** @var \Unirgy\Dropship\Model\Label\Batch $labelBatch */
            $labelBatch = $this->_labelBatchFactory->create();
            if (!$batchId) {
                $labelBatch->setVendor($this->_hlp->session()->getVendor());
                $labelBatch->initSave(0);
            } else {
                $labelBatch->load($batchId);
            }
            $labelBatch->updatePoIds(implode(',',$udpos->getAllIds()));

            if ($this->getRequest()->getParam('use_json_response')) {

                $labelBatch->setCloseSessionFlag(true);
                session_write_close();
                ignore_user_abort(true);
                set_time_limit(0);
                ob_implicit_flush();

                $disabledFuncs = explode(',', str_replace(' ', ',', ini_get('disable_functions')));
                if ($udpos->getSize()>5 && !in_array('exec', $disabledFuncs)) {
                    /** @var \Magento\Framework\Shell $shell */
                    $shell = $this->_hlp->getObj('shellBackground');
                    $isBackground = true;
                    $shell->execute($this->_dirList()->getRoot().'/bin/magento unirgy:dropship:label-batch %s', [$labelBatch->getId()]);
                } else {
                    $labelBatch->processPos();
                }

            } else {
                $labelBatch->processPos();
                return $labelBatch->prepareLabelsDownloadResponse();
            }

        } catch (\Exception $e) {
            $this->_hlp->createObj('\Psr\Log\LoggerInterface')->error($e);
        	if ($this->getRequest()->getParam('use_json_response')) {
        		$result = [
        			'error'=>true,
        			'message'=>$e->getMessage()
        		];
        	} else {
                $this->messageManager->addError(__($e->getMessage()));
        	}
        }
        if (!$isBackground && $labelBatch && $labelBatch->getId() && !$labelBatch->getShipmentCnt()) {
            $labelBatch->delete();
        }
    	if ($this->getRequest()->getParam('use_json_response')) {
        	return $this->_resultRawFactory->create()->setContents(
        		$this->_hlp->jsonEncode($result)
        	);
        } else {
        	$this->_redirect('udpo/vendor/', ['_current'=>true, '_query'=>['submit_action'=>'']]);
        }
    }
    /**
     * @return \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected function _dirList()
    {
        return $this->_hlp->getObj('\Magento\Framework\App\Filesystem\DirectoryList');
    }
}

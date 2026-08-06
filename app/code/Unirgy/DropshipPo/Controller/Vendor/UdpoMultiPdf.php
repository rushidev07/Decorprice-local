<?php

namespace Unirgy\DropshipPo\Controller\Vendor;

use Magento\Store\Model\StoreManagerInterface;

class UdpoMultiPdf extends AbstractVendor
{
    public function execute()
    {
    	$result = [];
        try {
            $udpos = $this->getVendorPoCollection();
            if (!$udpos->getSize()) {
                throw new \Exception('No purchase orders found for these criteria');
            }

            return $this->_preparePoMultiPdf($udpos);

        } catch (\Exception $e) {
        if ($this->getRequest()->getParam('use_json_response')) {
        		$result = [
        			'error'=>true,
        			'message'=>$e->getMessage()
        		];
        	} else {
            $this->messageManager->addError(__($e->getMessage()));
        	}
        }
        if ($this->getRequest()->getParam('use_json_response')) {
        	$this->_resultRawFactory->create()->setContents(
        		$this->_hlp->jsonEncode($result)
        	);
        } else {
        	$this->_redirect('udpo/vendor/', ['_current'=>true, '_query'=>['submit_action'=>'']]);
        }
    }
}

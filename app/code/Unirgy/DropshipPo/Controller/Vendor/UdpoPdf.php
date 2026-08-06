<?php

namespace Unirgy\DropshipPo\Controller\Vendor;

use Magento\Store\Model\StoreManagerInterface;

class UdpoPdf extends AbstractVendor
{
    public function execute()
    {
        try {
            $id = $this->getRequest()->getParam('udpo_id');
            if (!$id) {
                throw new \Exception('Invalid purchase order ID is supplied');
            }

            $udpos = $this->_poFactory->create()->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', $id)
                ->load();
            if (!$udpos->getSize()) {
                throw new \Exception('No purchase order found with supplied IDs');
            }

            return $this->_preparePoMultiPdf($udpos);

        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $this->_redirect('udpo/vendor/');
    }
}

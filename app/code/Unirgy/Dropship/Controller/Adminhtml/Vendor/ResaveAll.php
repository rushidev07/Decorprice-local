<?php

namespace Unirgy\Dropship\Controller\Adminhtml\Vendor;

use \Magento\Framework\Controller\ResultFactory;

class ResaveAll extends AbstractVendor
{
    public function execute()
    {
        ob_implicit_flush();
        $resultHtml = 'START. ';
        $vendors = $this->_hlp->createObj('\Unirgy\Dropship\Model\Vendor')->getCollection();
        foreach ($vendors as $vendor) {
            $resultHtml .= $vendor->getId().', ';
            $vendor->afterLoad();
            $vendor->save();
        }
        $resultHtml .= 'DONE.';
        return $this->resultFactory->create(ResultFactory::TYPE_RAW)->setContents($resultHtml);
    }
}

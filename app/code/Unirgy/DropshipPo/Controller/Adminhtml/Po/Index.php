<?php

namespace Unirgy\DropshipPo\Controller\Adminhtml\Po;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\LayoutFactory;
use Unirgy\DropshipPo\Helper\Data as HelperData;

class Index extends AbstractPo
{
    public function execute()
    {
        $resultPage = $this->_initAction();
        return $resultPage;
    }
}

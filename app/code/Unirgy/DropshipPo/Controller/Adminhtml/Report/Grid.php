<?php

namespace Unirgy\DropshipPo\Controller\Adminhtml\Report;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\Model\View\Result\Page;

class Grid extends AbstractReport
{
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}

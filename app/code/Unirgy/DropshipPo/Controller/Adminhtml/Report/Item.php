<?php

namespace Unirgy\DropshipPo\Controller\Adminhtml\Report;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\LayoutFactory;
use Unirgy\Dropship\Helper\Data as HelperData;

class Item extends AbstractReport
{
    public function execute()
    {
        $page = $this->_initAction();
        $page->addBreadcrumb(__('Advanced PO Item Details'), __('Advanced PO Item Details'));
        $title = $page->getConfig()->getTitle();
        $title->prepend(__('Advanced Item PO Details'));
        return $page->addContent($page->getLayout()->createBlock('Unirgy\DropshipPo\Block\Adminhtml\ReportItem'));
    }
}

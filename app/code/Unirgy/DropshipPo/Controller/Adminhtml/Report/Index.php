<?php

namespace Unirgy\DropshipPo\Controller\Adminhtml\Report;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\LayoutFactory;
use Unirgy\Dropship\Helper\Data as HelperData;

class Index extends AbstractReport
{
    public function execute()
    {
        $page = $this->_initAction();
        $page->addBreadcrumb(__('Advanced PO Details'), __('Advanced PO Details'));
        $title = $page->getConfig()->getTitle();
        $title->prepend(__('Advanced PO Details'));
        return $page->addContent($page->getLayout()->createBlock('Unirgy\DropshipPo\Block\Adminhtml\Report'));
    }
}

<?php

namespace Unirgy\DropshipPo\Controller\Adminhtml\Po;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\LayoutFactory;
use Unirgy\DropshipPo\Helper\Data as HelperData;

class ExportXml extends AbstractPo
{
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'po.xml';
        $content = $this->_view->getLayout()->getBlock('udpo.po.grid');

        return $this->_fileFactory->create(
            $fileName,
            $content->getExcelFile($fileName),
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR
        );
    }
}

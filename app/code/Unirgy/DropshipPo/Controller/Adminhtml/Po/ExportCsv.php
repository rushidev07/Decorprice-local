<?php

namespace Unirgy\DropshipPo\Controller\Adminhtml\Po;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\LayoutFactory;
use Unirgy\DropshipPo\Helper\Data as HelperData;

class ExportCsv extends AbstractPo
{
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'po.csv';
        $content = $this->_view->getLayout()->getBlock('udpo.po.grid');

        return $this->_fileFactory->create(
            $fileName,
            $content->getCsvFile($fileName),
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR
        );
    }
}

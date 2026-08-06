<?php

namespace Unirgy\DropshipPo\Controller\Adminhtml\Report;

class ItemExportXml extends AbstractReport
{
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'advancedpo_item_report.xml';
        $content = $this->_view->getLayout()->getBlock('udpo_item_reportgrid');

        return $this->_fileFactory->create(
            $fileName,
            $content->getExcelFile($fileName),
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR
        );
    }
}

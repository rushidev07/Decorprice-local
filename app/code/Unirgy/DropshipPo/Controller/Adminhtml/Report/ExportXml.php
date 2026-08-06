<?php

namespace Unirgy\DropshipPo\Controller\Adminhtml\Report;

class ExportXml extends AbstractReport
{
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'advancedpo_report.xml';
        $content = $this->_view->getLayout()->getBlock('udpo_reportgrid');

        return $this->_fileFactory->create(
            $fileName,
            $content->getExcelFile($fileName),
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR
        );
    }
}

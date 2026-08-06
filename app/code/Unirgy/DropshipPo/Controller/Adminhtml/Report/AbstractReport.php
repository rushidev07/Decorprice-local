<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_DropshipPo
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\DropshipPo\Controller\Adminhtml\Report;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\Model\View\Result\Page;

abstract class AbstractReport extends Action
{
    protected $_hlp;
    protected $_poHlp;
    protected $_fileFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Unirgy\Dropship\Helper\Data $udropshipHelper,
        \Unirgy\DropshipPo\Helper\Data $udpoHelper,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    )
    {
        $this->_hlp = $udropshipHelper;
        $this->_poHlp = $udpoHelper;
        $this->_fileFactory = $fileFactory;

        parent::__construct($context);
    }
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'index':
            case 'grid':
            case 'exportCsv':
            case 'exportXml':
                return $this->_authorization->isAllowed('Unirgy_DropshipPo::report_udpo');
            case 'item':
            case 'itemGrid':
            case 'itemExportCsv':
            case 'itemExportXml':
                return $this->_authorization->isAllowed('Unirgy_DropshipPo::report_udpo_item');
        }
        return parent::_isAllowed();
    }

    protected function _initAction()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu("Unirgy_DropshipPo::report_udpo")
            ->addBreadcrumb(__('Report'), __('Report'))
            ->addBreadcrumb(__('Dropship'), __('Dropship'));
        $title = $resultPage->getConfig()->getTitle();
        $title->prepend(__('Report'));
        $title->prepend(__('Dropship'));
        return $resultPage;
    }

}

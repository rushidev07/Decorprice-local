<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Controller\Adminhtml\Product;

class Grid extends \Aheadworks\Pquestion\Controller\Adminhtml\Product
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Product grid for AJAX request
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Aheadworks_Pquestion::all');
        $resultPage->getConfig()->getTitle()->prepend(__('Marketing'));
        $resultPage->getConfig()->getTitle()->prepend(__('Product Questions'));
        $resultPage->getConfig()->getTitle()->prepend(__('By Product'));
        $resultPage->getLayout()->getBlock('aw_pq.menu')->setCurrentItemKey(
            \Aheadworks\Pquestion\Block\Adminhtml\Question\MENU::ITEM_BY_PRODUCT
        );
        return $resultPage;
    }
}

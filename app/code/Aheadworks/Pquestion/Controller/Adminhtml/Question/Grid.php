<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Controller\Adminhtml\Question;

class Grid extends \Aheadworks\Pquestion\Controller\Adminhtml\Question
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context, $filterManager);
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
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Questions'));
        $resultPage->getLayout()->getBlock('aw_pq.menu')->setCurrentItemKey(
            \Aheadworks\Pquestion\Block\Adminhtml\Question\Menu::ITEM_PRODUCTQUESTION
        );
        return $resultPage;
    }
}

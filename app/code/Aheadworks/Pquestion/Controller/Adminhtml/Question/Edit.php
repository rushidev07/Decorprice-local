<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Controller\Adminhtml\Question;

class Edit extends \Aheadworks\Pquestion\Controller\Adminhtml\Question
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
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$question = $this->_initQuestion()) {
            $this->messageManager->addSuccess(__('Please choose the product.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('newquestion/*/');
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $resultPage->setActiveMenu('Aheadworks_Pquestion::all');
        $resultPage->getConfig()->getTitle()->prepend(__('Marketing'));
        $resultPage->getConfig()->getTitle()->prepend(__('Product Questions'));
        $resultPage->getLayout()->getBlock('aw_pq.menu')->setCurrentItemKey(
            \Aheadworks\Pquestion\Block\Adminhtml\Question\MENU::ITEM_PRODUCTQUESTION
        );

        $breadcrumbTitle = $breadcrumbLabel = __('New Question');
        if ($question->getId()) {
            $breadcrumbTitle = $breadcrumbLabel = __('Manage Question');
        }
        $resultPage->getConfig()->getTitle()->prepend($breadcrumbTitle);
        $resultPage->addBreadcrumb($breadcrumbLabel, $breadcrumbTitle);

        if ($question->getSharingType()
            == \Aheadworks\Pquestion\Model\Source\Question\Sharing\Type::SPECIFIED_PRODUCTS_VALUE
        ) {
            $this->_objectManager->get(\Magento\Framework\Registry::class)
                ->register(
                    'current_widget_instance',
                    new \Magento\Framework\DataObject(['widget_parameters' => $question->getSharingValue()])
                )
            ;
        }
        return $resultPage;
    }
}

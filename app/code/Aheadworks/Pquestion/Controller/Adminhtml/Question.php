<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Controller\Adminhtml;

use Magento\Backend\App\Action;

abstract class Question extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $_filterManager;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Filter\FilterManager $filterManager
    ) {
        parent::__construct($context);
        $this->_filterManager = $filterManager;
    }

    /**
     * @return mixed
     */
    protected function _initQuestion()
    {
        $helper = $this->_objectManager->create(\Aheadworks\Pquestion\Helper\Data::class);
        $questionModel = $this->_objectManager->create(\Aheadworks\Pquestion\Model\Question::class);

        $questionId  = (int) $this->getRequest()->getParam('id', 0);
        if ($questionId) {
            $questionModel->load($questionId);
        } else {
            $adminSessionUser = $this->_objectManager->get(\Magento\Backend\Model\Auth\Session::class)->getUser();
            $questionModel
                ->setIsAdmin(true)
                ->setAuthorName(
                    trim($adminSessionUser->getFirstname() . ' ' . $adminSessionUser->getLastname())
                )
                ->setAuthorEmail($adminSessionUser->getEmail())
                ->setCustomerId(0)
                ->setSharingType(\Aheadworks\Pquestion\Model\Source\Question\Sharing\Type::ALL_PRODUCTS_VALUE)
                ->setProductId(0)
                ->setSharingValue([])
                ->setHelpfulness(0)
                ->setVisibility(\Aheadworks\Pquestion\Model\Source\Question\Visibility::PUBLIC_VALUE)
                ->setShowInStoreIds(0) //All Store Views
            ;
        }

        $productModel = $this->_objectManager->create(\Magento\Catalog\Model\Product::class);
        $productModel = $productModel->load($questionModel->getProductId());
        $questionModel
            ->setProductName($productModel->getName())
            ->setStoreLabel($helper->getStoreLabel($questionModel->getStoreId()))
        ;

        $formData = $this->_objectManager->get(\Magento\Backend\Model\Session::class)->getFormData(true);
        if (!empty($formData)) {
            $questionModel->addData($formData);
        }
        $this->_objectManager->get(\Magento\Framework\Registry::class)
            ->register('current_question', $questionModel)
        ;
        return $questionModel;
    }

    /**
     * Check if admin has permissions to visit related pages
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Aheadworks_Pquestion::questions');
    }

    /**
     * @return string
     */
    protected function getQuestionEditLink()
    {
        $questionModel = $this->_objectManager->get(\Magento\Framework\Registry::class)
            ->registry('current_question')
        ;
        $url = $this->getUrl('productquestion/question/edit', ['id' => $questionModel->getId()]);
        $title = $this->_filterManager->truncate(
            htmlspecialchars($questionModel->getContent(), ENT_COMPAT, 'UTF-8', false),
            ['length' => 40, 'etc' => '...', 'remainder' => '', 'breakWords' => true]
        );
        return "<a href='{$url}' target='_blank'>{$title}</a>";
    }
}

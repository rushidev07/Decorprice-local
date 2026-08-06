<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Controller\Question;

class QList extends \Aheadworks\Pquestion\Controller\Question
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = [
            'success'  => true,
            'messages' => [],
        ];
        $this->_product->load($this->getRequest()->getParam('product'));
        $this->_objectManager->get(\Magento\Framework\Registry::class)
            ->register('product', $this->_product)
        ;
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $result['block'] = $resultPage->getLayout()->getBlock('aw_pq_question_list')->toHtml();
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        return $resultJson->setData($result);
    }
}

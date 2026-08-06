<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Controller\Adminhtml\Question;

class Delete extends \Aheadworks\Pquestion\Controller\Adminhtml\Question
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $questionModel = $this->_initQuestion();
        try {
            $questionModel->delete();
            $title = $this->_filterManager->truncate(
                htmlspecialchars($questionModel->getContent(), ENT_COMPAT, 'UTF-8', false),
                ['length' => 40, 'etc' => '...', 'remainder' => '', 'breakWords' => true]
            );
            $this->messageManager->addSuccess(
                __('Question "%1" has been deleted successfully.', $title)
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/grid');
    }
}

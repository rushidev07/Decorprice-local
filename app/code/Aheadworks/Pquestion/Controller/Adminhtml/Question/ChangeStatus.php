<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Controller\Adminhtml\Question;

class ChangeStatus extends \Aheadworks\Pquestion\Controller\Adminhtml\Question
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $redirectResult = $this->resultRedirectFactory->create();
        $questionModel = $this->_initQuestion();
        $status = $this->getRequest()->getParam('status_id', null);
        if (null === $status) {
            $this->messageManager->addError(__('Invalid status value.'));
            return $redirectResult->setRefererUrl();
        }
        try {
            $questionModel->setStatus($status)->save();
            $this->messageManager->addSuccess(
                __('Question %1 status has been changed successfully.', $this->getQuestionEditLink())
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        return $redirectResult->setRefererUrl();
    }
}

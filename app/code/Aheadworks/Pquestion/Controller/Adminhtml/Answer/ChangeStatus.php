<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Controller\Adminhtml\Answer;

class ChangeStatus extends \Aheadworks\Pquestion\Controller\Adminhtml\Answer
{
    /**
     * @return $this
     */
    public function execute()
    {
        $redirectResult = $this->resultRedirectFactory->create();
        $answerModel = $this->_initAnswer();
        $status = $this->getRequest()->getParam('status_id', null);
        if (null === $status) {
            $this->messageManager->addError(__('Invalid status value.'));
            return $redirectResult->setRefererUrl();
        }
        try {
            $answerModel->setStatus($status)->save();
            $this->messageManager->addSuccess(__('Answer status has been changed successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        return $redirectResult->setRefererUrl();
    }
}

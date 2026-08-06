<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Controller\Adminhtml\Answer;

class Delete extends \Aheadworks\Pquestion\Controller\Adminhtml\Answer
{
    /**
     * @return $this
     */
    public function execute()
    {
        $questionModel = $this->_initAnswer();
        try {
            $questionModel->delete();
            $this->messageManager->addSuccess(__('Answer has been deleted successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setRefererUrl();
    }
}

<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Controller\Adminhtml\Answer;

class Save extends \Aheadworks\Pquestion\Controller\Adminhtml\Answer
{
    /**
     * @return $this
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $formData = $this->getRequest()->getParam('data', []);
        if (!$formData) {
            return $resultRedirect->setRefererUrl();
        }
        $answerModel = $this->_initAnswer();
        if (null === $answerModel->getId()) {
            $formData = $formData['new_answer'];
        } else {
            $answerId = $this->getRequest()->getParam('id', null);
            $formData = $formData['answer'][$answerId];
        }

        $questionId = $answerModel->getQuestionId();
        if (null === $questionId) {
            $questionId = $formData['question_id'];
        }

        $questionModel = $this->_objectManager->create(\Aheadworks\Pquestion\Model\Question::class);
        $questionModel->load($questionId);
        try {
            $answerModel->addData($formData);
            $answerModel->setStatus(
                \Aheadworks\Pquestion\Model\Source\Question\Status::APPROVED_VALUE
            );
            if (null === $answerModel->getId()) {
                $questionModel->addAnswer($answerModel);
            } else {
                $answerModel->save();
            }
            $questionModel->setStatus(\Aheadworks\Pquestion\Model\Source\Question\Status::APPROVED_VALUE)->save();
            $this->messageManager->addSuccess(__('Answer saved successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $resultRedirect->setRefererUrl();
        }
        return $resultRedirect->setPath('*/question/index');
    }
}

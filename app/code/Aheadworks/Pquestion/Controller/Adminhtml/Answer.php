<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Controller\Adminhtml;

use Magento\Backend\App\Action;

abstract class Answer extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_dateTime;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        parent::__construct($context);
        $this->_dateTime = $dateTime;
    }

    /**
     * @return \Aheadworks\Pquestion\Model\Answer
     */
    protected function _initAnswer()
    {
        /** @var \Aheadworks\Pquestion\Model\Answer $answerModel */
        $answerModel = $this->_objectManager->create(\Aheadworks\Pquestion\Model\Answer::class);
        $answerId  = (int) $this->getRequest()->getParam('id', 0);
        if ($answerId) {
            $answerModel->load($answerId);
        } else {
            $adminSessionUser = $this->_objectManager->get(\Magento\Backend\Model\Auth\Session::class)->getUser();
            $answerModel->setIsAdmin(true);

            $currentDate = $this->_dateTime->gmtDate();

            $answerModel
                ->setAuthorName(
                    trim($adminSessionUser->getFirstname() . ' ' . $adminSessionUser->getLastname())
                )
                ->setStatus(\Aheadworks\Pquestion\Model\Source\Question\Status::APPROVED_VALUE)
                ->setCreatedAt($currentDate)
                ->setAuthorEmail($adminSessionUser->getEmail())
                ->setCustomerId(0)
                ->setHelpfulness(0)
            ;
        }
        $this->_objectManager->get(\Magento\Framework\Registry::class)
            ->register('current_answer', $answerModel, true)
        ;
        return $answerModel;
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
}

<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Controller;

/**
 * Class Answer
 * @package Aheadworks\Pquestion\Controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Answer extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_coreDate;

    /**
     * @var \Aheadworks\Pquestion\Helper\Config
     */
    protected $_configHelper;

    /**
     * @var \Aheadworks\Pquestion\Model\Question
     */
    protected $_question;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_session;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_dateTime;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param \Aheadworks\Pquestion\Helper\Config $configHelper
     * @param \Aheadworks\Pquestion\Model\Question $question
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Aheadworks\Pquestion\Helper\Config $configHelper,
        \Aheadworks\Pquestion\Model\Question $question,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        parent::__construct($context);
        $this->_customerSession = $customerSession;
        $this->_customer = $customer;
        $this->_coreDate = $coreDate;
        $this->_configHelper = $configHelper;
        $this->_question = $question;
        $this->_session = $session;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_dateTime = $dateTime;
    }

    /**
     * @return \Aheadworks\Pquestion\Model\Answer
     */
    protected function _initAnswer()
    {
        /** @var \Aheadworks\Pquestion\Model\Answer $answerModel */
        $answerModel = $this->_objectManager->create(\Aheadworks\Pquestion\Model\Answer::class);
        $questionId = (int)$this->getRequest()->getParam('question_id', 0);
        $productId = (int)$this->getRequest()->getParam('product_id', 0);
        $content = $this->getRequest()->getParam('content', null);

        if ($this->_customerSession->isLoggedIn()) {
            $this->_customer->load($this->_customerSession->getCustomerId());
            $authorName = $this->getRequest()->getParam('author_name', $this->_customer->getName());
            $authorEmail = $this->_customer->getEmail();
            $customerId = $this->_customer->getId();
        } else {
            $authorName = $this->getRequest()->getParam('author_name', null);
            $authorEmail = $this->getRequest()->getParam('author_email', null);
            $customerId = 0;
        }

        $createdAt = $this->_coreDate->gmtDate();

        $answerModel
            ->setQuestionId($questionId)
            ->setProductId($productId)
            ->setAuthorName($authorName)
            ->setAuthorEmail($authorEmail)
            ->setCustomerId($customerId)
            ->setContent($content)
            ->setStatus(\Aheadworks\Pquestion\Model\Source\Question\Status::PENDING_VALUE)
            ->setHelpfulness(0)
            ->setIsAdmin(0)
            ->setCreatedAt($createdAt)
        ;
        if (!$this->_configHelper->isRequireModerateCustomerAnswer()) {
            $answerModel->setStatus(\Aheadworks\Pquestion\Model\Source\Question\Status::APPROVED_VALUE);
        }

        $this->_validate($answerModel);

        $this->_objectManager->get(\Magento\Framework\Registry::class)
            ->register('current_answer', $answerModel, true)
        ;
        return $answerModel;
    }

    /**
     * Retrieve whether customer can vote
     *
     * @return bool
     */
    protected function _isCustomerCanVoteQuestion()
    {
        return $this->_customerSession->isLoggedIn()
            || $this->_configHelper->isAllowGuestRateHelpfulness()
        ;
    }

    /**
     * @param mixed $answerModel
     *
     * @return void
     * @throws \Exception
     */
    protected function _validate($answerModel)
    {
        $authorName = $answerModel->getAuthorName();
        if (!is_string($authorName) || strlen($authorName) <= 0) {
            throw new \Exception(__("Author name not specified."));
        }

        $authorEmail = $answerModel->getAuthorEmail();
        if (!is_string($authorEmail) || strlen($authorEmail) <= 0) {
            throw new \Exception(__("Author email not specified."));
        }

        $content = $answerModel->getContent();
        if (!is_string($content) || strlen($content) <= 0) {
            throw new \Exception(__("Answer not specified."));
        }

        $questionModel = $this->_question->load($answerModel->getQuestionId());
        if (!$questionModel->getId()) {
            throw new \Exception(__("Question not found."));
        }
    }

    /**
     * Validate Form Key
     *
     * @return bool
     */
    protected function _validateFormKey()
    {
        return $this->_formKeyValidator->validate($this->getRequest());
    }
}

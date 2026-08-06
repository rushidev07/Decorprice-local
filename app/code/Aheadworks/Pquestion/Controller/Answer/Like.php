<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Controller\Answer;

class Like extends \Aheadworks\Pquestion\Controller\Answer
{
    /**
     * @var \Aheadworks\Pquestion\Model\Answer
     */
    protected $_answer;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;

    /**
     * @var \Magento\Customer\Model\Visitor
     */
    protected $_visitor;

    /**
     * @param \Aheadworks\Pquestion\Model\Answer $answer
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Magento\Customer\Model\Visitor $visitor
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param \Aheadworks\Pquestion\Helper\Config $configHelper
     * @param \Aheadworks\Pquestion\Model\Question $question
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Aheadworks\Pquestion\Model\Answer $answer,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Customer\Model\Visitor $visitor,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Aheadworks\Pquestion\Helper\Config $configHelper,
        \Aheadworks\Pquestion\Model\Question $question,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        parent::__construct(
            $customerSession,
            $customer,
            $coreDate,
            $configHelper,
            $question,
            $session,
            $formKeyValidator,
            $context,
            $dateTime
        );
        $this->_answer = $answer;
        $this->_customer = $customer;
        $this->_visitor = $visitor;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $result = [
            'success'  => true,
            'messages' => [],
        ];

        if ($this->_isCustomerCanVoteQuestion()) {
            $answerId = (int)$this->getRequest()->getParam('answer_id', 0);
            $answerModel = $this->_answer->load($answerId);
            if ($answerModel->getId()) {
                if ($this->_customerSession->isLoggedIn()) {
                    $customer = $this->_customer->load($this->_customerSession->getCustomerId());
                } else {
                    $customer = $this->_visitor;
                }
                $value = $this->getRequest()->getParam('value', 1);
                try {
                    $answerModel->addHelpful($customer, $value);
                } catch (\Exception $e) {
                    $result['success'] = false;
                    $result['messages'][] = __($e->getMessage());
                }
            } else {
                $result['success'] = false;
                $result['messages'][] = __("Question not found.");
            }
        } else {
            $result['success'] = false;
            $result['messages'][] = __('Product Questions disabled');
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        return $resultJson->setData($result);
    }
}

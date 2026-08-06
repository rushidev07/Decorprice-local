<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Controller\Question;

use Aheadworks\Pquestion\Controller\Question;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Customer;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Aheadworks\Pquestion\Helper\Config;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\App\Action\Context;
use Aheadworks\Pquestion\Model\QuestionFactory as QuestionModelFactory;
use Aheadworks\Pquestion\Model\Question as QuestionModel;
use Magento\Framework\Registry;
use Magento\Customer\Model\Visitor;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Like
 * @package Aheadworks\Pquestion\Controller\Question
 */
class Like extends Question
{
    /**
     * @var QuestionModel
     */
    protected $_question;

    /**
     * @var Customer
     */
    protected $_customer;

    /**
     * @var Visitor
     */
    protected $_visitor;

    /**
     * @param QuestionModel $question
     * @param Customer $customer
     * @param Visitor $visitor
     * @param Session $customerSession
     * @param DateTime $coreDate
     * @param Config $configHelper
     * @param SessionManagerInterface $session
     * @param StoreManagerInterface $storeManager
     * @param Product $product
     * @param Validator $formKeyValidator
     * @param Context $context
     * @param DateTime $dateTime
     * @param QuestionModelFactory $questionFactory
     * @param Registry $registry
     */
    public function __construct(
        QuestionModel $question,
        Customer $customer,
        Visitor $visitor,
        Session $customerSession,
        DateTime $coreDate,
        Config $configHelper,
        SessionManagerInterface $session,
        StoreManagerInterface $storeManager,
        Product $product,
        Validator $formKeyValidator,
        Context $context,
        DateTime $dateTime,
        QuestionModelFactory $questionFactory,
        Registry $registry
    ) {
        parent::__construct(
            $customerSession,
            $customer,
            $coreDate,
            $configHelper,
            $session,
            $storeManager,
            $product,
            $formKeyValidator,
            $context,
            $dateTime,
            $questionFactory,
            $registry
        );
        $this->_question = $question;
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
            $questionId = (int)$this->getRequest()->getParam('question_id', 0);
            $questionModel = $this->_question->load($questionId);
            if ($questionModel->getId()) {
                if ($this->_customerSession->isLoggedIn()) {
                    $customer = $this->_customer->load($this->_customerSession->getCustomerId());
                } else {
                    $customer = $this->_visitor;
                }
                $value = $this->getRequest()->getParam('value', 1);
                try {
                    $questionModel->addHelpful($customer, $value);
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

        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        return $resultJson->setData($result);
    }
}

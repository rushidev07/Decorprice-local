<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Controller;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Customer;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Aheadworks\Pquestion\Helper\Config;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Aheadworks\Pquestion\Model\Question as QuestionModel;
use Aheadworks\Pquestion\Model\QuestionFactory as QuestionModelFactory;
use Aheadworks\Pquestion\Model\Source\Question\Visibility;
use Aheadworks\Pquestion\Model\Source\Question\Status;
use Aheadworks\Pquestion\Model\Source\Question\Sharing\Type;
use Magento\Framework\Registry;

/**
 * Class Question
 * @package Aheadworks\Pquestion\Controller
 */
abstract class Question extends Action
{
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var Customer
     */
    protected $_customer;

    /**
     * @var DateTime
     */
    protected $_coreDate;

    /**
     * @var Config
     */
    protected $_configHelper;

    /**
     * @var SessionManagerInterface
     */
    protected $_session;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Product
     */
    protected $_product;

    /**
     * @var Validator
     */
    protected $_formKeyValidator;

    /**
     * @var TimezoneInterface
     */
    protected $_dateTime;

    /**
     * @var QuestionModel
     */
    protected $_question;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @param Session $customerSession
     * @param Customer $customer
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
        Session $customerSession,
        Customer $customer,
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
        parent::__construct($context);
        $this->_customerSession = $customerSession;
        $this->_customer = $customer;
        $this->_coreDate = $coreDate;
        $this->_configHelper = $configHelper;
        $this->_session = $session;
        $this->_storeManager = $storeManager;
        $this->_product = $product;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_dateTime = $dateTime;
        $this->_question = $questionFactory->create();
        $this->_registry = $registry;
    }

    /**
     * @return QuestionModel
     */
    protected function _initQuestion()
    {
        $productId = (int)$this->getRequest()->getParam('product_id', 0);
        $content = $this->getRequest()->getParam('content', '');
        $isPrivate = $this->getRequest()->getParam('is_private', false);

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

        $visibility = Visibility::PUBLIC_VALUE;
        if ($isPrivate) {
            $visibility = Visibility::PRIVATE_VALUE;
        }

        $createdAt = $this->_coreDate->gmtDate();

        $this->_question
            ->setAuthorName($authorName)
            ->setAuthorEmail($authorEmail)
            ->setCustomerId($customerId)
            ->setContent($content)
            ->setVisibility($visibility)
            ->setStatus(Status::PENDING_VALUE)
            ->setSharingType(Type::ORIGINAL_PRODUCT_VALUE)
            ->setProductId($productId)
            ->setSharingValue([$productId])
            ->setHelpfulness(0)
            ->setShowInStoreIds($this->_storeManager->getStore()->getId()) //Current Store
            ->setStoreId($this->_storeManager->getStore()->getId())
            ->setCreatedAt($createdAt)
        ;

        $this->_validate($this->_question);
        $this->_registry->register('current_question', $this->_question, true);

        return $this->_question;
    }

    /**
     * @param mixed $questionModel
     *
     * @return void
     * @throws \Exception
     */
    protected function _validate($questionModel)
    {
        $authorName = $questionModel->getAuthorName();
        if (!is_string($authorName) || strlen($authorName) <= 0) {
            throw new \Exception(__("Author name not specified."));
        }

        $authorEmail = $questionModel->getAuthorEmail();
        if (!is_string($authorEmail) || strlen($authorEmail) <= 0) {
            throw new \Exception(__("Author email not specified."));
        }

        $content = $questionModel->getContent();
        if (!is_string($content) || strlen($content) <= 0) {
            throw new \Exception(__("Question not specified."));
        }

        $productModel = $this->_product->load($questionModel->getProductId());
        if (!$productModel->getId()) {
            throw new \Exception(__("Product not found."));
        }
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
     * Validate Form Key
     *
     * @return bool
     */
    protected function _validateFormKey()
    {
        return $this->_formKeyValidator->validate($this->getRequest());
    }
}

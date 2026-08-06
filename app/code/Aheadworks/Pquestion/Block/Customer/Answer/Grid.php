<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Block\Customer\Answer;

use Aheadworks\Pquestion\Model\Answer;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Customer;
use Aheadworks\Pquestion\Model\Source\Question\Status;
use Aheadworks\Pquestion\Model\ResourceModel\Answer\Collection as AnswerCollection;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\Framework\DataObject\IdentityInterface;

class Grid extends Template implements IdentityInterface
{
    /**
     * @var CurrentCustomer
     */
    protected $_currentCustomer;

    /**
     * @var Customer
     */
    protected $_customer;

    /**
     * @var Status
     */
    protected $_sourceQuestionStatus;

    /**
     * @var AnswerCollection
     */
    protected $_answerCollection;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @param Customer $customer
     * @param Status $sourceQuestionStatus
     * @param AnswerCollection $answerCollection
     * @param ProductFactory $productFactory
     * @param CurrentCustomer $currentCustomer
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Customer $customer,
        Status $sourceQuestionStatus,
        AnswerCollection $answerCollection,
        ProductFactory $productFactory,
        CurrentCustomer $currentCustomer,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_currentCustomer = $currentCustomer;
        $this->_customer = $customer;
        $this->_sourceQuestionStatus = $sourceQuestionStatus;
        $this->_answerCollection = $answerCollection;
        $this->_productFactory = $productFactory;

        $this->_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $this->_customer->load(
            $this->_currentCustomer->getCustomerId()
        );
        $this->_answerCollection->addFilterByCustomer($this->_customer);
        $this->setCollection($this->_answerCollection);

        return $this;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->getCollection()->load();

        return $this;
    }

    /**
     * @param Answer $answer
     *
     * @return string
     */
    public function getProductUrlByAnswer(Answer $answer)
    {
        /** @var Product $product */
        $product = $this->_productFactory->create()->load($answer->getProductId());
        if (null === $product->getId()) {
            return '';
        }
        return $product->getProductUrl();
    }

    /**
     * @param Answer $answer
     *
     * @return string
     */
    public function getProductNameByAnswer(Answer $answer)
    {
        /** @var Product $product */
        $product = $this->_productFactory->create()->load($answer->getProductId());
        if (null === $product->getId()) {
            return '';
        }
        return $product->getName();
    }

    /**
     * @param Answer $answer
     *
     * @return string
     */
    public function getStatusLabelByAnswer(Answer $answer)
    {
        return $this->_sourceQuestionStatus->getOptionByValue($answer->getStatus());
    }

    /**
     * @param mixed $value
     * @param int $length
     * @param string $etc
     * @param string $remainder
     * @param bool $breakWords
     * @return string
     */
    public function truncateString($value, $length = 80, $etc = '...', &$remainder = '', $breakWords = true)
    {
        return $this->filterManager->truncate(
            $value,
            ['length' => $length, 'etc' => $etc, 'remainder' => $remainder, 'breakWords' => $breakWords]
        );
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [
            Answer::CACHE_TAG,
        ];
    }

    /**
     * @param Answer $answer
     *
     * @return bool
     */
    public function checkProductAvailableByAnswer(Answer $answer)
    {
        /** @var Product $product */
        $product = $this->_productFactory->create()->load($answer->getProductId());

        return $product->isAvailable();
    }
}

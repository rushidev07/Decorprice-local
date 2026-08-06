<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Block\Customer\Question;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Customer;
use Aheadworks\Pquestion\Model\Source\Question\Status;
use Aheadworks\Pquestion\Model\ResourceModel\Question\Collection;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Aheadworks\Pquestion\Model\Question;
use Magento\Catalog\Model\Product;

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
     * @var Collection
     */
    protected $_questionCollection;

    /**
     * @param Customer $customer
     * @param Status $sourceQuestionStatus
     * @param Collection $questionCollection
     * @param CurrentCustomer $currentCustomer
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Customer $customer,
        Status $sourceQuestionStatus,
        Collection $questionCollection,
        CurrentCustomer $currentCustomer,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_currentCustomer = $currentCustomer;
        $this->_customer = $customer;
        $this->_sourceQuestionStatus = $sourceQuestionStatus;
        $this->_questionCollection = $questionCollection;
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
        $this->_questionCollection->addFilterByCustomer($this->_customer);
        $this->setCollection($this->_questionCollection);

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
     * @param Question $question
     *
     * @return string
     */
    public function getIsAnsweredLabelForQuestion(Question $question)
    {
        $answerCount = $question->getApprovedAnswerCollection()->getSize();
        if ($answerCount < 1) {
            return __('Not yet');
        }
        if ($answerCount === 1) {
            return __('Yes (1 answer)');
        }

        return __('Yes (%1 answers)', $answerCount);
    }

    /**
     * @param Question $question
     *
     * @return string
     */
    public function getStatusLabelByQuestion(Question $question)
    {
        return $this->_sourceQuestionStatus->getOptionByValue($question->getStatus());
    }

    /**
     * @param Question $question
     *
     * @return string
     */
    public function getProductUrlByQuestion(Question $question)
    {
        /** @var Product $product */
        $product = $question->getProduct();
        if (null === $product->getId()) {
            return '';
        }

        return $product->getProductUrl();
    }

    /**
     * @param Question $question
     *
     * @return string
     */
    public function getProductNameByQuestion(Question $question)
    {
        /** @var Product $product */
        $product = $question->getProduct();
        if (null === $product->getId()) {
            return '';
        }

        return $product->getName();
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
            Question::CACHE_TAG,
        ];
    }

    /**
     * @param Question $question
     *
     * @return bool
     */
    public function checkProductAvailableByQuestion(Question $question)
    {
        /** @var Product $product */
        $product = $question->getProduct();

        return $product->isAvailable();
    }
}

<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Model;

use Magento\Framework\DataObject\IdentityInterface;

/**
 * Class Answer
 *
 * @package Aheadworks\Pquestion\Model
 */
class Answer extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
    /**
     * @var ResourceModel\Summary\Answer\Collection
     */
    protected $_summaryCollection;

    /**
     * @var string
     */
    protected $_eventPrefix = 'aw_pq_answer';

    /**
     * @var string
     */
    protected $_eventObject = 'answer';

    /**
     * @var null
     */
    protected $_question = null;

    /**
     * CACHE_TAG constant
     */
    const CACHE_TAG = 'aw_pquestion_answer';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Aheadworks\Pquestion\Model\ResourceModel\Summary\Answer\Collection $summaryCollection,
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Aheadworks\Pquestion\Model\ResourceModel\Summary\Answer\Collection $summaryCollection,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_summaryCollection = $summaryCollection;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Aheadworks\Pquestion\Model\ResourceModel\Answer::class);
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [
            self::CACHE_TAG,
            self::CACHE_TAG . '_' . $this->getId()
        ];
    }

    /**
     * @return \Aheadworks\Pquestion\Model\ResourceModel\Summary\Answer\Collection
     */
    public function getHelpfulCollection()
    {
        return $this->_summaryCollection->addFilterByAnswerId($this->getId());
    }

    /**
     * @param \Magento\Customer\Model\Customer | \Magento\Customer\Model\Visitor $customer
     * @param int $value
     *
     * @return $this
     * @throws \Exception
     */
    public function addHelpful($customer, $value)
    {
        if (!$customer instanceof \Magento\Customer\Model\Customer
            && !$customer instanceof \Magento\Customer\Model\Visitor) {
            throw new \Exception('Not supported customer object instance.');
        }

        $helpfulCollection = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Aheadworks\Pquestion\Model\Summary\Answer::class)
            ->getCollection()
        ;
        $helpfulCollection->addFilterByAnswerId($this->getId());
        $helpfulModel = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Aheadworks\Pquestion\Model\Summary\Answer::class)
        ;
        if ($customer instanceof \Magento\Customer\Model\Customer) {
            $helpfulCollection->addFilterByCustomerId($customer->getId());
            $helpfulModel = $helpfulCollection->getFirstItem();
            $helpfulModel->setCustomerId($customer->getId());
        }
        if ($customer instanceof \Magento\Customer\Model\Visitor) {
            if ($customer->getCustomerId()) {
                $helpfulCollection->addFilterByCustomerId($customer->getCustomerId());
            } else {
                $helpfulCollection->addFilterByVisitorId($customer->getId());
            }
            $helpfulModel = $helpfulCollection->getFirstItem();
            $helpfulModel
                ->setCustomerId($customer->getCustomerId())
                ->setVisitorId($customer->getId())
            ;
        }

        if (null === $helpfulModel->getId()) {
            $helpfulModel->setHelpful(0);
        }

        $helpfulModel
            ->setAnswerId($this->getId())
            ->setHelpful($helpfulModel->getHelpful() + $value)
        ;
        if (null !== $helpfulModel->getHelpful()
            && !in_array($helpfulModel->getHelpful(), [-1, 0, 1])
        ) {
            throw new \Exception('Not allowed value for this customer.');
        }
        $helpfulModel->save();
        $this
            ->setHelpfulness($this->getHelpfulness() + $value)
            ->save()
        ;
        return $this;
    }

    /**
     * @return \Aheadworks\Pquestion\Model\Question
     */
    public function getQuestion()
    {
        if (null === $this->_question) {
            $this->_question = \Magento\Framework\App\ObjectManager::getInstance()
                ->create(\Aheadworks\Pquestion\Model\Question::class)
                ->load($this->getQuestionId())
            ;

            if ($this->_question->getId() && $this->getProductId()) {
                $this->_question->setProductId($this->getProductId());
            }
        }
        return $this->_question;
    }
}

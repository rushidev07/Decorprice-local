<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Model;

use Magento\Framework\DataObject\IdentityInterface;

class Question extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'aw_pquestion_question';

    /**
     * @var string
     */
    protected $_eventPrefix = 'aw_pq_question';

    /**
     * @var string
     */
    protected $_eventObject = 'question';

    /**
     * @var null
     */
    protected $_product = null;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Aheadworks\Pquestion\Model\ResourceModel\Question::class);
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
     * @return $this
     */
    public function beforeSave()
    {
        if (is_array($this->getShowInStoreIds())) {
            $this->setShowInStoreIds(implode(',', $this->getShowInStoreIds()));
        }
        return parent::beforeSave();
    }

    /**
     * @return $this
     */
    public function afterSave()
    {
        if (strlen($this->getShowInStoreIds()) > 0) {
            $this->setShowInStoreIds(array_map('intval', explode(',', $this->getShowInStoreIds())));
        } else {
            $this->setShowInStoreIds([]);
        }
        return parent::afterSave();
    }

    /**
     * @return $this
     */
    protected function _afterLoad()
    {
        if (strlen($this->getShowInStoreIds()) > 0) {
            $this->setShowInStoreIds(array_map('intval', explode(',', $this->getShowInStoreIds())));
        } else {
            $this->setShowInStoreIds([]);
        }
        return parent::_afterLoad();
    }

    /**
     * @return \Aheadworks\Pquestion\Model\ResourceModel\Answer\Collection
     */
    public function getAnswerCollection()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Aheadworks\Pquestion\Model\Answer::class)
            ->getCollection()->addFilterByQuestionId($this->getId())
        ;
    }

    /**
     * @return \Aheadworks\Pquestion\Model\ResourceModel\Summary\Question\Collection
     */
    public function getHelpfulCollection()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Aheadworks\Pquestion\Model\Summary\Question::class)
            ->getCollection()
            ->addFilterByQuestionId($this->getId())
        ;
    }

    /**
     * @param \Aheadworks\Pquestion\Model\Answer $answer
     *
     * @return $this
     * @throws \Exception
     */
    public function addAnswer(\Aheadworks\Pquestion\Model\Answer $answer)
    {
        if (null === $this->getId()) {
            throw new \Exception('Question ID can not be NULL');
        }
        $answer
            ->setQuestionId($this->getId())
            ->save();
        return $this;
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
            ->create(\Aheadworks\Pquestion\Model\Summary\Question::class)
            ->getCollection()
        ;
        $helpfulCollection->addFilterByQuestionId($this->getId());
        $helpfulModel = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Aheadworks\Pquestion\Model\Question::class)
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
            ->setQuestionId($this->getId())
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
     * @return \Aheadworks\Pquestion\Model\ResourceModel\Answer\Collection
     */
    public function getApprovedAnswerCollection()
    {
        return $this->getAnswerCollection()
            ->addFilterByStatus(\Aheadworks\Pquestion\Model\Source\Question\Status::APPROVED_VALUE)
            ->sortByHelpfull()
        ;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if (null === $this->_product) {
            $this->_product = \Magento\Framework\App\ObjectManager::getInstance()
                ->create(\Magento\Catalog\Model\Product::class)
                ->setStoreId($this->getStoreId())
                ->load($this->getProductId())
            ;
        }
        return $this->_product;
    }
}

<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Model\ResourceModel\Summary\Question;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Aheadworks\Pquestion\Model\Summary\Question::class,
            \Aheadworks\Pquestion\Model\ResourceModel\Summary\Question::class
        );
    }

    /**
     * @param int $questionId
     *
     * @return $this
     */
    public function addFilterByQuestionId($questionId)
    {
        return $this->addFieldToFilter('question_id', $questionId);
    }

    /**
     * @param int $customerId
     *
     * @return $this
     */
    public function addFilterByCustomerId($customerId)
    {
        return $this->addFieldToFilter('customer_id', $customerId);
    }

    /**
     * @param int $visitorId
     *
     * @return $this
     */
    public function addFilterByVisitorId($visitorId)
    {
        return $this->addFieldToFilter('visitor_id', $visitorId);
    }

    /**
     * @param mixed $questionIds
     * @return $this
     */
    public function addFilterByQuestionIds($questionIds)
    {
        return $this->addFieldToFilter('question_id', ['in' => $questionIds]);
    }

    /**
     * Convert items array to hash for select options
     *
     * return items hash
     * array($value => $label)
     *
     * @param   string $valueField
     * @param   string $labelField
     * @return  array
     */
    protected function _toOptionHash($valueField = 'question_id', $labelField = 'helpful')
    {
        return parent::_toOptionHash($valueField, $labelField);
    }
}

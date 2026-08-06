<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Model\ResourceModel\Summary\Answer;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Aheadworks\Pquestion\Model\Summary\Answer::class,
            \Aheadworks\Pquestion\Model\ResourceModel\Summary\Answer::class
        );
    }

    /**
     * @param int $answerId
     *
     * @return $this
     */
    public function addFilterByAnswerId($answerId)
    {
        return $this->addFieldToFilter('answer_id', $answerId);
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
     * @param array $answerIds
     *
     * @return $this
     */
    public function addFilterByAnswerIds($answerIds)
    {
        return $this->addFieldToFilter('answer_id', ['in' => $answerIds]);
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
    protected function _toOptionHash($valueField = 'answer_id', $labelField = 'helpful')
    {
        return parent::_toOptionHash($valueField, $labelField);
    }
}

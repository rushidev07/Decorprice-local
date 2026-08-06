<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Model\ResourceModel\Answer;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Aheadworks\Pquestion\Model\Answer::class,
            \Aheadworks\Pquestion\Model\ResourceModel\Answer::class
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
     * @param int|string|\Magento\Customer\Model\Customer $customer
     *
     * @return $this
     */
    public function addFilterByCustomer($customer)
    {
        $customerValue = $this->_getCustomerFilteredValue($customer);
        if (is_string($customerValue)) {
            return $this->addFieldToFilter('author_email', $customerValue);
        }
        return $this->addFieldToFilter('customer_id', $customerValue);
    }

    /**
     * int customerId | string customerEmail
     * @param int |string|\Magento\Customer\Model\Customer $customer
     *
     * @return int|string
     */
    protected function _getCustomerFilteredValue($customer)
    {
        if (is_string($customer)) {
            return $customer;
        }

        $customerId = $customer;
        $customerEmail = '';
        if ($customer instanceof \Magento\Customer\Model\Customer) {
            $customerEmail = $customer->getEmail();
            $customerId    = (int)$customer->getId();
            if (!$customerId && empty($customerEmail)) {
                $customerId = -1;//empty collection should be returned
            }
        }

        if ($customerId) {
            return $customerId;
        }
        return $customerEmail;
    }

    /**
     * @param int $status
     *
     * @return $this
     */
    public function addFilterByStatus($status)
    {
        return $this->addFieldToFilter('status', $status);
    }

    /**
     * @return $this
     */
    public function addCreatedAtLessThanNowFilter()
    {
        $now = new \Zend_Date();
        return $this->addFieldToFilter(
            'created_at',
            ['lteq' => $now->toString(\Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT)]
        );
    }

    /**
     * @param string $sort
     *
     * @return $this
     */
    public function sortByHelpfull($sort = \Magento\Framework\DB\Select::SQL_DESC)
    {
        return $this->setOrder('helpfulness', $sort);
    }
}

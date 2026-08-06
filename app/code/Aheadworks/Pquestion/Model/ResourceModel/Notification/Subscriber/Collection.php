<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Model\ResourceModel\Notification\Subscriber;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Aheadworks\Pquestion\Model\Notification\Subscriber::class,
            \Aheadworks\Pquestion\Model\ResourceModel\Notification\Subscriber::class
        );
    }

    /**
     * @param mixed $notificationType
     * @return $this
     */
    public function addFilterByNotificationType($notificationType)
    {
        return $this->addFieldToFilter('notification_type', $notificationType);
    }

    /**
     * @param null $websiteId
     * @return $this
     */
    public function addFilterByWebsiteId($websiteId = null)
    {
        if (null === $websiteId) {
            $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        }
        return $this->addFieldToFilter('website_id', $websiteId);
    }

    /**
     * @param int|string|Mage_Customer_Model_Customer $customer
     *
     * @return $this
     */
    public function addFilterByCustomer($customer)
    {
        $customerValue = $this->_getCustomerFilteredValue($customer);
        if (is_string($customerValue)) {
            return $this->addFieldToFilter('customer_email', $customerValue);
        }
        return $this->addFieldToFilter('customer_id', $customerValue);
    }

    /**
     * int customerId|string customerEmail
     * @param int|string|\Magento\Customer\Model\Customer $customer
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
                $customerId = -1; //empty collection should be returned
            }
        }
        if ($customerId) {
            return $customerId;
        }
        return $customerEmail;
    }
}

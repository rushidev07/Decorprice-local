<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Model\Observer\Adminhtml;

use Magento\Framework\Event\ObserverInterface;
use \Aheadworks\Pquestion\Model\Source\Notification\Type as NotificationType;

/**
 * Class AdminhtmlCustomerSaveAfter
 * @package Aheadworks\Pquestion\Model\Observer\Adminhtml
 */
class AdminhtmlCustomerSaveAfter implements ObserverInterface
{
    /**
     * @var \Aheadworks\Pquestion\Model\Notification
     */
    protected $_notification;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @param \Aheadworks\Pquestion\Model\Notification $notification
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Customer\Model\Customer $customer
     */
    public function __construct(
        \Aheadworks\Pquestion\Model\Notification $notification,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\Customer $customer
    ) {
        $this->_notification = $notification;
        $this->_logger = $logger;
        $this->_customer = $customer;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $this->_customer->load(
            $observer->getCustomer()->getId()
        );
        $request = $observer->getRequest();
        $subscribeTo = $request->getParam('subscribe_to', '');
        if (strlen($subscribeTo) > 0) {
            $subscribeTo = explode(',', $subscribeTo);
            $subscribeTo = array_map('intval', $subscribeTo);
        } else {
            $subscribeTo = [];
        }

        //subscribe to
        foreach ($subscribeTo as $type) {
            foreach (NotificationType::$groupMapForCustomer[$type] as $notificationType) {
                try {
                    $this->_notification->subscribe(
                        $customer,
                        $notificationType,
                        $customer->getWebsiteId()
                    );
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                }
            }
        }

        //unsubscribe from
        $unsubscribeFrom = array_diff(
            array_keys(NotificationType::$groupMapForCustomer),
            $subscribeTo
        );
        foreach ($unsubscribeFrom as $type) {
            foreach (NotificationType::$groupMapForCustomer[$type] as $notificationType) {
                try {
                    $this->_notification->unsubscribe(
                        $customer,
                        $notificationType,
                        $customer->getWebsiteId()
                    );
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                }
            }
        }
    }
}

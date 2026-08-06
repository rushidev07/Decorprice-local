<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Controller\Customer;

use \Aheadworks\Pquestion\Model\Source\Notification\Type as NotificationType;

class Subscribe extends \Aheadworks\Pquestion\Controller\Customer
{
    /**
     * @var \Aheadworks\Pquestion\Model\Notification
     */
    protected $_notification;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Aheadworks\Pquestion\Helper\Config $configHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Aheadworks\Pquestion\Model\Notification $notification
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Aheadworks\Pquestion\Helper\Config $configHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Aheadworks\Pquestion\Model\Notification $notification
    ) {
        parent::__construct($context, $configHelper, $customerSession);
        $this->_notification = $notification;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $customerId = (int)$this->_customerSession->getCustomerId();
        $subscribeTo = $this->getRequest()->getParam('aw_pq_customer_subscribe_to', []);
        $subscribeTo = array_map('intval', $subscribeTo);
        //subscribe to
        foreach ($subscribeTo as $type) {
            foreach (NotificationType::$groupMapForCustomer[$type] as $notificationType) {
                $this->_notification->subscribe($customerId, $notificationType);
            }
        }

        //unsubscribe from
        $unsubscribeFrom = array_diff(
            array_keys(NotificationType::$groupMapForCustomer),
            $subscribeTo
        );
        foreach ($unsubscribeFrom as $type) {
            foreach (NotificationType::$groupMapForCustomer[$type] as $notificationType) {
                $this->_notification->unsubscribe($customerId, $notificationType);
            }
        }
        $this->messageManager->addSuccess(
            __("Subscription settings have been successfully saved.")
        );
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setRefererUrl();
    }
}

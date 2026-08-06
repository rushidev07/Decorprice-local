<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Controller\Notification;

use \Aheadworks\Pquestion\Model\Source\Notification\Type as NotificationType;

class UnsubscribePost extends \Aheadworks\Pquestion\Controller\Notification
{
    /**
     * @var \Aheadworks\Pquestion\Helper\Notification
     */
    protected $_notificationHelper;

    /**
     * @var \Aheadworks\Pquestion\Model\Notification
     */
    protected $_notification;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @param \Aheadworks\Pquestion\Helper\Notification $notificationHelper
     * @param \Aheadworks\Pquestion\Model\Notification $notification
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Aheadworks\Pquestion\Helper\Notification $notificationHelper,
        \Aheadworks\Pquestion\Model\Notification $notification,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Action\Context $context
    ) {
        parent::__construct($context);
        $this->_notificationHelper = $notificationHelper;
        $this->_notification = $notification;
        $this->_logger = $logger;
    }

    /**
     * @return $this
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $key = $this->getRequest()->getParam('key', null);
        if (null === $key) {
            return $resultRedirect->setRefererUrl();
        }
        $key = $this->_notificationHelper->decrypt($key);
        list($email, $websiteId) = explode(',', $key ? $key : ',');
        if (empty($email) || empty($websiteId)) {
            return $resultRedirect->setRefererUrl();
        }
        $subscribeTo = $this->getRequest()->getParam('aw_pq_notification_subscribe_to', []);
        $subscribeTo = array_map('intval', $subscribeTo);
        //subscribe to
        foreach ($subscribeTo as $type) {
            foreach (NotificationType::$groupMapForCustomer[$type] as $notificationType) {
                try {
                    $this->_notification->subscribe($email, $notificationType, $websiteId);
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
                    $this->_notification->unsubscribe($email, $notificationType, $websiteId);
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                }
            }
        }
        $this->messageManager->addSuccess(
            __("Subscription settings have been successfully saved.")
        );
        return $resultRedirect->setRefererOrBaseUrl();
    }
}

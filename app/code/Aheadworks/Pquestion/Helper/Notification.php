<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Helper;

use \Aheadworks\Pquestion\Model\Source\Notification\Type as NotificationType;

class Notification extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var \Magento\Framework\Url
     */
    protected $_urlBuilder;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Url $urlBuilder
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Url $urlBuilder
    ) {
        $this->_storeManager = $storeManager;
        $this->_encryptor = $encryptor;
        parent::__construct($context);
        $this->_urlBuilder = $urlBuilder;
    }

    /**
     * @param int|string|\Magento\Customer\Model\Customer $customer
     * @param int|null $websiteId
     * @param string $notificationType
     *
     * @return bool
     */
    public function isCanNotifyCustomer($customer, $notificationType, $websiteId = null)
    {
        $subscriberCollection = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Aheadworks\Pquestion\Model\ResourceModel\Notification\Subscriber\Collection::class)
        ;
        $notificationSource = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(NotificationType::class)
        ;
        $subscriberCollection
            ->addFilterByCustomer($customer)
            ->addFilterByNotificationType($notificationType)
            ->addFilterByWebsiteId($websiteId)
        ;
        $subscriberModel = $subscriberCollection->getFirstItem();
        if (null !== $subscriberModel->getId()) {
            return (bool)$subscriberModel->getValue();
        } elseif (!$notificationSource->isCustomerSubscribedByDefault($websiteId)) {
            return false;
        }
        return true;
    }

    /**
     * @param int|string|\Magento\Customer\Model\Customer $customer
     * @param int $websiteId = null
     *
     * @return array
     *
     * @SuppressWarnings("unused")
     */
    public function getNotificationListForCustomer($customer, $websiteId = null)
    {
        $isSubscribed = array_combine(
            array_keys(NotificationType::$groupMapForCustomer),
            array_fill(0, count(NotificationType::$groupMapForCustomer), false)
        );

        foreach ($isSubscribed as $key => $value) {
            foreach (NotificationType::$groupMapForCustomer[$key] as $notificationType) {
                $isCanNotify = $this->isCanNotifyCustomer($customer, $notificationType, $websiteId);
                if ($isCanNotify) {
                    $isSubscribed[$key] = true;
                    break;
                }
            }
        }
        $notificationSource = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(NotificationType::class)
        ;
        $groupList = $notificationSource->getAllGroupForCustomerDataAsArray();
        $data = [];
        foreach ($groupList as $groupKey => $groupData) {
            $data[$groupKey] = [
                'value'     => $groupKey,
                'label'     => $groupData['label'],
                'checked' => $isSubscribed[$groupKey],
            ];
        }
        return $data;
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function encrypt($value)
    {
        return $this->urlEncoder->encode($this->_encryptor->encrypt($value));
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function decrypt($value)
    {
        return $this->_encryptor->decrypt($this->urlDecoder->decode($value));
    }

    /**
     * @param string $customerEmail
     * @param int $queueId
     * @param mixed $store
     *
     * @return string
     */
    public function getViewWebVersionUrl($customerEmail, $queueId, $store)
    {
        return $this->_getUrl(
            "productquestion/notification/view",
            [
                'key'    => $this->encrypt($customerEmail . ',' . $queueId),
                '_store' => $store
            ]
        );
    }

    /**
     * @param string $customerEmail
     * @param mixed $store
     *
     * @return string
     */
    public function getUnsubscribeUrl($customerEmail, $store)
    {
        $store = $this->_storeManager->getStore($store);
        $websiteId = $store->getWebsiteId();
        return $this->_getUrl(
            "productquestion/notification/unsubscribe",
            [
                'key'    => $this->encrypt($customerEmail . ',' . $websiteId),
                '_store' => $store->getId()
            ]
        );
    }

    /**
     * @param mixed $customerEmail
     * @param mixed $redirectUrl
     * @param mixed $store
     * @return string
     */
    public function getAutoLoginUrl($customerEmail, $redirectUrl, $store)
    {
        $store = $this->_storeManager->getStore($store);
        return $this->_getUrl(
            "productquestion/notification/login",
            [
                'key'    => $this->encrypt($customerEmail . '|' . $redirectUrl . '|' . $store->getId()),
                '_store' => $store->getId()
            ]
        );
    }
}

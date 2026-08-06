<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Model;

/**
 * Class Notification
 * @package Aheadworks\Pquestion\Model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Notification extends \Magento\Email\Model\Template
{
    /**
     * @param string $recipientName
     * @param string $recipientEmail
     * @param string $notificationType
     * @param array $variables
     * @param int|null|\Magento\Store\Model\Store $store
     *
     * @return \Aheadworks\Pquestion\Model\Notification\Queue
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    public function addToQueue($recipientName, $recipientEmail, $notificationType, $variables, $store)
    {
        $notificationSourceType = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Aheadworks\Pquestion\Model\Source\Notification\Type::class)
        ;
        $notificationTypeList = $notificationSourceType->getAllTypesDataAsArray($store);
        if (!array_key_exists($notificationType, $notificationTypeList)) {
            throw new \Exception('Unknown notification type');
        }
        $notificationTypeData = $notificationTypeList[$notificationType];
        if (empty($notificationTypeData['template']) || empty($recipientEmail)) {
            return false;
        }
        $this->setDesignConfig(['area'=>'adminhtml', 'store' => $store]);
        if (is_numeric($notificationTypeData['template'])) {
            $this->load($notificationTypeData['template']);
        } else {
            $localeCode = $this->scopeConfig->getValue(
                'general/locale/code',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            );
            $this->loadDefault($notificationTypeData['template'], $localeCode);
        }
        if (!$this->getId()) {
            throw new \Exception('Invalid transactional email code.');
        }
        $_sender = $notificationSourceType->getSender();
        if (empty($_sender)) {
            throw new \Exception('Sender not specified for this notification type.');
        }

        $this->setDesignConfig(['area'=>'frontend', 'store' => $store]);

        $currentDate = new \Zend_Date();
        $queueModel = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Aheadworks\Pquestion\Model\Notification\Queue::class)
        ;
        $queueModel
            ->setNotificationType($notificationType)
            ->setRecipientName($recipientName)
            ->setRecipientEmail($recipientEmail)
            ->setSubject('')
            ->setBody('')
            ->setSenderName(
                $this->scopeConfig->getValue(
                    'trans_email/ident_' . $_sender . '/name',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $store
                )
            )
            ->setSenderEmail(
                $this->scopeConfig->getValue(
                    'trans_email/ident_' . $_sender . '/email',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $store
                )
            )
            ->setCreatedAt(
                $currentDate->toString(\Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT)
            )
            ->save()
        ;
        $notificationHelper = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Aheadworks\Pquestion\Helper\Notification::class)
        ;
        $variables['unsubscribe_link'] = $notificationHelper->getUnsubscribeUrl(
            $recipientEmail,
            $store
        );
        $variables['web_version_link'] = $notificationHelper->getViewWebVersionUrl(
            $recipientEmail,
            $queueModel->getId(),
            $store
        );
        $queueModel
            ->setSubject($this->getProcessedTemplateSubject($variables))
            ->setBody($this->getProcessedTemplate($variables, true))
            ->save()
        ;
        if ($notificationTypeData['send_now']) {
            $queueModel->send();
        }
        return $queueModel;
    }

    /**
     * @param int|string|\Magento\Customer\Model\Customer $customer
     * @param string $notificationType
     * @param int|null $websiteId
     *
     * @return $this
     */
    public function unsubscribe($customer, $notificationType, $websiteId = null)
    {
        $this->_updateSubscriber($customer, $notificationType, 0, $websiteId);
        return $this;
    }

    /**
     * @param int|string|\Magento\Customer\Model\Customer $customer
     * @param string $notificationType
     * @param int|null $websiteId
     *
     * @return $this
     */
    public function subscribe($customer, $notificationType, $websiteId = null)
    {
        $this->_updateSubscriber($customer, $notificationType, 1, $websiteId);
        return $this;
    }

    /**
     * @param int|string|\Magento\Customer\Model\Customer $customer
     * @param int $notificationType
     * @param int $value
     * @param int $websiteId
     *
     * @return $this
     */
    protected function _updateSubscriber($customer, $notificationType, $value, $websiteId)
    {
        if (null === $websiteId) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }
        if (is_numeric($customer)) {
            $customer = \Magento\Framework\App\ObjectManager::getInstance()
                ->create(\Magento\Customer\Model\Customer::class)
                ->load($customer)
            ;
        }
        if (is_string($customer)) {
            $_email = $customer;
            $customer = \Magento\Framework\App\ObjectManager::getInstance()
                ->create(\Magento\Customer\Model\Customer::class)
                ->setWebsiteId($websiteId)
                ->loadByEmail($_email)
            ;
            $customer->setEmail($_email);
        }

        if (!$customer instanceof \Magento\Customer\Model\Customer) {
            return $this;
        }
        $subscriberModel = $this->_getSubscriber($customer, $notificationType, $websiteId);
        $subscriberModel
            ->setWebsiteId($websiteId)
            ->setNotificationType($notificationType)
            ->setValue($value)
            ->setCustomerId((int)$customer->getId())
            ->setCustomerEmail($customer->getEmail())
            ->save()
        ;
        return $this;
    }

    /**
     * @param int|string|\Magento\Customer\Model\Customer $customer
     * @param string $notificationType
     * @param int|null $websiteId
     *
     * @return \Aheadworks\Pquestion\Model\Notification\Subscriber
     */
    protected function _getSubscriber($customer, $notificationType, $websiteId = null)
    {
        $subscriberCollection = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Aheadworks\Pquestion\Model\ResourceModel\Notification\Subscriber\Collection::class)
        ;
        $subscriberCollection
            ->addFilterByCustomer($customer)
            ->addFilterByNotificationType($notificationType)
            ->addFilterByWebsiteId($websiteId)
        ;
        return $subscriberCollection->getFirstItem();
    }
}

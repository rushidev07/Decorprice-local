<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Model\ResourceModel\Notification;

class Queue extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('aw_pq_notification_queue', 'entity_id');
    }

    /**
     * @param mixed $email
     * @param mixed $queueId
     * @return mixed
     */
    public function getStoredEmail($email, $queueId)
    {
        $queueCollection = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Aheadworks\Pquestion\Model\ResourceModel\Notification\Queue\Collection::class)
        ;
        $queueCollection
            ->addFieldToFilter('recipient_email', $email)
            ->addFieldToFilter('entity_id', $queueId)
        ;
        return $queueCollection->getFirstItem();
    }

    /**
     * @return $this
     */
    public function removeOldStoredEmails()
    {
        $storeCollection = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Magento\Store\Model\ResourceModel\Store\Collection::class)
        ;
        $notificationType = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Aheadworks\Pquestion\Model\Source\Notification\Type::class)
        ;
        foreach ($storeCollection as $storeModel) {
            $storedEmailsLifeTime = $notificationType->getStoredEmailsLifetime($storeModel);

            if (null !== $storedEmailsLifeTime) {
                $finalDay = new \Zend_Date();
                $finalDay->subDay($storedEmailsLifeTime);
                $this->getConnection()->query(
                    "DELETE FROM `{$this->getTable('aw_pq_notification_queue')}`"
                    . " WHERE sent_at  <= '"
                    . $finalDay->toString(\Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT) . "'"
                );
            }
        }
        return $this;
    }
}

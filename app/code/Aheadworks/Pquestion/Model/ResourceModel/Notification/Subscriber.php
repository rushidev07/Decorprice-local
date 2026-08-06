<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Model\ResourceModel\Notification;

class Subscriber extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('aw_pq_notification_subscriber', 'entity_id');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->getValue()) {
            $queueTableName = $this->getTable('aw_pq_notification_queue');
            $this->getConnection()->query(
                "DELETE FROM {$queueTableName}"
                . " WHERE recipient_email = '{$object->getCustomerEmail()}'"
                . " AND notification_type = '{$object->getNotificationType()}'"
                . " AND sent_at IS NULL" //remove not sent emails only
            );
        }
        return $this;
    }
}

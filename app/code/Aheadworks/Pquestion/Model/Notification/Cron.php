<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Model\Notification;

class Cron
{
    const LIMIT = 25;

    /**
     * @var \Aheadworks\Pquestion\Model\ResourceModel\Notification\Queue
     */
    protected $_queueResource;

    /**
     * @var \Aheadworks\Pquestion\Model\ResourceModel\Notification\Queue\Collection
     */
    protected $_queueCollection;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @param \Aheadworks\Pquestion\Model\ResourceModel\Notification\Queue $queueResource
     * @param \Aheadworks\Pquestion\Model\ResourceModel\Notification\Queue\Collection $queueCollection
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Aheadworks\Pquestion\Model\ResourceModel\Notification\Queue $queueResource,
        \Aheadworks\Pquestion\Model\ResourceModel\Notification\Queue\Collection $queueCollection,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_queueResource = $queueResource;
        $this->_queueCollection = $queueCollection;
        $this->_logger = $logger;
    }

    /**
     * @return void
     */
    public function sendQueue()
    {
        $this->_queueCollection
            ->addFilterByPending()
            ->setPageSize(self::LIMIT)
        ;
        foreach ($this->_queueCollection as $queue) {
            try {
                /** @var \Aheadworks\Pquestion\Model\Notification\Queue $queue */
                $queue->send();
            } catch (\Exception $e) {
                $this->_logger->critical($e);
            }
        }
    }

    /**
     * @return $this
     */
    public function removeOldStoredEmails()
    {
        $this->_queueResource->removeOldStoredEmails();
        return $this;
    }
}

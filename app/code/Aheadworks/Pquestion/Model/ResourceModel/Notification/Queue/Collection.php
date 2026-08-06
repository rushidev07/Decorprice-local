<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Model\ResourceModel\Notification\Queue;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Aheadworks\Pquestion\Model\Notification\Queue::class,
            \Aheadworks\Pquestion\Model\ResourceModel\Notification\Queue::class
        );
    }

    /**
     * @return $this
     */
    public function addFilterByPending()
    {
        $this->addFieldToFilter('sent_at', ['null' => true]);
        return $this;
    }
}

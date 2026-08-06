<?php
namespace Aheadworks\RewardPoints\Model\ResourceModel;

/**
 * Class Aheadworks\RewardPoints\Model\ResourceModel\Transaction
 */
class Transaction extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     *  {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init('aw_rp_transaction', 'transaction_id');
    }
}

<?php
namespace Aheadworks\RewardPoints\Model\Indexer\EarnRule\Action;

use Aheadworks\RewardPoints\Model\Indexer\EarnRule\AbstractAction;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Full
 * @package Aheadworks\RewardPoints\Model\Indexer\EarnRule\Action
 */
class Full extends AbstractAction
{
    /**
     * Execute Full reindex
     *
     * @param array|int|null $ids
     * @return void
     * @throws LocalizedException
     */
    public function execute($ids = null)
    {
        try {
            $this->earnRuleProductIndexerResource->reindexAll();
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }
}

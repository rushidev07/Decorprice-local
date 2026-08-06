<?php
namespace Aheadworks\RewardPoints\Model\Indexer\EarnRule;

use Aheadworks\RewardPoints\Model\ResourceModel\EarnRule\Indexer\Product as EarnRuleProductIndexerResource;

/**
 * Class AbstractAction
 * @package Aheadworks\RewardPoints\Model\Indexer\EarnRule
 */
abstract class AbstractAction
{
    /**
     * @var EarnRuleProductIndexerResource
     */
    protected $earnRuleProductIndexerResource;

    /**
     * @param EarnRuleProductIndexerResource $earnRuleProductIndexerResource
     */
    public function __construct(
        EarnRuleProductIndexerResource $earnRuleProductIndexerResource
    ) {
        $this->earnRuleProductIndexerResource = $earnRuleProductIndexerResource;
    }

    /**
     * Execute action for given ids
     *
     * @param array|int $ids
     * @return void
     */
    abstract public function execute($ids);
}

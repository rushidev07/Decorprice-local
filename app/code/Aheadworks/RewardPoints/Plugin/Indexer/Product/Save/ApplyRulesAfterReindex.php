<?php
namespace Aheadworks\RewardPoints\Plugin\Indexer\Product\Save;

use Aheadworks\RewardPoints\Model\Indexer\EarnRule\Processor as EarnRuleIndexerProcessor;
use Magento\Catalog\Model\Product;

/**
 * Class ApplyRulesAfterReindex
 * @package Aheadworks\RewardPoints\Plugin\Indexer\Product\Save
 */
class ApplyRulesAfterReindex
{
    /**
     * @var EarnRuleIndexerProcessor
     */
    private $earnRuleIndexerProcessor;

    /**
     * @param EarnRuleIndexerProcessor $earnRuleIndexerProcessor
     */
    public function __construct(
        EarnRuleIndexerProcessor $earnRuleIndexerProcessor
    ) {
        $this->earnRuleIndexerProcessor = $earnRuleIndexerProcessor;
    }

    /**
     * Apply catalog rules after product resource model save
     *
     * @param Product $subject
     * @return void
     */
    public function afterReindex(Product $subject)
    {
        $this->earnRuleIndexerProcessor->reindexRow($subject->getId());
    }
}

<?php
namespace Aheadworks\RewardPoints\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface EarnRuleSearchResultsInterface
 * @package Aheadworks\RewardPoints\Api\Data
 * @api
 */
interface EarnRuleSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get rule list
     *
     * @return \Aheadworks\RewardPoints\Api\Data\EarnRuleInterface[]
     */
    public function getItems();

    /**
     * Set rule list
     *
     * @param \Aheadworks\RewardPoints\Api\Data\EarnRuleInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

<?php
namespace Aheadworks\RewardPoints\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface TransactionSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get transactions list.
     *
     * @return TransactionInterface[]
     */
    public function getItems();

    /**
     * Set transactions list.
     *
     * @param TransactionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

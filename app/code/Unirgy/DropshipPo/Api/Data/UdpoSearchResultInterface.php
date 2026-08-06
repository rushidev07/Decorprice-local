<?php

namespace Unirgy\DropshipPo\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface UdpoSearchResultInterface extends SearchResultsInterface
{
    /**
     * Gets collection items.
     *
     * @return \Unirgy\DropshipPo\Api\Data\UdpoInterface[] Array of collection items.
     */
    public function getItems();

    /**
     * Set collection items.
     *
     * @param \Unirgy\DropshipPo\Api\Data\UdpoInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

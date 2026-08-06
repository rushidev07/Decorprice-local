<?php

namespace Unirgy\DropshipPo\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface UdpoCommentSearchResultInterface extends SearchResultsInterface
{
    /**
     * Gets collection items.
     *
     * @return \Unirgy\DropshipPo\Api\Data\UdpoCommentInterface[] Array of collection items.
     */
    public function getItems();

    /**
     * Set collection items.
     *
     * @param \Unirgy\DropshipPo\Api\Data\UdpoCommentInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

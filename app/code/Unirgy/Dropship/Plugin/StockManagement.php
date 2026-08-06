<?php

namespace Unirgy\Dropship\Plugin;

use Magento\CatalogInventory\Api\Data\StockItemInterface;

class StockManagement
{
    protected $_hlp;
    protected $_stockAvail;
    public function __construct(
        \Unirgy\Dropship\Helper\Data $udropshipHelper,
        \Unirgy\Dropship\Model\Stock\Availability $stockAvail
    ) {
        $this->_hlp = $udropshipHelper;
        $this->_stockAvail = $stockAvail;
    }
    public function aroundCanSubtractQty(
        \Magento\CatalogInventory\Model\StockManagement $subject,
        \Closure $next,
        StockItemInterface $item
    ) {
    
        $result = $next($item);
        if ($this->_stockAvail->isItemAlwaysInStock($item)) {
            $result = 1;
        }
        return $result;
    }
}

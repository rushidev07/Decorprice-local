<?php

namespace Unirgy\Dropship\Plugin;

use Magento\CatalogInventory\Model\Spi\StockStateProviderInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;

class StockStateProvider
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
    public function aroundCheckQty(
        StockStateProviderInterface $subject,
        \Closure $next,
        StockItemInterface $item,
        $qty
    ) {
    
        $result = $next($item, $qty);
        if ($this->_stockAvail->isItemAlwaysInStock($item)) {
            $result = 1;
        }
        return $result;
    }
    public function aroundVerifyStock(
        StockStateProviderInterface $subject,
        \Closure $next,
        StockItemInterface $item
    ) {
    
        $result = $next($item);
        if ($this->_stockAvail->isItemAlwaysInStock($item)) {
            $result = true;
        }
        return $result;
    }
}

<?php

namespace Unirgy\Dropship\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class InventoryObserverProductQty extends \Magento\CatalogInventory\Observer\ProductQty
{
    /**
     * @return \Unirgy\Dropship\Helper\Data
     */
    protected function _hlp()
    {
        return ObjectManager::getInstance()->get('Unirgy\Dropship\Helper\Data');
    }
    /**
     * @return \Unirgy\Dropship\Model\Stock\Availability
     */
    protected function getStockAvailability()
    {
        return $this->_hlp()->getObj('Unirgy\Dropship\Model\Stock\Availability');
    }
    protected function _addItemToQtyArray(QuoteItem $quoteItem, &$items)
    {
        if (!$this->getStockAvailability()->getUseLocalStockIfAvailable() || $quoteItem->getUdropshipVendor()==$this->_hlp()->getLocalVendorId()) {
            parent::_addItemToQtyArray($quoteItem, $items);
        }
    }
}

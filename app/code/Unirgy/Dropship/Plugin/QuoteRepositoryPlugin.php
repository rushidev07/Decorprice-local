<?php

namespace Unirgy\Dropship\Plugin;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class QuoteRepositoryPlugin
{
    /**
     * @var \Unirgy\Dropship\Helper\Data
     */
    private $_hlp;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    public function __construct(
        \Unirgy\Dropship\Helper\Data $uDropshipHelper,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->_hlp = $uDropshipHelper;
        $this->eventManager = $eventManager;
    }

    public function beforeSave(CartRepositoryInterface $subject, CartInterface $quote)
    {
        if (!$this->_hlp->isActive()) {
            return;
        }
        if ($quote->getRefreshVendorsFlag()) {
            $hlp = $this->_hlp->hlpPr();
            $items = $quote->getAllItems();
            $this->eventManager->dispatch('udropship_prepare_quote_items_before', ['items'=>$items]);
            $hlp->applyDefaultVendorIds($items)->applyStockAvailability($items);
            $this->_hlp->iHlp()->initBaseCosts($items);
            $this->eventManager->dispatch('udropship_prepare_quote_items_after', ['items'=>$items]);
        }
    }
}
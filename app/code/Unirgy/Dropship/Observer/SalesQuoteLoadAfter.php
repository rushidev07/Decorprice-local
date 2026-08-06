<?php

namespace Unirgy\Dropship\Observer;

use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;
use \Unirgy\Dropship\Helper\Item;
use \Unirgy\Dropship\Helper\ProtectedCode;
use \Unirgy\Dropship\Observer\AbstractObserver;

class SalesQuoteLoadAfter extends AbstractObserver implements ObserverInterface
{
    /**
     * @var ProtectedCode
     */
    protected $_hlpPr;

    /**
     * @var Item
     */
    protected $_iHlp;

    protected $_urlBuilder;

    public function __construct(
        ProtectedCode $helperProtectedCode,
        Item $helperItem,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Unirgy\Dropship\Observer\Context $context,
        array $data = []
    ) {

        $this->_hlpPr = $helperProtectedCode;
        $this->_iHlp = $helperItem;
        $this->_urlBuilder = $urlBuilder;

        parent::__construct($context, $data);
    }

    public function execute(Observer $observer)
    {
        try {
            $hl = $this->_hlp;
            $quote = $observer->getQuote();
            $qId = $quote->getId();
            $hlp = $this->_hlpPr;
            if ($hl->isSkipQuoteLoadAfterEvent($qId) || $this->getIsCartUpdateActionFlag()) {
                return;
            }
            $items = $quote->getAllItems();
            $this->_eventManager->dispatch('udropship_prepare_quote_items_before', ['items'=>$items]);
            $hlp->setAllowReorginizeQuote(true);
            $hlp->applyDefaultVendorIds($items)->applyStockAvailability($items);
            $this->_iHlp->initBaseCosts($items);
            $hlp->setAllowReorginizeQuote(false);
            if ($this->getIsCheckoutIndexActionFlag()) {
                if ($quote->getHasAddressError() && !$quote->getHasStockError()) {
                    $quote->setHasError(false);
                }
            } else {
                if ($quote->getHasAddressError() && !$quote->getHasStockError()) {
                    $quote->addMessage(
                        __('Change you address <a href="$1">here</a>.', $this->_urlBuilder->getUrl('checkout'))
                    );
                }
            }
            $this->_eventManager->dispatch('udropship_prepare_quote_items_after', ['items'=>$items]);
        } catch (\Exception $e) {
            $this->_hlp->logError("$e");
            // all necessary actions should be already done by now, kill the exception
        }
    }
}

<?php

namespace Unirgy\Dropship\Observer;

use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;
use \Unirgy\Dropship\Helper\Item;
use \Unirgy\Dropship\Helper\ProtectedCode;
use \Unirgy\Dropship\Observer\AbstractObserver;

class SalesQuoteSaveBefore extends AbstractObserver implements ObserverInterface
{
    /**
     * @var ProtectedCode
     */
    protected $_hlpPr;

    /**
     * @var Item
     */
    protected $_iHlp;

    public function __construct(
        ProtectedCode $helperProtectedCode,
        Item $helperItem,
        \Unirgy\Dropship\Observer\Context $context,
        array $data = []
    ) {
    
        $this->_hlpPr = $helperProtectedCode;
        $this->_iHlp = $helperItem;

        parent::__construct($context, $data);
    }

    public function execute(Observer $observer)
    {
        if (!$this->_hlp->isActive()) {
            return;
        }
        if (($quote = $observer->getQuote()) && $quote->getRefreshVendorsFlag()) {
            $hlp = $this->_hlpPr;
            $items = $quote->getAllItems();
            $this->_eventManager->dispatch('udropship_prepare_quote_items_before', ['items'=>$items]);
            $hlp->applyDefaultVendorIds($items)->applyStockAvailability($items);
            $this->_iHlp->initBaseCosts($items);
            $this->_eventManager->dispatch('udropship_prepare_quote_items_after', ['items'=>$items]);
            if ($this->getIsThrowOnQuoteError() && $quote->getHasError()
                && ($quote->getHasStockError() || $quote->getHasAddressError())
            ) {
                $errorMsg = '';
                foreach ($quote->getErrors() as $__err) {
                    if (!empty($errorMsg)) {
                        $errorMsg .= "\n";
                    }
                    $errorMsg .= $__err->getText();
                }
                $coSession = $this->_hlp->getObj('\Magento\Checkout\Model\Session');
                $urlObj = $this->_hlp->getObj('Magento\Framework\UrlInterface');
                $coSession->setRedirectUrl(
                    $urlObj->getUrl('checkout/cart', ['_secure' => true])
                );
                throw new \Magento\Framework\Exception\LocalizedException(__('Cart Error'));
            }
        }
    }
}

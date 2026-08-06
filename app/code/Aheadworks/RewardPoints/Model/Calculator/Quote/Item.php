<?php
namespace Aheadworks\RewardPoints\Model\Calculator\Quote;

use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem as QuoteAbstractItem;
use Aheadworks\RewardPoints\Model\Quote\Item\Checker as QuoteItemChecker;

/**
 * Class Item
 *
 * @package Aheadworks\RewardPoints\Model\Calculator\Quote
 */
class Item
{
    /**
     * @var QuoteItemChecker
     */
    private $quoteItemChecker;

    /**
     * @param QuoteItemChecker $quoteItemChecker
     */
    public function __construct(
        QuoteItemChecker $quoteItemChecker
    ) {
        $this->quoteItemChecker = $quoteItemChecker;
    }

    /**
     * Calculate quote item total
     *
     * @param CartItemInterface|QuoteAbstractItem $quoteItem
     * @return float|int
     */
    public function calculateItemTotal($quoteItem)
    {
        $total = 0;
        if ($this->quoteItemChecker->hasDynamicBundleParentProduct($quoteItem)) {
            foreach ($quoteItem->getChildren() as $child) {
                $total += $this->getItemBasePrice($child) * $child->getTotalQty()
                    - $child->getBaseDiscountAmount()
                ;
            }
        } else {
            $total = $this->getItemBasePrice($quoteItem) * $quoteItem->getTotalQty()
                - $quoteItem->getBaseDiscountAmount()
            ;
        }
        return $total;
    }

    /**
     * Retrieve quote item base price
     *
     * @param CartItemInterface|QuoteAbstractItem $quoteItem
     * @return float
     */
    public function getItemBasePrice($quoteItem)
    {
        $price = $quoteItem->getDiscountCalculationPrice();
        return $price !== null
            ? $quoteItem->getBaseDiscountCalculationPrice()
            : $quoteItem->getBaseCalculationPrice();
    }

    /**
     * Retrieve quote item price
     *
     * @param CartItemInterface|QuoteAbstractItem $quoteItem
     * @return float
     */
    public function getItemPrice($quoteItem)
    {
        $price = $quoteItem->getDiscountCalculationPrice();
        $calculationPrice = $quoteItem->getCalculationPrice();
        return $price ?? $calculationPrice;
    }

    /**
     * Calculate item price with discount
     *
     * @param CartItemInterface|QuoteAbstractItem $quoteItem
     * @return float|int
     */
    public function calculateItemPriceWithDiscount($quoteItem)
    {
        $itemPrice = $this->getItemPrice($quoteItem);
        $qty = $quoteItem->getTotalQty();
        $itemPriceWithDiscount = $itemPrice * $qty - $quoteItem->getDiscountAmount();
        if ($this->quoteItemChecker->hasDynamicBundleParentProduct($quoteItem)) {
            $itemPriceWithDiscount = $itemPrice * $qty;
            foreach ($quoteItem->getChildren() as $child) {
                $itemPriceWithDiscount -= $child->getDiscountAmount();
            }
        }

        return $itemPriceWithDiscount;
    }

    /**
     * Calculate item base price with discount
     *
     * @param CartItemInterface|QuoteAbstractItem $quoteItem
     * @return float|int
     */
    public function calculateItemBasePriceWithDiscount($quoteItem)
    {
        $itemBasePrice = $this->getItemBasePrice($quoteItem);
        $qty = $quoteItem->getTotalQty();
        $itemBasePriceWithDiscount = $itemBasePrice * $qty - $quoteItem->getBaseDiscountAmount();
        if ($this->quoteItemChecker->hasDynamicBundleParentProduct($quoteItem)) {
            $itemBasePriceWithDiscount = $itemBasePrice * $qty;
            foreach ($quoteItem->getChildren() as $child) {
                $itemBasePriceWithDiscount -= $child->getBaseDiscountAmount();
            }
        }

        return $itemBasePriceWithDiscount;
    }
}

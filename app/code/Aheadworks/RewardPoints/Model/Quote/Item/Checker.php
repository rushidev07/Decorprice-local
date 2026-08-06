<?php
namespace Aheadworks\RewardPoints\Model\Quote\Item;

use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem as QuoteAbstractItem;

/**
 * Class Checker
 *
 * @package Aheadworks\RewardPoints\Model\Quote\Item
 */
class Checker
{
    /**
     * Quote item option code for subscription product of AW SARP2 module
     */
    const AW_SARP2_SUBSCRIPTION_PRODUCT_OPTION_CODE = 'aw_sarp2_subscription_type';

    /**
     * Check if quote item contains subscription product
     *
     * @param CartItemInterface|QuoteAbstractItem $quoteItem
     * @return bool
     */
    public function hasSubscriptionProduct($quoteItem)
    {
        $subscriptionProductOption = $quoteItem->getOptionByCode(
            self::AW_SARP2_SUBSCRIPTION_PRODUCT_OPTION_CODE
        );

        return $subscriptionProductOption && $subscriptionProductOption->getValue();
    }

    /**
     * Check if quote item contains dynamic bundle parent product, calculated by its child products
     *
     * @param CartItemInterface|QuoteAbstractItem $quoteItem
     * @return bool
     */
    public function hasDynamicBundleParentProduct($quoteItem)
    {
        return $quoteItem->getHasChildren() && $quoteItem->isChildrenCalculated();
    }
}

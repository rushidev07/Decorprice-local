<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @version      v1.0.0
 * @since        v1.12.11
 * @created      2025-03-21
 */

namespace WeSupply\Toolbox\Helper\NortonShoppingGuarantee\PackageProtection;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class PackageProtection
 *
 * @package WeSupply\Toolbox\Helper
 */
class NsgPpData extends AbstractHelper
{
    /**
     * NSG Package Protection base cart fee
     */
    private const NSGPP_CART_FEE_PERCENT = 2;

    /**
     * NSG Package Protection minimum fee
     */
    private const NSGPP_MIN_FEE = 0.98;

    /**
     * NSG Package Protection maximum cart value
     */
    private const MAX_CART_VAL = 5000.00;

    /**
     * @var CheckoutSession
     */
    protected CheckoutSession $checkoutSession;

    /**
     * PackageProtection constructor.
     *
     * @param Context $context
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;

        parent::__construct($context);
    }

    /**
     * Check if NSG Package Protection is enabled
     *
     * @return bool
     */
    public function isNsgPpEnabled(): bool
    {
        return $this->scopeConfig->getValue(
            'norton_shopping_guarantee/package_protection/nsgpp_enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get NSG Package Protection store identifier
     *
     * @return int
     */
    public function getStoreNumber(): int
    {
        return $this->scopeConfig->getValue(
            'norton_shopping_guarantee/package_protection/nsgpp_sn',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Calculate the NSG Package Protection fee
     *
     * @param $source
     *
     * @return float
     */
    public function calculateNsgFee($source)
    {
        $nsgppFee = 0.00;

        if (!$source || empty($source->getData())) {
            // No quote available to apply the cart fee
            return $nsgppFee;
        }

        $percentageDecimal = self::NSGPP_CART_FEE_PERCENT / 100;
        $total = min($source->getSubtotal(), self::MAX_CART_VAL);
        $nsgppFee = round(($total * $percentageDecimal), 2);

        return max($nsgppFee, self::NSGPP_MIN_FEE);
    }

    /**
     * Get the actual cart fee for the quote.
     *
     * Get the `epsi_amount` if is present in quote.
     * Otherwise, the fee is calculated using the `calculateCartFee`.
     *
     * @param $quote
     *
     * @return float
     */
    public function getActualCartFee($quote)
    {
        $isEpsi = $this->isNsgPpEnabled() // Just make sure NSG is still enabled
            && filter_var($quote->getData('is_epsi') ?? FALSE, FILTER_VALIDATE_BOOLEAN);
        $epsiAmount = $quote->getData('epsi_amount') ?? 0;

        // Use the stored epsi_amount if it exists, otherwise calculate
        $cartFee = ($isEpsi && $epsiAmount > 0) ? $epsiAmount :
            ($isEpsi ? $this->calculateNsgFee($quote) : 0.00);

        return $cartFee;
    }

    /**
     * Get the NSG Package Protection fee for the order.
     *
     * @param mixed $source
     * @param bool  $recalculate
     *
     * @return float
     */
    public function getEpsiAmount($source, $recalculate)
    {
        if (!$source->getData('is_epsi')) {
            return 0;
        }

        if ($recalculate) {
            return $this->calculateNsgFee($source);
        }

        // Return the EPSI amount from the order
        return floatval($source->getData('epsi_amount') ?? 0);
    }
}

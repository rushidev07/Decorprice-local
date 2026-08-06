<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @version      v1.0.0
 * @since        v1.12.11
 * @created      2025-03-24
 */

namespace WeSupply\Toolbox\Model\NortonShoppingGuarantee\PackageProtection;

use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use WeSupply\Toolbox\Helper\NortonShoppingGuarantee\PackageProtection\NsgPpData as PackageProtectionHelper;

/**
 * Class CartFee
 *
 * @package WeSupply\Toolbox\Model\NortonShoppingGuarantee\PackageProtection
 */
class CartFee extends AbstractTotal
{
    /**
     * @var PackageProtectionHelper
     */
    protected PackageProtectionHelper $helper;

    /**
     * CartFee constructor.
     *
     * @param PackageProtectionHelper $helper
     */
    public function __construct(
        PackageProtectionHelper $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Collect fee totals
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     *
     * @return $this
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $this->clearValues($total);

        $cartFee = $this->helper->getActualCartFee($quote);

        $total->setTotalAmount('nsgpp_fee', $cartFee);
        $total->setBaseTotalAmount('nsgpp_fee', $cartFee);

        // Add fee to grand total
//        $total->setGrandTotal($total->getGrandTotal() + $cartFee);
//        $total->setBaseGrandTotal($total->getBaseGrandTotal() + $cartFee);

        // Make sure these values are set for the quote total as well
        $quote->setData('nsgpp_fee', $cartFee);
        $quote->setData('base_nsgpp_fee', $cartFee);

        return $this;
    }

    /**
     * Fetch fee totals
     *
     * @param Quote $quote
     * @param Total $total
     *
     * @return array
     */
    public function fetch(Quote $quote, Total $total)
    {
        $cartFee = $this->helper->getActualCartFee($quote);

        if ($cartFee > 0) {
            return [
                'code' => 'nsgpp_fee',
                'title' => __('NSG Package Protection'),
                'value' => $cartFee
            ];
        }

        return [];
    }

    /**
     * Clear fee totals
     *
     * @param Total $total
     */
    protected function clearValues(Total $total)
    {
        $total->setTotalAmount('nsgpp_fee', 0);
        $total->setBaseTotalAmount('nsgpp_fee', 0);
    }
}

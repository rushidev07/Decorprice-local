<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @since        v1.12.11
 * @version      v1.0.0
 * @created      2025-04-09
 */

namespace WeSupply\Toolbox\Plugin\NortonShoppingGuarantee\PackageProtection\Order\Invoice;

use Magento\Sales\Block\Adminhtml\Order\Invoice\Totals;
use Magento\Sales\Model\Order\Invoice;
use WeSupply\Toolbox\Helper\NortonShoppingGuarantee\PackageProtection\NsgPpData as PackageProtectionHelper;

/**
 * Class TotalsPlugin
 *
 * @package WeSupply\Toolbox\Plugin\NortonShoppingGuarantee\PackageProtection\Order\Invoice
 */
class TotalsPlugin
{
    /**
     * @var PackageProtectionHelper
     */
    private PackageProtectionHelper $helper;

    /**
     * TotalsPlugin constructor.
     *
     * @param PackageProtectionHelper $helper
     */
    public function __construct(
        PackageProtectionHelper $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Add EPSI amount to invoice
     *
     * @param Totals $subject
     * @param        $result
     *
     * @return Invoice|mixed
     */
    public function afterGetInvoice(Totals $subject, $result)
    {
        if ($result instanceof Invoice) {
            if ($result->getId() // Invoice already created (view page)
                || $result->getData('epsi_added_to_invoice') // Already added to invoice
            ) {
                return $result;
            }

            $order = $result->getOrder();

            $epsiAmount = $this->helper->getEpsiAmount($order, FALSE);

            $result->setData('is_epsi', $order->getData('is_epsi') ?? FALSE);
            $result->setData('epsi_amount', $epsiAmount);

            $result->setGrandTotal(floatval($result->getGrandTotal()) + $epsiAmount);
            $result->setBaseGrandTotal(floatval($result->getBaseGrandTotal()) + $epsiAmount);

            $result->setData('epsi_added_to_invoice', TRUE);
        }

        return $result;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @since        v1.12.11
 * @version      v1.0.0
 * @created      2025-04-04
 */

namespace WeSupply\Toolbox\Observer\NortonShoppingGuarantee\PackageProtection;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Creditmemo;
use WeSupply\Toolbox\Helper\NortonShoppingGuarantee\PackageProtection\NsgPpData as PackageProtectionHelper;

/**
 * Class SaveEpsiAmountToCreditmemo
 *
 * @package WeSupply\Toolbox\Observer\NortonShoppingGuarantee\PackageProtection
 */
class SaveEpsiAmountToCreditmemo implements ObserverInterface
{
    /**
     * @var PackageProtectionHelper
     */
    private PackageProtectionHelper $helper;

    /**
     * Flag for NSG Fee recalculation
     * Will be TRUE if the item quantities were changed
     *
     * @var bool
     */
    private bool $recalcNsgFee = FALSE;

    /**
     * SaveEpsiAmountToCreditmemo constructor.
     *
     * @param PackageProtectionHelper $helper
     */
    public function __construct(
        PackageProtectionHelper $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Save EPSI amount to creditmemo before creation
     *
     * @param Observer $observer
     *
     * @return $this
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getData('creditmemo');

        if (!$creditmemo) {
            return $this;
        }

        $epsiAmount = $this->getEpsiAmount($creditmemo);
        $input = $observer->getEvent()->getData('input');

        if ($input && isset($input['epsi_amount'])) {
            $epsiCustomAmount = floatval($input['epsi_amount']);
            $this->canApplyEpsiAmount($epsiCustomAmount, $epsiAmount);
        }

        $creditmemo->setData('epsi_amount', $epsiAmount);

        if ($this->isEditableField($creditmemo)) {
            $creditmemo->setGrandTotal(floatval($creditmemo->getGrandTotal()) + $epsiAmount);
            $creditmemo->setBaseGrandTotal(floatval($creditmemo->getBaseGrandTotal()) + $epsiAmount);
        }

        return $this;
    }

    /**
     * Get EPSI amount from creditmemo or order
     *
     * @param mixed $creditmemo
     *
     * @return float
     */
    private function getEpsiAmount(Creditmemo $creditmemo)
    {
        $epsiAmount = $creditmemo->getData('epsi_amount');

        if ($epsiAmount === NULL) {
            $order = $creditmemo->getOrder();
            $creditmemo->setData('is_epsi', $order->getData('is_epsi') ?? FALSE);

            $this->creditmemoHasChanges($creditmemo, $order);
            $epsiAmount = $this->helper->getEpsiAmount($order, $this->recalcNsgFee);

            $this->adjustEpsiAmount($order, $epsiAmount);
        }

        return $epsiAmount ?? 0;
    }

    /**
     * Check if the custom EPSI amount is valid
     *
     * @throws LocalizedException
     */
    private function canApplyEpsiAmount($epsiCustomAmount, &$epsiAmount)
    {
        if ($epsiCustomAmount > $epsiAmount) {
            throw new LocalizedException(
                __(
                    'Maximum NSG amount allowed to refund is: %1',
                    number_format($epsiAmount, 2)
                )
            );
        }

        $epsiAmount = $epsiCustomAmount;
    }

    /**
     * Check if on create page
     *
     * @return bool
     */
    public function isEditableField($creditmemo)
    {
        return !$creditmemo->getId();
    }

    /**
     * Check if the creditmemo has changes
     * and set the flag to recalculate the NSG Fee
     *
     * @param Creditmemo $creditmemo
     * @param mixed      $order
     *
     * @return void
     */
    private function creditmemoHasChanges($creditmemo, $order)
    {
        $this->recalcNsgFee
            = floatval($creditmemo->getSubtotal()) !== floatval($order->getSubtotal());

        if (!$this->recalcNsgFee) {
            // Item QTYs not changed -> no need to recalculate
            return;
        }

        $orderItems = $order->getItems();
        $creditmemoItems = $creditmemo->getItems();

        $updateTotal = $this->recalcNsgFee;
        foreach ($orderItems as $item) {
            $qtyShipped = floatval($item->getQtyShipped());

            if (!$qtyShipped) {
                /**
                 * Item was not shipped
                 * Leave the flag as it is and check the next item
                 */
                continue;
            }

            /**
             * If the items were shipped the NSG Fee is not refundable anymore
             * So, we have recalculate the NSG Fee amount allowed for refund
             */
            foreach ($creditmemoItems as $creditmemoItem) {
                if ($item->getItemId() === $creditmemoItem->getData('order_item_id')
                    && floatval($item->getQtyOrdered()) !== floatval($creditmemoItem->getQty())
                ) {
                    /**
                     * Item was shipped
                     * Determine how many items were shipped compared to how many were refunded
                     * Recalculate the NSG fee only when the refunded quantity is less than or equal to the unshipped quantity
                     */
                    $qtyNotShipped = floatval($item->getQtyOrdered()) - $qtyShipped;
                    if ($creditmemoItem->getQty() <= $qtyNotShipped) {
                        $updateTotal = TRUE;
                    }
                }
            }
        }

        if ($updateTotal) {
            /**
             * Update the subtotal of the order
             * to have the correct value for NSG Fee calculation
             */
            $order->setSubtotal(
                floatval($order->getSubtotal()) - floatval($creditmemo->getSubtotal())
            );
        }

        $this->recalcNsgFee = $updateTotal;
    }

    /**
     * Adjust the EPSI amount based on the existing credit memos
     *
     * Iterates through all credit memos associated with a given order
     * and reduces the EPSI amount by the EPSI amounts already refunded in those credit memos
     *
     * @param mixed $order
     * @param float $epsiAmount
     *
     * @return void
     */
    private function adjustEpsiAmount($order, &$epsiAmount)
    {
        $allCreditMemos = $order->getCreditmemosCollection();

        foreach ($allCreditMemos as $creditmemo) {
            if ($creditmemo->getIsEpsi() && $creditmemo->getEpsiAmount()) {
                $epsiAmount -= floatVal($creditmemo->getEpsiAmount());
            }
        }
    }
}

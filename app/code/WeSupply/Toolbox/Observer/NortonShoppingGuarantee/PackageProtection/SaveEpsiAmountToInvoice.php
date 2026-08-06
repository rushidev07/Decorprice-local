<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @since        v1.12.11
 * @version      v1.0.0
 * @created      2025-04-02
 */

namespace WeSupply\Toolbox\Observer\NortonShoppingGuarantee\PackageProtection;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use WeSupply\Toolbox\Helper\NortonShoppingGuarantee\PackageProtection\NsgPpData as PackageProtectionHelper;

/**
 * Class SaveEpsiAmountToInvoice
 *
 * @package WeSupply\Toolbox\Observer\NortonShoppingGuarantee\PackageProtection
 */
class SaveEpsiAmountToInvoice implements ObserverInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var PackageProtectionHelper
     */
    private PackageProtectionHelper $helper;

    /**
     * Flag to check if the total due has already been processed
     *
     * @var bool
     */
    private static bool $isProcessed = FALSE;

    /**
     * SaveEpsiAmountToInvoice constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        PackageProtectionHelper $helper
    ) {
        $this->orderRepository = $orderRepository;
        $this->helper = $helper;
    }

    /**
     * Set EPSI amount on the invoice
     *
     * @param Observer $observer
     *
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var Invoice $invoice */
        $invoice = $observer->getEvent()->getData('invoice');

        if (self::$isProcessed || !$invoice) {
            return $this;
        }

        /** @var Order $order */
        $order = $invoice->getOrder();
        $epsiAmount = $this->helper->getEpsiAmount($order, FALSE);

        $invoice->setData('is_epsi', $order->getData('is_epsi') ?? FALSE);
        $invoice->setData('epsi_amount', $epsiAmount);

        $invoice->setGrandTotal(floatval($invoice->getGrandTotal()) + $epsiAmount);
        $invoice->setBaseGrandTotal(floatval($invoice->getBaseGrandTotal()) + $epsiAmount);

        if (!empty($epsiAmount)) {
            $this->addEpsiAmountToTotalPaid($order, $epsiAmount);
        }

        return $this;
    }

    /**
     * Add EPSI amount to the total paid amount of the order
     *
     * @param Order $order
     * @param float $epsiAmount
     *
     * @return void
     */
    private function addEpsiAmountToTotalPaid(Order $order, float $epsiAmount)
    {
        // Get the current total paid amount
        $totalPaid = floatval($order->getTotalPaid());
        $baseTotalPaid = floatval($order->getBaseTotalPaid());

        // Add the EPSI amount to the total paid amounts
        $order->setTotalPaid($totalPaid + $epsiAmount);
        $order->setBaseTotalPaid($baseTotalPaid + $epsiAmount);

        // Adjust due amounts accordingly
        $order->setTotalDue($order->getGrandTotal() - ($totalPaid + $epsiAmount));
        $order->setBaseTotalDue($order->getBaseGrandTotal() - ($baseTotalPaid + $epsiAmount));

        try {
            self::$isProcessed = TRUE;
            $this->orderRepository->save($order);
        } finally {
            self::$isProcessed = FALSE;
        }
    }
}

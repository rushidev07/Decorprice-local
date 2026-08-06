<?php
namespace Aheadworks\RewardPoints\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class Aheadworks\RewardPoints\Observer\IncreaseOrderRewardPointsInvoicedAmount
 */
class IncreaseOrderRewardPointsInvoicedAmount implements ObserverInterface
{
    /**
     * Increase order aw_reward_points_invoiced attribute based on created invoice
     * used for event: sales_order_invoice_register
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();
        if ($invoice->getBaseAwRewardPointsAmount()) {
            $order->setBaseAwRewardPointsInvoiced(
                $order->getBaseAwRewardPointsInvoiced() + $invoice->getBaseAwRewardPointsAmount()
            );
            $order->setAwRewardPointsInvoiced(
                $order->getAwRewardPointsInvoiced() + $invoice->getAwRewardPointsAmount()
            );
        }
        return $this;
    }
}

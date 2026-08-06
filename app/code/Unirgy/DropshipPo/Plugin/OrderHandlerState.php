<?php

namespace Unirgy\DropshipPo\Plugin;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Handler\State as BaseOrderHandlerState;
use Unirgy\Dropship\Model\Source as DropshipSource;
use Unirgy\DropshipPo\Model\Source as UdpoSource;

class OrderHandlerState
{
    protected $_hlp;
    public function __construct(
        \Unirgy\Dropship\Helper\Data $udropshipHelper
    ) {
        $this->_hlp = $udropshipHelper;
    }
    public function aroundCheck(BaseOrderHandlerState $subject, \Closure $proceed, Order $order)
    {
        if (!$order->isCanceled() && !$order->canUnhold() && !$order->canInvoice()) {
            $udpoHlp = $this->_hlp->udpoHlp();
            if ($order->getIsVirtual() && !$udpoHlp->canCreatePo($order)) {
                $udpoHlp->initOrderUdposCollection($order);
                $isComplete = true;
                foreach ($order->getUdposCollection() as $udpo) {
                    if (!in_array($udpo->getUdropshipStatus(), array(
                        UdpoSource::UDPO_STATUS_SHIPPED,
                        UdpoSource::UDPO_STATUS_DELIVERED,
                        UdpoSource::UDPO_STATUS_CANCELED
                    ))) {
                        $isComplete = false;
                        break;
                    }
                }
                if ($isComplete) {
                    $order->setState(Order::STATE_COMPLETE)
                        ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_COMPLETE));
                }
            } elseif (!$order->canShip()) {
                if (0 == $order->getBaseGrandTotal() || $order->canCreditmemo()) {
                    if ($order->getState() !== Order::STATE_COMPLETE) {
                        if ($this->_hlp->isUdropshipOrder($order)) {
                            $isComplete = true;
                            $shipments = $order->getShipmentsCollection();
                            if ($shipments) {
                                foreach ($shipments as $shipment) {
                                    if (!in_array($shipment->getUdropshipStatus(), array(
                                        DropshipSource::SHIPMENT_STATUS_SHIPPED,
                                        DropshipSource::SHIPMENT_STATUS_DELIVERED,
                                        DropshipSource::SHIPMENT_STATUS_CANCELED
                                    ))) {
                                        $isComplete = false;
                                        break;
                                    }
                                }
                            }
                            if ($isComplete) {
                                $order->setState(Order::STATE_COMPLETE)
                                    ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_COMPLETE));
                            }
                        } else {
                            $order->setState(Order::STATE_COMPLETE)
                                ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_COMPLETE));
                        }
                    }
                } elseif (floatval($order->getTotalRefunded())
                    || !$order->getTotalRefunded() && $order->hasForcedCanCreditmemo()
                ) {
                    if ($order->getState() !== Order::STATE_CLOSED) {
                        $order->setState(Order::STATE_CLOSED)
                            ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CLOSED));
                    }
                }
            }
        }
        if ($order->getState() == Order::STATE_NEW && $order->getIsInProcess()) {
            $order->setState(Order::STATE_PROCESSING)
                ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));
        }
        return $subject;
    }
}
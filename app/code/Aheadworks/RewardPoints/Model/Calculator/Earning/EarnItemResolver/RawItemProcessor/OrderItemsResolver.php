<?php
namespace Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class OrderItemsResolver
 * @package Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor
 */
class OrderItemsResolver
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Get order items
     *
     * @param int $orderId
     * @return OrderItemInterface[] [itemId => OrderItemInterface, ...]
     */
    public function getOrderItems($orderId)
    {
        $orderItems = [];
        /** @var OrderInterface|null $order */
        $order = $this->getOrderById($orderId);
        if ($order) {
            /** @var OrderItemInterface[] $items */
            $items = $order->getItems();
            foreach ($items as $item) {
                $orderItems[$item->getItemId()] = $item;
            }
        }
        return $orderItems;
    }

    /**
     * Retrieve order by id
     *
     * @param int $orderId
     * @return OrderInterface|bool
     */
    private function getOrderById($orderId)
    {
        try {
            return $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }
}

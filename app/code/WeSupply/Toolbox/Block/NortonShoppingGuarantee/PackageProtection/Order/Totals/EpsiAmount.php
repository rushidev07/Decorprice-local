<?php

namespace WeSupply\Toolbox\Block\NortonShoppingGuarantee\PackageProtection\Order\Totals;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Block\Order\Totals;

class EpsiAmount extends Template
{
    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $orderRepo;

    /**
     * @var NULL | OrderInterface
     */
    private ?OrderInterface $order = NULL;

    /**
     * EpsiAmount constructor.
     *
     * @param Context                  $context
     * @param OrderRepositoryInterface $orderRepository
     * @param array                    $data
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        $this->orderRepo = $orderRepository;

        parent::__construct($context, $data);
    }

    /**
     * Initialize EPSI amount total
     *
     * @return $this
     */
    public function initTotals()
    {
        if (!$this->hasEpsiFee()) {
            return $this;
        }

        $parent = $this->getParentBlock();

        if ($parent instanceof Totals) {
            $epsiTotal = new DataObject([
                'code' => 'nsgpp_epsi_amount',
                'label' => __('NSG Package Protection'),
                'value' => $this->getEpsiFeeAmount(),
                'base_value' => $this->getEpsiFeeAmount()
            ]);

            $parent->addTotal($epsiTotal, 'shipping');
        }

        return $this;
    }

    /**
     * Get current order
     *
     * @return OrderInterface|null
     */
    public function getOrder()
    {
        if ($this->order) {
            return $this->order;
        }

        if ($this->getParentBlock() && method_exists($this->getParentBlock(), 'getOrder')) {
            $this->order = $this->getParentBlock()->getOrder();
        }

        return $this->order;
    }

    /**
     * Check if order has EPSI fee
     *
     * @return bool
     */
    public function hasEpsiFee()
    {
        if (!$this->getOrder()) {
            return false;
        }

        return boolval($this->getOrder()->getData('is_epsi'))
            && floatval($this->getOrder()->getData('epsi_amount')) > 0;
    }

    /**
     * Get EPSI amount
     *
     * @return float
     */
    public function getEpsiFeeAmount()
    {
        return floatval($this->getOrder()->getData('epsi_amount') ?? 0);
    }
}

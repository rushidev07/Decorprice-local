<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @version      v1.0.0
 * @since        v1.12.11
 * @created      2025-04-11
 */

namespace WeSupply\Toolbox\Block\NortonShoppingGuarantee\PackageProtection\Order\Creditmemo\Totals;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Block\Order\Creditmemo\Totals;

class EpsiAmount extends Template
{
    /**
     * @var CreditmemoRepositoryInterface
     */
    protected CreditmemoRepositoryInterface $creditmemoRepo;

    /**
     * @var NULL | CreditmemoInterface
     */
    private ?CreditmemoInterface $creditmemo = NULL;

    /**
     * EpsiAmount constructor.
     *
     * @param Context                  $context
     * @param CreditmemoRepositoryInterface $orderRepository
     * @param array                    $data
     */
    public function __construct(
        Context $context,
        CreditmemoRepositoryInterface $orderRepository,
        array $data = []
    ) {
        $this->creditmemoRepo = $orderRepository;

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
     * Get current creditmemo
     *
     * @return CreditmemoInterface
     */
    public function getCreditmemo()
    {
        if ($this->creditmemo) {
            return $this->creditmemo;
        }

        if ($this->getParentBlock() && method_exists($this->getParentBlock(), 'getCreditmemo')) {
            $this->creditmemo = $this->getParentBlock()->getCreditmemo();
        }

        return $this->creditmemo;
    }

    /**
     * Check if order has EPSI fee
     *
     * @return bool
     */
    public function hasEpsiFee()
    {
        if (!$this->getCreditmemo()) {
            return false;
        }

        return boolval($this->getCreditmemo()->getData('is_epsi'))
            && floatval($this->getCreditmemo()->getData('epsi_amount')) > 0;
    }

    /**
     * Get EPSI amount
     *
     * @return float
     */
    public function getEpsiFeeAmount()
    {
        return floatval($this->getCreditmemo()->getData('epsi_amount') ?? 0);
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @since        v1.12.11
 * @version      v1.0.0
 * @created      2025-04-04
 */

namespace WeSupply\Toolbox\Block\Adminhtml\NortonShoppingGuarantee\PackageProtection\Order\Creditmemo\Totals;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;

/**
 * Class EpsiAmount
 *
 * @package WeSupply\Toolbox\Block\Adminhtml\NortonShoppingGuarantee\PackageProtection\Order\Creditmemo\Totals
 */
class EpsiAmount extends Template
{
    /**
     * EpsiAmount constructor.
     *
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Get creditmemo
     *
     * @return Creditmemo
     */
    public function getCreditmemo()
    {
        return $this->getParentBlock()->getCreditmemo();
    }

    /**
     * Get order
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->getCreditmemo()->getOrder();
    }

    /**
     * Initialize totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $total = new DataObject([
            'code' => 'epsi_amount',
            'value' => $this->getEpsiAmount(),
            'base_value' => $this->getEpsiAmount(),
            'label' => __('NSG Package Protection'),
            'block_name' => $this->getNameInLayout()
        ]);

        $this->getParentBlock()->addTotal($total, 'adjustment');

        return $this;
    }

    /**
     * @return int|mixed
     */
    public function getEpsiAmount()
    {
        return $this->getCreditmemo()->getData('epsi_amount') ?? 0;
    }

    /**
     * Check if Epsi is enabled
     *
     * @return bool
     */
    public function isEpsi()
    {
        return $this->getCreditmemo()->getData('is_epsi') ?? FALSE;
    }

    /**
     * Check if on create page
     *
     * @return bool
     */
    public function isEditableField()
    {
        return !$this->getCreditmemo()->getId();
    }
}

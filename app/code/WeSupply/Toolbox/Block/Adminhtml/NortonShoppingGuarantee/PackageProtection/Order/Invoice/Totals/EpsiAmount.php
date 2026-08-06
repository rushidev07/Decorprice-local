<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @since        v1.12.11
 * @version      v1.0.0
 * @created      2025-04-02
 */

namespace WeSupply\Toolbox\Block\Adminhtml\NortonShoppingGuarantee\PackageProtection\Order\Invoice\Totals;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order\Invoice;

/**
 * Class EpsiAmount
 *
 * @package WeSupply\Toolbox\Block\Adminhtml\NortonShoppingGuarantee\PackageProtection\Order\Invoice\Totals
 */
class EpsiAmount extends Template
{
    /**
     * @var Invoice
     */
    protected Invoice $_invoice;

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
     * Get invoice
     *
     * @return Invoice
     */
    public function getInvoice()
    {
        return $this->getParentBlock()->getInvoice();
    }

    /**
     * Initialize totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $invoice = $this->getInvoice();

        if (!$invoice) {
            return $this;
        }

        if ($invoice->getData('is_epsi')) {
            $total = new DataObject([
                'code' => 'nsgpp_fee',
                'value' => $invoice->getData('epsi_amount'),
                'base_value' => $invoice->getData('epsi_amount'),
                'label' => __('NSG Package Protection'),
            ]);

            $this->getParentBlock()->addTotal($total, 'shipping');
        }

        return $this;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @version      v1.0.0
 * @since        v1.12.11
 * @created      2025-04-11
 */

namespace WeSupply\Toolbox\Block\NortonShoppingGuarantee\PackageProtection\Order\Invoice\Totals;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Block\Order\Invoice\Totals;

class EpsiAmount extends Template
{
    /**
     * @var InvoiceRepositoryInterface
     */
    protected InvoiceRepositoryInterface $invoiceRepo;

    /**
     * @var NULL | InvoiceInterface
     */
    private ?InvoiceInterface $invoice = NULL;

    /**
     * EpsiAmount constructor.
     *
     * @param Context                  $context
     * @param InvoiceRepositoryInterface $orderRepository
     * @param array                    $data
     */
    public function __construct(
        Context $context,
        InvoiceRepositoryInterface $orderRepository,
        array $data = []
    ) {
        $this->invoiceRepo = $orderRepository;

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
     * @return InvoiceInterface
     */
    public function getInvoice()
    {
        if ($this->invoice) {
            return $this->invoice;
        }

        if ($this->getParentBlock() && method_exists($this->getParentBlock(), 'getInvoice')) {
            $this->invoice = $this->getParentBlock()->getInvoice();
        }

        return $this->invoice;
    }

    /**
     * Check if order has EPSI fee
     *
     * @return bool
     */
    public function hasEpsiFee()
    {
        if (!$this->getInvoice()) {
            return false;
        }

        return boolval($this->getInvoice()->getData('is_epsi'))
            && floatval($this->getInvoice()->getData('epsi_amount')) > 0;
    }

    /**
     * Get EPSI amount
     *
     * @return float
     */
    public function getEpsiFeeAmount()
    {
        return floatval($this->getInvoice()->getData('epsi_amount') ?? 0);
    }
}

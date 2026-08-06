<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_DropshipPo
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */
 
namespace Unirgy\DropshipPo\Model\OrderInvoiceTotal;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\Shipping as TotalShipping;

class Shipping extends TotalShipping
{
    protected $_hlp;
    public function __construct(
        \Unirgy\Dropship\Helper\Data $udropshipHelper,
        array $data = []
    )
    {
        $this->_hlp = $udropshipHelper;
        parent::__construct($data);
    }
    public function collect(Invoice $invoice)
    {
        $order = $invoice->getOrder();
        $bsaInvoiced = 0;
        foreach ($order->getInvoiceCollection() as $pi) {
            if ($pi->getBaseShippingAmount() && !$pi->isCanceled()) {
                $bsaInvoiced += $pi->getBaseShippingAmount();
            }
        }
        $bsaLeft = max(0, $order->getBaseShippingAmount() - $bsaInvoiced);
        if ($invoice->hasBaseShippingAmount() && !$invoice->isLast()) {
            $bsaToInvoice = min($invoice->getBaseShippingAmount(), $bsaLeft);
        } else {
            $bsaToInvoice = $bsaLeft;
        }
        $_orderRate = $order->getBaseToOrderRate() > 0 ? $order->getBaseToOrderRate() : 1;
        $_incTaxRate = $order->getBaseShippingAmount() == 0 ? 1
            : $order->getBaseShippingInclTax()/$order->getBaseShippingAmount();
            
        $saToInvoice = $_orderRate*$bsaToInvoice;
        $invoice->setShippingAmount($saToInvoice);
        $invoice->setBaseShippingAmount($bsaToInvoice);
        $invoice->setShippingInclTax($this->_hlp->roundPrice($_incTaxRate*$saToInvoice));
        $invoice->setBaseShippingInclTax($this->_hlp->roundPrice($_incTaxRate*$bsaToInvoice));
        $invoice->setGrandTotal($invoice->getGrandTotal()+$saToInvoice);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal()+$bsaToInvoice);
        return $this;
    }
}
<?php

namespace Unirgy\DropshipPo\Model;

use Magento\Sales\Model\Order\Invoice;

class OrderInvoice extends Invoice
{
    public function isLast()
    {
        foreach ($this->getOrder()->getAllItems() as $oItem) {
            $found = false;
            foreach ($this->getAllItems() as $iItem) {
                if ($iItem->getOrderItemId()!=$oItem->getId()) continue;
                $found = true;
                if (!$iItem->isLast()) {
                    return false;
                }
            }
            if (!$found && $oItem->canInvoice()) return false;
        }
        return true;
    }
}
<?php

namespace Unirgy\Dropship\Observer\GraphQl;

use \Magento\Framework\Event\Observer;

class SalesQuoteProductAddAfter extends \Unirgy\Dropship\Observer\SalesQuoteProductAddAfter
{
    public function execute(Observer $observer)
    {
        //parent::execute($observer);
    }
}

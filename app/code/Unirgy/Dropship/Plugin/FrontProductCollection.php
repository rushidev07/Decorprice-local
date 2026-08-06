<?php

namespace Unirgy\Dropship\Plugin;

class FrontProductCollection
{
    public function beforeLoad(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $subject
    ) {
        $subject->addAttributeToSelect('udropship_vendor');
    }
}

<?php

namespace Unirgy\Dropship\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection as CoreProductAttributeCollection;

class ProductAttributeCollection
{
    public function afterAddToIndexFilter(
        CoreProductAttributeCollection $subject, $result)
    {
        $subject->getSelect()->orWhere("main_table.attribute_code='udropship_vendor'");
        return $result;
    }
}

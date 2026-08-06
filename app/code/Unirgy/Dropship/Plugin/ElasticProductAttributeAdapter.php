<?php

namespace Unirgy\Dropship\Plugin;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter as CoreElasticProductAttributeAdapter;

class ElasticProductAttributeAdapter
{
    public function afterIsAlwaysIndexable(
        CoreElasticProductAttributeAdapter $subject, $result)
    {
        $alwaysIndex = [
            'udropship_vendor'
        ];
        return $result || in_array($subject->getAttributeCode(), $alwaysIndex, true);
    }
}

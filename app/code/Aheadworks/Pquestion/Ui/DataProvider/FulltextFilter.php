<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Ui\DataProvider;

use Magento\Framework\Data\Collection;
use Magento\Framework\Api\Filter;

/**
 * Class RegularFilter
 */
class FulltextFilter implements \Magento\Framework\View\Element\UiComponent\DataProvider\FilterApplierInterface
{
    /**
     * Apply regular filters like collection filters
     *
     * @param AbstractDb $collection
     * @param Filter $filter
     * @return void
     */
    public function apply(Collection $collection, Filter $filter)
    {
        $collection->addFieldToFilter(
            $filter->getField(),
            ['like' => sprintf('%%%s%%', $filter->getValue())]
        );
    }
}

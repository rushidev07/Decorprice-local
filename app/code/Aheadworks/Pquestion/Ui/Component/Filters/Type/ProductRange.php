<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Ui\Component\Filters\Type;

class ProductRange extends \Magento\Ui\Component\Filters\Type\Range
{
    /**
     * @return void
     */
    protected function applyFilter()
    {
        parent::applyFilter();

        if (isset($this->filterData[$this->getName()])) {
            $value = $this->filterData[$this->getName()];
            if (isset($value['from']) || isset($value['to'])) {
                $this->_applySharingTypeFilter();
            }
        }
    }

    /**
     * @return void
     */
    protected function _applySharingTypeFilter()
    {
        $filter = $this->filterBuilder->setConditionType('eq')
            ->setField('sharing_type')
            ->setValue(\Aheadworks\Pquestion\Model\Source\Question\Sharing\Type::ORIGINAL_PRODUCT_VALUE)
            ->create();

        $this->getContext()->getDataProvider()->addFilter($filter);
    }
}

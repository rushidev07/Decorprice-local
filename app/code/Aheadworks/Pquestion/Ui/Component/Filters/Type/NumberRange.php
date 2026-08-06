<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Ui\Component\Filters\Type;

class NumberRange extends \Magento\Ui\Component\Filters\Type\Range
{
    /**
     * @return void
     */
    protected function applyFilter()
    {
        if (isset($this->filterData[$this->getName()])) {
            $value = $this->filterData[$this->getName()];

            if (isset($value['from']) && 0 < $value['from']) {
                parent::applyFilter();
                return;
            }
            if (isset($value['to']) && 0 > $value['to']) {
                parent::applyFilter();
                return;
            }
            if (isset($value['from'])) {
                $this->applyMyFilterByType('gteq', $value['from']);
            }

            if (isset($value['to'])) {
                $this->applyMyFilterByType('lteq', $value['to']);
            }
        }
    }

    /**
     * @param string $type
     * @param mixed $value
     *
     * @return void
     */
    protected function applyMyFilterByType($type, $value)
    {

        if (strlen($value) > 0) {
            $filter = $this->filterBuilder->setConditionType('use_undocumented_feature')
                ->setField($this->getName())
                ->setValue([
                    [$type => $value],
                    ['null' => $value]
                ])
                ->create()
            ;
            $this->getContext()->getDataProvider()->addFilter($filter);
        }
    }
}

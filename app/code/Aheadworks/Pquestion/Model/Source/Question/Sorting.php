<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Model\Source\Question;

use Magento\Framework\Data\OptionSourceInterface;

class Sorting implements OptionSourceInterface
{
    const DATE_VALUE        = 1;
    const HELPFULNESS_VALUE = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::DATE_VALUE  => __('Date'),
            self::HELPFULNESS_VALUE => __('Rating')
        ];
    }
}

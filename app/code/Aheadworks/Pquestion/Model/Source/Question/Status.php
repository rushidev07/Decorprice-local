<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Model\Source\Question;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    const PENDING_VALUE  = 1;
    const APPROVED_VALUE = 2;
    const DECLINE_VALUE  = 3;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::PENDING_VALUE,  'label' => __('Pending')],
            ['value' => self::APPROVED_VALUE,  'label' => __('Published')],
            ['value' => self::DECLINE_VALUE,  'label'  => __('Rejected')]
        ];
    }

    /**
     * @param mixed $value
     * @return null|string
     */
    public function getOptionByValue($value)
    {
        $options = $this->toOptionArray();
        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return null;
    }
}

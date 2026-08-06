<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Model\Source\Question;

use Magento\Framework\Data\OptionSourceInterface;

class Visibility implements OptionSourceInterface
{
    const PUBLIC_VALUE  = 1;
    const PRIVATE_VALUE = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::PUBLIC_VALUE, 'label' => __('Public')],
            ['value' => self::PRIVATE_VALUE, 'label' => __('Private')],
        ];
    }

    /**
     * @param mixed $value
     * @return null
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

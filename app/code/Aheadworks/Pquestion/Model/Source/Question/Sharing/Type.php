<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Model\Source\Question\Sharing;

use Magento\Framework\Data\OptionSourceInterface;

class Type implements OptionSourceInterface
{
    const ORIGINAL_PRODUCT_VALUE    = 1;
    const ALL_PRODUCTS_VALUE        = 2;
    const SPECIFIED_PRODUCTS_VALUE  = 3;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $newArray = [];
        $options= $this->toOptionValues();

        foreach ($options as $option) {
            $newArray[$option['value']] = $option['label'];
        }
        return $newArray;
    }

    /**
     * @return array
     */
    public function toOptionValues()
    {
        return [
            ['value' => self::ALL_PRODUCTS_VALUE, 'label' => __('All Products')],
            ['value' => self::SPECIFIED_PRODUCTS_VALUE, 'label' => __('Selected Products')],
            ['value' => self::ORIGINAL_PRODUCT_VALUE, 'label' => __('Certain Product')],
        ];
    }

    /**
     * @param mixed $value
     * @return mixed|null
     */
    public function getOptionByValue($value)
    {
        $options = $this->toOptionArray();
        if (array_key_exists($value, $options)) {
            return $options[$value];
        }
        return null;
    }
}

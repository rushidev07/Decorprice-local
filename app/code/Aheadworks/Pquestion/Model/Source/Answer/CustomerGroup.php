<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Model\Source\Answer;

use Magento\Framework\Data\OptionSourceInterface;

class CustomerGroup implements OptionSourceInterface
{
    const ADMIN_VALUE    = 1;
    const CUSTOMER_VALUE = 2;
    const GUEST_VALUE    = 3;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::ADMIN_VALUE    => __('Admin'),
            self::CUSTOMER_VALUE => __('Customer'),
            self::GUEST_VALUE    => __('Guest')
        ];
    }

    /**
     * @return array
     */
    public function toOptionMultiArray()
    {
        $newArray = [];
        $options = $this->toOptionArray();

        foreach ($options as $key => $option) {
            $newArray[] = ['value' => $key, 'label' => $option];
        }
        return $newArray;
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

<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class WhoCanAskQuestion implements OptionSourceInterface
{
    const REGISTERED_CUSTOMERS_VALUE = 0;
    const ANYONE_VALUE               = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::REGISTERED_CUSTOMERS_VALUE => __('Registered Customers'),
            self::ANYONE_VALUE               => __('Anyone')
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

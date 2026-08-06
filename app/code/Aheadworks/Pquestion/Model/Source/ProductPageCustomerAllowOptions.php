<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ProductPageCustomerAllowOptions implements OptionSourceInterface
{
    const DENIED_VALUE                               = 1;
    const REGISTERED_CUSTOMERS_BOUGHT_PRODUCT_VALUE  = 2;
    const REGISTERED_CUSTOMERS_VALUE                 = 3;
    const ALL_CUSTOMERS_VALUE                        = 4;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::DENIED_VALUE               => __('Nobody (admin only)'),
            self::REGISTERED_CUSTOMERS_BOUGHT_PRODUCT_VALUE => __(
                'Only Registered Customers Who Purchased The Products'
            ),
            self::REGISTERED_CUSTOMERS_VALUE => __('Any Registered Customer'),
            self::ALL_CUSTOMERS_VALUE        => __('Anyone')
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

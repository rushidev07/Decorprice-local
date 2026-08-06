<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_NameYourPrice
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\NameYourPrice\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class PriceType
 * @package Mageplaza\NameYourPrice\Model\Config\Source
 */
class PriceType implements ArrayInterface
{
    const FIXED = 'fixed';
    const PERCENTAGE = 'percentage';

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::FIXED, 'label' => __('Fixed Price')],
            ['value' => self::PERCENTAGE, 'label' => __('Percentage of Original Price')],
        ];
    }
}

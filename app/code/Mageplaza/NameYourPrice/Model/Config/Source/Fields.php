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
 * Class Fields
 * @package Mageplaza\NameYourPrice\Model\Config\Source
 */
class Fields implements ArrayInterface
{
    const NAME = 'customer_name';
    const PHONE = 'phone';
    const MESSAGE = 'customer_message';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        foreach ($this->toArray() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $options;
    }

    /**
     * @return array
     */
    protected function toArray()
    {
        return [
            self::NAME => __('Name'),
            self::PHONE => __('Phone'),
            self::MESSAGE => __('Message'),
        ];
    }
}

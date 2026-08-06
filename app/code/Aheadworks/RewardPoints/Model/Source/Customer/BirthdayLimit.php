<?php
namespace Aheadworks\RewardPoints\Model\Source\Customer;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class BirthdayLimit
 * @package Aheadworks\RewardPoints\Model\Source\Customer
 */
class BirthdayLimit implements ArrayInterface
{
    /**#@+
     * Limit values
     */
    const NO_LIMIT = 'no_limit';
    const ONCE_A_YEAR = 'once_a_year';
    /**#@-*/

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::NO_LIMIT,
                'label' => __('No')
            ],
            [
                'value' => self::ONCE_A_YEAR,
                'label' => __('Once a year')
            ]
        ];
    }
}

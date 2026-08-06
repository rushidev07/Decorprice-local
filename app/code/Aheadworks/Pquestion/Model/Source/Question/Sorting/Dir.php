<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Model\Source\Question\Sorting;

use Magento\Framework\Data\OptionSourceInterface;

class Dir implements OptionSourceInterface
{
    const ASC_VALUE     = 'ASC';
    const DESC_VALUE    = 'DESC';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::ASC_VALUE  => __('Ascending'),
            self::DESC_VALUE => __('Descending')
        ];
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function getInvertedValue($value)
    {
        if ($value == self::DESC_VALUE) {
            return self::ASC_VALUE;
        } else {
            return self::DESC_VALUE;
        }
    }

    /**
     * @param mixed $value
     *
     * @return null|string
     */
    public function getStorageValue($value)
    {
        $sourceToStorage = [
            self::ASC_VALUE  => \Magento\Framework\DB\Select::SQL_ASC,
            self::DESC_VALUE => \Magento\Framework\DB\Select::SQL_DESC
        ];
        return (isset($sourceToStorage[$value]) ? $sourceToStorage[$value] : null);
    }
}

<?php
namespace Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor;

/**
 * Interface ItemGroupConverterInterface
 * @package Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor
 */
interface ItemGroupConverterInterface
{
    /**
     * Convert raw object item groups to item groups
     *
     * @param array $objectItemGroups
     * @return array
     */
    public function convert($objectItemGroups);
}

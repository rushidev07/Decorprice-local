<?php
namespace Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver;

use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemInterface;

/**
 * Interface ItemProcessorInterface
 * @package Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver
 */
interface ItemProcessorInterface
{
    /**
     * @param ItemInterface[] $groupedItems
     * @param bool $beforeTax
     * @return EarnItemInterface
     */
    public function getEarnItem($groupedItems, $beforeTax = true);
}

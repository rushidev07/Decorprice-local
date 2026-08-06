<?php
namespace Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor;

use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemInterface;
use Magento\Catalog\Model\Product;

/**
 * Interface TypeProcessorInterface
 * @package Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor
 */
interface TypeProcessorInterface
{
    /**
     * Get earn items
     *
     * @param Product $product
     * @param bool $beforeTax
     * @return EarnItemInterface[]
     */
    public function getEarnItems($product, $beforeTax = true);
}

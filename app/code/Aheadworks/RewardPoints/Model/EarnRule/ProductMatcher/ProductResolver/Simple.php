<?php
namespace Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\ProductResolver;

use Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\ProductResolverInterface;
use Magento\Catalog\Model\Product;

/**
 * Class Simple
 * @package Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\ProductResolver
 */
class Simple implements ProductResolverInterface
{
    /**
     * Get products for validation
     *
     * @param Product $product
     * @return Product[]
     */
    public function getProductsForValidation($product)
    {
        return [$product];
    }
}

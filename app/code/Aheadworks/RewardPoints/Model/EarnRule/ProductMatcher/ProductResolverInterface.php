<?php
namespace Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher;

use Magento\Catalog\Model\Product;

/**
 * Interface ProductResolverInterface
 * @package Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher
 */
interface ProductResolverInterface
{
    /**
     * Get products for validation
     *
     * @param Product $product
     * @return Product[]
     */
    public function getProductsForValidation($product);
}

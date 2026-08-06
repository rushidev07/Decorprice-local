<?php
namespace Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher;

use Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\ProductResolver\Pool;
use Magento\Catalog\Model\Product;

/**
 * Class ProductResolver
 * @package Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher
 */
class ProductResolver implements ProductResolverInterface
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @param Pool $pool
     */
    public function __construct(
        Pool $pool
    ) {
        $this->pool = $pool;
    }

    /**
     * Get products for validation
     *
     * @param Product $product
     * @return Product[]
     * @throws \Exception
     */
    public function getProductsForValidation($product)
    {
        $resolver = $this->pool->getResolverByCode($product->getTypeId());
        return $resolver->getProductsForValidation($product);
    }
}

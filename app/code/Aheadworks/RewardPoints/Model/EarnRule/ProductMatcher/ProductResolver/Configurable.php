<?php
namespace Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\ProductResolver;

use Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\ProductResolverInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;

/**
 * Class Configurable
 * @package Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\ProductResolver
 */
class Configurable implements ProductResolverInterface
{
    /**
     * Get products for validation
     *
     * @param Product $product
     * @return ProductInterface[]|Product[]
     */
    public function getProductsForValidation($product)
    {
        /** @var ConfigurableType $configurableType */
        $configurableType = $product->getTypeInstance();
        $productsForValidation = $configurableType->getUsedProducts($product);
        $productsForValidation[] = $product;

        return $productsForValidation;
    }
}

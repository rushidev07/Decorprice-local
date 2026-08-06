<?php
namespace Aheadworks\RewardPoints\Model\ResourceModel\EarnRule\Indexer\Product\DataCollector;

use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\Result as ProductMatcherResult;
use Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Class RuleProcessor
 * @package Aheadworks\RewardPoints\Model\ResourceModel\EarnRule\Indexer\Product\DataCollector
 */
class RuleProcessor
{
    /**
     * @var ProductMatcher
     */
    private $productMatcher;

    /**
     * @var ProductMatcherItemsProcessor
     */
    private $productMatcherItemsProcessor;

    /**
     * @param ProductMatcher $productMatcher
     * @param ProductMatcherItemsProcessor $productMatcherItemsProcessor
     */
    public function __construct(
        ProductMatcher $productMatcher,
        ProductMatcherItemsProcessor $productMatcherItemsProcessor
    ) {
        $this->productMatcher = $productMatcher;
        $this->productMatcherItemsProcessor = $productMatcherItemsProcessor;
    }

    /**
     * Prepare rule product data for all matching products
     *
     * @param EarnRuleInterface $rule
     * @return array
     */
    public function getAllMatchingProductsData($rule)
    {
        $data = [];
        /** @var ProductMatcherResult $result */
        $result = $this->productMatcher->matchAllProducts($rule);
        if ($result->getTotalCount() > 0) {
            $data = $this->productMatcherItemsProcessor->prepareData($result->getItems(), $rule);
        }

        return $data;
    }

    /**
     * Prepare rule product data for specific product
     *
     * @param EarnRuleInterface $rule
     * @param ProductInterface $product
     * @return array
     */
    public function getMatchingProductData($rule, $product)
    {
        $data = [];
        /** @var ProductMatcherResult $result */
        $result = $this->productMatcher->matchProduct($product, $rule);
        if ($result->getTotalCount() > 0) {
            $data = $this->productMatcherItemsProcessor->prepareData($result->getItems(), $rule);
        }

        return $data;
    }
}

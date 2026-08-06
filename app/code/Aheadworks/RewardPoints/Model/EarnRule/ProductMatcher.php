<?php
namespace Aheadworks\RewardPoints\Model\EarnRule;

use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Model\EarnRule\Condition\Rule\Loader as ConditionRuleLoader;
use Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\ProductResolver;
use Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\Result;
use Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\ResultFactory;
use Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\Result\Item as ResultItem;
use Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\Result\ItemFactory as ResultItemFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;

/**
 * Class ProductValidator
 * @package Aheadworks\RewardPoints\Model\EarnRule
 */
class ProductMatcher
{
    /**
     * @var ProductResolver
     */
    private $productResolver;

    /**
     * @var ConditionRuleLoader
     */
    private $conditionRuleLoader;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var ResultItemFactory
     */
    private $resultItemFactory;

    /**
     * @param ProductResolver $productResolver
     * @param ConditionRuleLoader $conditionRuleLoader
     * @param ResultFactory $resultFactory
     * @param ResultItemFactory $resultItemFactory
     */
    public function __construct(
        ProductResolver $productResolver,
        ConditionRuleLoader $conditionRuleLoader,
        ResultFactory $resultFactory,
        ResultItemFactory $resultItemFactory
    ) {
        $this->productResolver = $productResolver;
        $this->conditionRuleLoader = $conditionRuleLoader;
        $this->resultFactory = $resultFactory;
        $this->resultItemFactory = $resultItemFactory;
    }

    /**
     * Match the product with the specified rule
     *
     * @param ProductInterface|Product $product
     * @param EarnRuleInterface $rule
     * @return Result
     */
    public function matchProduct($product, $rule)
    {
        /** @var Result $result */
        $result = $this->resultFactory->create();
        $items = [];

        $conditionRule = $this->conditionRuleLoader->loadRule($rule->getCondition());
        $productsForValidation = $this->productResolver->getProductsForValidation($product);
        $isValid = false;
        foreach ($productsForValidation as $productForValidation) {
            if ($conditionRule->validate($productForValidation)) {
                $isValid = true;
                break;
            }
        }
        if ($isValid) {
            $productId = $product->getId();

            $websiteIds = [];
            foreach ($rule->getWebsiteIds() as $ruleWebsiteId) {
                if (in_array($ruleWebsiteId, $product->getWebsiteIds())) {
                    $websiteIds[] = $ruleWebsiteId;
                }
            }

            if (count($websiteIds) >0) {
                /** @var ResultItem $resultItem */
                $resultItem = $this->resultItemFactory->create();
                $resultItem
                    ->setProductId($productId)
                    ->setWebsiteIds($websiteIds);

                $items[] = $resultItem;
            }
        }

        $result
            ->setItems($items)
            ->setTotalCount(count($items));

        return $result;
    }

    /**
     * Get matching products validation data
     *
     * @param EarnRuleInterface $rule
     * @return Result
     */
    public function matchAllProducts($rule)
    {
        /** @var Result $result */
        $result = $this->resultFactory->create();
        $items = [];

        $websiteIds = $rule->getWebsiteIds();
        $conditionRule = $this->conditionRuleLoader->loadRule($rule->getCondition());
        $matchingProductsData = $conditionRule->getMatchingProductIds($websiteIds);

        foreach ($matchingProductsData as $productId => $validatedWebsiteIds) {
            $websiteIds = [];
            foreach ($validatedWebsiteIds as $websiteId => $isWebsiteValid) {
                if ($isWebsiteValid) {
                    $websiteIds[] = $websiteId;
                }
            }
            if (count($websiteIds) >0) {
                /** @var ResultItem $resultItem */
                $resultItem = $this->resultItemFactory->create();
                $resultItem
                    ->setProductId($productId)
                    ->setWebsiteIds($websiteIds);

                $items[] = $resultItem;
            }
        }

        $result
            ->setItems($items)
            ->setTotalCount(count($items));

        return $result;
    }
}

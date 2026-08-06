<?php
namespace Aheadworks\RewardPoints\Model\ResourceModel\EarnRule\Indexer\Product\DataCollector;

use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Model\Indexer\EarnRule\ProductInterface as EarnRuleProductInterface;
use Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\Result\Item as ProductMatcherResultItem;

/**
 * Class ProductMatcherItemsProcessor
 * @package Aheadworks\RewardPoints\Model\ResourceModel\EarnRule\Indexer\Product\DataCollector
 */
class ProductMatcherItemsProcessor
{
    /**
     * Prepare product matcher result items data
     *
     * @param ProductMatcherResultItem[] $items
     * @param EarnRuleInterface $rule
     * @return array
     */
    public function prepareData($items, $rule)
    {
        $data = [];
        $customerGroupIds = $rule->getCustomerGroupIds();

        /** @var ProductMatcherResultItem $item */
        foreach ($items as $item) {
            $websiteIds = $item->getWebsiteIds();
            foreach ($websiteIds as $websiteId) {
                foreach ($customerGroupIds as $customerGroupId) {
                    $data[] = [
                        EarnRuleProductInterface::RULE_ID => $rule->getId(),
                        EarnRuleProductInterface::FROM_DATE => $rule->getFromDate(),
                        EarnRuleProductInterface::TO_DATE => $rule->getToDate(),
                        EarnRuleProductInterface::CUSTOMER_GROUP_ID => $customerGroupId,
                        EarnRuleProductInterface::WEBSITE_ID => $websiteId,
                        EarnRuleProductInterface::PRODUCT_ID => $item->getProductId(),
                        EarnRuleProductInterface::PRIORITY => $rule->getPriority(),
                    ];
                }
            }
        }

        return $data;
    }
}

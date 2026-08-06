<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\ResourceModel\EarnRule\Indexer\Product\DataCollector;

use Aheadworks\RewardPoints\Model\ResourceModel\EarnRule\Indexer\Product\DataCollector\ProductMatcherItemsProcessor;
use Aheadworks\RewardPoints\Model\Indexer\EarnRule\ProductInterface as EarnRuleProductInterface;
use Aheadworks\RewardPoints\Model\ResourceModel\EarnRule\Hydrator\Action as ActionHydrator;
use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\Result\Item as ProductMatcherResultItem;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for ProductMatcherItemsProcessor
 */
class ProductMatcherItemsProcessorTest extends TestCase
{
    /**
     * @var ProductMatcherItemsProcessor
     */
    private $processor;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->processor = $objectManager->getObject(ProductMatcherItemsProcessor::class, []);
    }

    /**
     * Test prepareData method
     *
     * @param ProductMatcherResultItem[]|\PHPUnit_Framework_MockObject_MockObject[] $items
     * @param EarnRuleInterface|\PHPUnit_Framework_MockObject_MockObject $rule
     * @param array $result
     * @dataProvider prepareDataDataProvider
     */
    public function testPrepareData($items, $rule, $result)
    {
        $this->assertEquals($result, $this->processor->prepareData($items, $rule));
    }

    /**
     * @return array
     */
    public function prepareDataDataProvider()
    {
        return [
            [
                'items' => [$this->getResultItemMock(125, [1, 3])],
                'rule' => $this->getRuleMock(100, null, null, 90, true, [10]),
                'result' => [
                    [
                        EarnRuleProductInterface::RULE_ID => 100,
                        EarnRuleProductInterface::FROM_DATE => null,
                        EarnRuleProductInterface::TO_DATE => null,
                        EarnRuleProductInterface::CUSTOMER_GROUP_ID => 10,
                        EarnRuleProductInterface::PRODUCT_ID => 125,
                        EarnRuleProductInterface::PRIORITY => 90,
                        EarnRuleProductInterface::WEBSITE_ID => 1,
                    ],
                    [
                        EarnRuleProductInterface::RULE_ID => 100,
                        EarnRuleProductInterface::FROM_DATE => null,
                        EarnRuleProductInterface::TO_DATE => null,
                        EarnRuleProductInterface::CUSTOMER_GROUP_ID => 10,
                        EarnRuleProductInterface::PRODUCT_ID => 125,
                        EarnRuleProductInterface::PRIORITY => 90,
                        EarnRuleProductInterface::WEBSITE_ID => 3,
                    ],
                ]
            ],
            [
                'items' => [$this->getResultItemMock(125, [5])],
                'rule' => $this->getRuleMock(100, '2018-01-01', '2018-01-31', 90, true, [10, 11]),
                'result' => [
                    [
                        EarnRuleProductInterface::RULE_ID => 100,
                        EarnRuleProductInterface::FROM_DATE => '2018-01-01',
                        EarnRuleProductInterface::TO_DATE => '2018-01-31',
                        EarnRuleProductInterface::CUSTOMER_GROUP_ID => 10,
                        EarnRuleProductInterface::PRODUCT_ID => 125,
                        EarnRuleProductInterface::PRIORITY => 90,
                        EarnRuleProductInterface::WEBSITE_ID => 5,
                    ],
                    [
                        EarnRuleProductInterface::RULE_ID => 100,
                        EarnRuleProductInterface::FROM_DATE => '2018-01-01',
                        EarnRuleProductInterface::TO_DATE => '2018-01-31',
                        EarnRuleProductInterface::CUSTOMER_GROUP_ID => 11,
                        EarnRuleProductInterface::PRODUCT_ID => 125,
                        EarnRuleProductInterface::PRIORITY => 90,
                        EarnRuleProductInterface::WEBSITE_ID => 5,
                    ],
                ]
            ]
        ];
    }

    /**
     * Get result item mock
     *
     * @param int $productId
     * @param int[] $websiteIds
     * @return ProductMatcherResultItem|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getResultItemMock($productId, $websiteIds)
    {
        $resultItemMock = $this->createMock(ProductMatcherResultItem::class);
        $resultItemMock->expects($this->any())
            ->method('getProductId')
            ->willReturn($productId);
        $resultItemMock->expects($this->any())
            ->method('getWebsiteIds')
            ->willReturn($websiteIds);

        return $resultItemMock;
    }

    /**
     * Get rule mock
     *
     * @param int $ruleId
     * @param string|null $ruleFrom
     * @param string|null $ruleTo
     * @param int $rulePriority
     * @param bool $ruleStopsProcessing
     * @param int[] $customerGroupIds
     * @return EarnRuleInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getRuleMock($ruleId, $ruleFrom, $ruleTo, $rulePriority, $ruleStopsProcessing, $customerGroupIds)
    {
        $ruleMock = $this->createMock(EarnRuleInterface::class);
        $ruleMock->expects($this->any())
            ->method('getId')
            ->willReturn($ruleId);
        $ruleMock->expects($this->any())
            ->method('getFromDate')
            ->willReturn($ruleFrom);
        $ruleMock->expects($this->any())
            ->method('getToDate')
            ->willReturn($ruleTo);
        $ruleMock->expects($this->any())
            ->method('getPriority')
            ->willReturn($rulePriority);
        $ruleMock->expects($this->any())
            ->method('getDiscardSubsequentRules')
            ->willReturn($ruleStopsProcessing);
        $ruleMock->expects($this->any())
            ->method('getCustomerGroupIds')
            ->willReturn($customerGroupIds);

        return $ruleMock;
    }
}

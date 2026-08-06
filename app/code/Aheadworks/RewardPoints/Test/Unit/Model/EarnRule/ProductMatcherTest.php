<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\EarnRule;

use Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher;
use Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\ProductResolver;
use Aheadworks\RewardPoints\Api\Data\ConditionInterface;
use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Model\EarnRule\Condition\Rule\Loader as ConditionRuleLoader;
use Aheadworks\RewardPoints\Model\EarnRule\Condition\Rule as ConditionRule;
use Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\Result;
use Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\ResultFactory;
use Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\Result\Item as ResultItem;
use Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\Result\ItemFactory as ResultItemFactory;
use Aheadworks\RewardPoints\Model\Indexer\EarnRule\ProductInterface;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher
 */
class ProductMatcherTest extends TestCase
{
    /**
     * @var ProductMatcher
     */
    private $productMatcher;

    /**
     * @var ProductResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productResolverMock;

    /**
     * @var ConditionRuleLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $conditionRuleLoaderMock;

    /**
     * @var ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var ResultItemFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultItemFactoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->productResolverMock = $this->createMock(ProductResolver::class);
        $this->conditionRuleLoaderMock = $this->createMock(ConditionRuleLoader::class);
        $this->resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->resultItemFactoryMock = $this->createMock(ResultItemFactory::class);

        $this->productMatcher = $objectManager->getObject(
            ProductMatcher::class,
            [
                'productResolver' => $this->productResolverMock,
                'conditionRuleLoader' => $this->conditionRuleLoaderMock,
                'resultFactory' => $this->resultFactoryMock,
                'resultItemFactory' => $this->resultItemFactoryMock,
            ]
        );
    }

    /**
     * Test matchProduct method
     *
     * @param ProductInterface|Product|\PHPUnit_Framework_MockObject_MockObject $product
     * @param int[] $ruleWebsiteIds
     * @param bool $validationResult
     * @param ResultItem|\PHPUnit_Framework_MockObject_MockObject $resultItem
     * @dataProvider matchProductDataProvider
     */
    public function testMatchProduct($product, $ruleWebsiteIds, $validationResult, $resultItem)
    {
        $resultMock = $this->createMock(Result::class);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultMock);

        $this->productResolverMock->expects($this->once())
            ->method('getProductsForValidation')
            ->with($product)
            ->willReturn([$product]);

        $conditionMock = $this->createMock(ConditionInterface::class);
        $ruleMock = $this->getRuleMock($conditionMock, $ruleWebsiteIds);

        $conditionRuleMock = $this->createMock(ConditionRule::class);
        $this->conditionRuleLoaderMock->expects($this->once())
            ->method('loadRule')
            ->with($conditionMock)
            ->willReturn($conditionRuleMock);

        $conditionRuleMock->expects($this->once())
            ->method('validate')
            ->with($product)
            ->willReturn($validationResult);

        if ($resultItem) {
            $this->resultItemFactoryMock->expects($this->once())
                ->method('create')
                ->willReturn($resultItem);

            $resultMock->expects($this->once())
                ->method('setItems')
                ->with([$resultItem])
                ->willReturnSelf();
            $resultMock->expects($this->once())
                ->method('setTotalCount')
                ->with(1)
                ->willReturnSelf();
        } else {
            $resultMock->expects($this->once())
                ->method('setItems')
                ->with([])
                ->willReturnSelf();
            $resultMock->expects($this->once())
                ->method('setTotalCount')
                ->with(0)
                ->willReturnSelf();
        }

        $this->assertSame($resultMock, $this->productMatcher->matchProduct($product, $ruleMock));
    }

    /**
     * @return array
     */
    public function matchProductDataProvider()
    {
        return [
            [
                'product' => $this->getProductMock(100, [10, 20]),
                'ruleWebsiteIds' => [10, 11],
                'validationResult' => true,
                'resultItem' => $this->getResultItemMock(100, [10])
            ],
            [
                'product' => $this->getProductMock(100, [10, 20]),
                'ruleWebsiteIds' => [11],
                'validationResult' => true,
                'resultItem' => null
            ],
            [
                'product' => $this->getProductMock(100, [10, 20]),
                'ruleWebsiteIds' => [10, 11],
                'validationResult' => false,
                'resultItem' => null
            ],
            [
                'product' => $this->getProductMock(100, [10, 20]),
                'ruleWebsiteIds' => [11],
                'validationResult' => false,
                'resultItem' => null
            ],
        ];
    }

    /**
     * Test matchAllProducts method
     *
     * @param int[] $ruleWebsiteIds
     * @param array $matchingProductsData
     * @param ResultItem[]|\PHPUnit_Framework_MockObject_MockObject[] $resultItems
     * @dataProvider matchAllProductsDataProvider
     */
    public function testMatchAllProducts($ruleWebsiteIds, $matchingProductsData, $resultItems)
    {
        $resultMock = $this->createMock(Result::class);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultMock);

        $conditionMock = $this->createMock(ConditionInterface::class);
        $ruleMock = $this->getRuleMock($conditionMock, $ruleWebsiteIds);

        $conditionRuleMock = $this->createMock(ConditionRule::class);
        $this->conditionRuleLoaderMock->expects($this->once())
            ->method('loadRule')
            ->with($conditionMock)
            ->willReturn($conditionRuleMock);

        $conditionRuleMock->expects($this->once())
            ->method('getMatchingProductIds')
            ->with($ruleWebsiteIds)
            ->willReturn($matchingProductsData);

        if (count($resultItems) > 0) {
            $this->resultItemFactoryMock->expects($this->exactly(2))
                ->method('create')
                ->willReturnOnConsecutiveCalls($resultItems[0], $resultItems[1]);

            $resultMock->expects($this->once())
                ->method('setItems')
                ->with($resultItems)
                ->willReturnSelf();
            $resultMock->expects($this->once())
                ->method('setTotalCount')
                ->with(2)
                ->willReturnSelf();
        } else {
            $resultMock->expects($this->once())
                ->method('setItems')
                ->with([])
                ->willReturnSelf();
            $resultMock->expects($this->once())
                ->method('setTotalCount')
                ->with(0)
                ->willReturnSelf();
        }

        $this->assertSame($resultMock, $this->productMatcher->matchAllProducts($ruleMock));
    }

    /**
     * @return array
     */
    public function matchAllProductsDataProvider()
    {
        return [
            [
                'ruleWebsiteIds' => [11, 12],
                'matchingProductsData' => [
                    125 => [11 => true, 12 => true],
                    126 => [11 => true, 12 => false],
                ],
                'resultItems' => [
                    $this->getResultItemMock(125, [11, 12]),
                    $this->getResultItemMock(126, [11]),
                ]
            ],
            [
                'ruleWebsiteIds' => [11, 12],
                'matchingProductsData' => [
                    125 => [11 => false, 12 => false],
                    126 => [11 => false, 12 => false],
                ],
                'resultItems' => []
            ],
            [
                'ruleWebsiteIds' => [11, 12],
                'matchingProductsData' => [],
                'resultItems' => []
            ]
        ];
    }

    /**
     * Get product mock
     *
     * @param int $productId
     * @param int[] $websiteIds
     * @return ProductInterface|Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getProductMock($productId, $websiteIds)
    {
        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->any())
            ->method('getId')
            ->willReturn($productId);
        $productMock->expects($this->any())
            ->method('getWebsiteIds')
            ->willReturn($websiteIds);

        return $productMock;
    }

    /**
     * Get rule mock
     *
     * @param ConditionInterface|\PHPUnit_Framework_MockObject_MockObject $conditionMock
     * @param array $websiteIds
     * @return EarnRuleInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getRuleMock($conditionMock, $websiteIds)
    {
        $ruleMock = $this->createMock(EarnRuleInterface::class);
        $ruleMock->expects($this->any())
            ->method('getCondition')
            ->willReturn($conditionMock);
        $ruleMock->expects($this->any())
            ->method('getWebsiteIds')
            ->willReturn($websiteIds);

        return $ruleMock;
    }

    /**
     * Get result item mock
     *
     * @param int $productId
     * @param int[] $websiteIds
     * @return ResultItem|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getResultItemMock($productId, $websiteIds)
    {
        $resultItemMock = $this->createMock(ResultItem::class);
        $resultItemMock->expects($this->once())
            ->method('setProductId')
            ->with($productId)
            ->willReturnSelf();
        $resultItemMock->expects($this->once())
            ->method('setWebsiteIds')
            ->with($websiteIds)
            ->willReturnSelf();

        return $resultItemMock;
    }
}

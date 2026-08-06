<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Calculator\Earning\EarnItemResolver\ItemProcessor;

use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemProcessor\Bundle
    as BundleItemProcessor;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemInterfaceFactory;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemProcessor\Bundle
 */
class BundleTest extends TestCase
{
    /**
     * @var BundleItemProcessor
     */
    private $processor;

    /**
     * @var EarnItemInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $earnItemFactoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->earnItemFactoryMock = $this->createMock(EarnItemInterfaceFactory::class);

        $this->processor = $objectManager->getObject(
            BundleItemProcessor::class,
            [
                'earnItemFactory' => $this->earnItemFactoryMock,
            ]
        );
    }

    /**
     * Test getEarnItem method
     *
     * @param array $groupedItems
     * @param bool $beforeTax
     * @param EarnItemInterface|\PHPUnit_Framework_MockObject_MockObject $earnItem
     * @dataProvider getEarnItemDataProvider
     */
    public function testGetEarnItem($groupedItems, $beforeTax, $earnItem)
    {
        $this->earnItemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($earnItem);

        $this->assertSame($earnItem, $this->processor->getEarnItem($groupedItems, $beforeTax));
    }

    /**
     * @return array
     */
    public function getEarnItemDataProvider()
    {
        $parentFixedItem = $this->getItemMock(null, 125, false, 100, 110, 25, 10, 5);
        $childFixedItem = $this->getItemMock($parentFixedItem, 126, false, 0, 0, 0, 0, 0);
        $parentDynamicItem = $this->getItemMock(null, 130, true, 100, 110, 25, 10, 5);
        $childDynamicOneItem = $this->getItemMock($parentFixedItem, 131, false, 20, 22, 5, 5, 0);
        $childDynamicTwoItem = $this->getItemMock($parentFixedItem, 132, false, 30, 33, 4, 4, 0);
        return [
            [
                'groupedItems' => [$parentFixedItem, $childFixedItem],
                'beforeTax' => true,
                'earnItem' => $this->getEarnItemMock(125, 65, 5)
            ],
            [
                'groupedItems' => [$parentFixedItem, $childFixedItem],
                'beforeTax' => false,
                'earnItem' => $this->getEarnItemMock(125, 75, 5)
            ],
            [
                'groupedItems' => [$parentFixedItem],
                'beforeTax' => false,
                'earnItem' => $this->getEarnItemMock(null, 0, 0)
            ],
            [
                'groupedItems' => [$childFixedItem],
                'beforeTax' => false,
                'earnItem' => $this->getEarnItemMock(null, 0, 0)
            ],
            [
                'groupedItems' => [$parentDynamicItem, $childDynamicOneItem, $childDynamicTwoItem],
                'beforeTax' => true,
                'earnItem' => $this->getEarnItemMock(130, 32, 5)
            ],
            [
                'groupedItems' => [$parentDynamicItem, $childDynamicOneItem, $childDynamicTwoItem],
                'beforeTax' => false,
                'earnItem' => $this->getEarnItemMock(130, 37, 5)
            ],
            [
                'groupedItems' => [],
                'beforeTax' => false,
                'earnItem' => $this->getEarnItemMock(null, 0, 0)
            ],
        ];
    }

    /**
     * Get item mock
     *
     * @param ItemInterface|\PHPUnit_Framework_MockObject_MockObject|null $parent
     * @param int $productId
     * @param bool $isChildrenCalculated
     * @param float $baseRowTotal
     * @param float $baseRowTotalInclTax
     * @param float $baseDiscountAmount
     * @param float $baseAwRewardPintsAmount
     * @param float $qty
     * @return ItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getItemMock(
        $parent,
        $productId,
        $isChildrenCalculated,
        $baseRowTotal,
        $baseRowTotalInclTax,
        $baseDiscountAmount,
        $baseAwRewardPintsAmount,
        $qty
    ) {
        $itemMock = $this->createMock(ItemInterface::class);
        $itemMock->expects($this->any())
            ->method('getParentItem')
            ->willReturn($parent);
        $itemMock->expects($this->any())
            ->method('getProductId')
            ->willReturn($productId);
        $itemMock->expects($this->any())
            ->method('getIsChildrenCalculated')
            ->willReturn($isChildrenCalculated);
        $itemMock->expects($this->any())
            ->method('getBaseRowTotal')
            ->willReturn($baseRowTotal);
        $itemMock->expects($this->any())
            ->method('getBaseRowTotalInclTax')
            ->willReturn($baseRowTotalInclTax);
        $itemMock->expects($this->any())
            ->method('getBaseDiscountAmount')
            ->willReturn($baseDiscountAmount);
        $itemMock->expects($this->any())
            ->method('getBaseAwRewardPointsAmount')
            ->willReturn($baseAwRewardPintsAmount);
        $itemMock->expects($this->any())
            ->method('getQty')
            ->willReturn($qty);

        return $itemMock;
    }

    /**
     * Get earn item mock
     *
     * @param int $productId
     * @param float $baseAmount
     * @param $qty
     * @return EarnItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getEarnItemMock($productId, $baseAmount, $qty)
    {
        $earnItemMock = $this->createMock(EarnItemInterface::class);
        $earnItemMock->expects($this->once())
            ->method('setProductId')
            ->with($productId)
            ->willReturnSelf();
        $earnItemMock->expects($this->once())
            ->method('setBaseAmount')
            ->with($baseAmount)
            ->willReturnSelf();
        $earnItemMock->expects($this->once())
            ->method('setQty')
            ->with($qty)
            ->willReturnSelf();

        return $earnItemMock;
    }
}

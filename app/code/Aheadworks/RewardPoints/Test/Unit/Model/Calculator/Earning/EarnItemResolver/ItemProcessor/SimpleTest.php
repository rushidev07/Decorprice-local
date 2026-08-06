<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Calculator\Earning\EarnItemResolver\ItemProcessor;

use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemProcessor\Simple as SimpleItemProcessor;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemInterfaceFactory;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemProcessor\Simple
 */
class SimpleTest extends TestCase
{
    /**
     * @var SimpleItemProcessor
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
            SimpleItemProcessor::class,
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
        return [
            [
                'groupedItems' => [
                    $this->getItemMock(125, 100, 110, 0, 0, 5)
                ],
                'beforeTax' => true,
                'earnItem' => $this->getEarnItemMock(125, 100, 5)
            ],
            [
                'groupedItems' => [
                    $this->getItemMock(125, 100, 110, 0, 0, 5)
                ],
                'beforeTax' => false,
                'earnItem' => $this->getEarnItemMock(125, 110, 5)
            ],
            [
                'groupedItems' => [
                    $this->getItemMock(125, 100, 110, 25, 0, 5)
                ],
                'beforeTax' => true,
                'earnItem' => $this->getEarnItemMock(125, 75, 5)
            ],
            [
                'groupedItems' => [
                    $this->getItemMock(125, 100, 110, 25, 0, 5)
                ],
                'beforeTax' => false,
                'earnItem' => $this->getEarnItemMock(125, 85, 5)
            ],
            [
                'groupedItems' => [
                    $this->getItemMock(125, 100, 110, 25, 10, 5)
                ],
                'beforeTax' => true,
                'earnItem' => $this->getEarnItemMock(125, 65, 5)
            ],
            [
                'groupedItems' => [
                    $this->getItemMock(125, 100, 110, 25, 10, 5)
                ],
                'beforeTax' => false,
                'earnItem' => $this->getEarnItemMock(125, 75, 5)
            ],
            [
                'groupedItems' => [],
                'beforeTax' => true,
                'earnItem' => $this->getEarnItemMock(null, 0, 0)
            ],
        ];
    }

    /**
     * Get item mock
     *
     * @param int $productId
     * @param float $baseRowTotal
     * @param float $baseRowTotalInclTax
     * @param float $baseDiscountAmount
     * @param float $baseAwRewardPintsAmount
     * @param float $qty
     * @return ItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getItemMock(
        $productId,
        $baseRowTotal,
        $baseRowTotalInclTax,
        $baseDiscountAmount,
        $baseAwRewardPintsAmount,
        $qty
    ) {
        $itemMock = $this->createMock(ItemInterface::class);
        $itemMock->expects($this->any())
            ->method('getProductId')
            ->willReturn($productId);
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

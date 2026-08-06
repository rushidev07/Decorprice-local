<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor;

use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor\ItemGroupConverterInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor\QuoteProcessor;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Test for \Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor\QuoteProcessor
 */
class QuoteProcessorTest extends TestCase
{
    /**
     * @var QuoteProcessor
     */
    private $processor;

    /**
     * @var ItemGroupConverterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemGroupConverterMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->itemGroupConverterMock = $this->createMock(ItemGroupConverterInterface::class);

        $this->processor = $objectManager->getObject(
            QuoteProcessor::class,
            [
                'itemGroupConverter' => $this->itemGroupConverterMock,
            ]
        );
    }

    /**
     * Test getItemGroups method
     *
     * @param QuoteItem[]|\PHPUnit_Framework_MockObject_MockObject[] $quoteItems
     * @param array $quoteItemGroups
     * @param array $itemGroups
     * @dataProvider getItemGroupsDataProvider
     */
    public function testGetItemGroups($quoteItems, $quoteItemGroups, $itemGroups)
    {
        $quoteMock = $this->createMock(Quote::class);
        $quoteMock->expects($this->once())
            ->method('getAllItems')
            ->willReturn($quoteItems);

        $this->itemGroupConverterMock->expects($this->once())
            ->method('convert')
            ->with($quoteItemGroups)
            ->willReturn($itemGroups);

        $this->assertEquals($itemGroups, $this->processor->getItemGroups($quoteMock));
    }

    /**
     * @return array
     */
    public function getItemGroupsDataProvider()
    {
        $simpleMock = $this->getQuoteItemMock(10, null, false);
        $configurableParentMock = $this->getQuoteItemMock(11, null, true);
        $configurableChildMock = $this->getQuoteItemMock(12, 11, false);

        return [
            [
                'quoteItems' => [
                    $simpleMock,
                ],
                'quoteItemGroups' => [
                    10 => [
                        10 => $simpleMock
                    ]
                ],
                'itemGroups' => [
                    [$this->createMock(ItemInterface::class)]
                ]
            ],
            [
                'quoteItems' => [
                    $configurableParentMock,
                    $configurableChildMock
                ],
                'quoteItemGroups' => [
                    11 => [
                        11 => $configurableParentMock,
                        12 => $configurableChildMock
                    ]
                ],
                'itemGroups' => [
                    [
                        $this->createMock(ItemInterface::class),
                        $this->createMock(ItemInterface::class)
                    ]
                ]
            ],
            [
                'quoteItems' => [
                    $simpleMock,
                    $configurableParentMock,
                    $configurableChildMock
                ],
                'quoteItemGroups' => [
                    10 => [
                        10 => $simpleMock
                    ],
                    11 => [
                        11 => $configurableParentMock,
                        12 => $configurableChildMock
                    ]
                ],
                'itemGroups' => [
                    [$this->createMock(ItemInterface::class)],
                    [
                        $this->createMock(ItemInterface::class),
                        $this->createMock(ItemInterface::class)
                    ]
                ]
            ]
        ];
    }

    /**
     * Get quote item mock
     *
     * @param int $itemId
     * @param int|null $parentItemId
     * @param bool $isChildrenCalculated
     * @return QuoteItem|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getQuoteItemMock($itemId, $parentItemId, $isChildrenCalculated)
    {
        $quoteItemMock = $this->getMockBuilder(QuoteItem::class)
                              ->disableOriginalConstructor()
                              ->setMethods(['getItemId','getParentItemId', 'isChildrenCalculated', 'setIsChildrenCalculated'])
                              ->getMock();
        $quoteItemMock->expects($this->any())
            ->method('getItemId')
            ->willReturn($itemId);
        $quoteItemMock->expects($this->any())
            ->method('getParentItemId')
            ->willReturn($parentItemId);
        $quoteItemMock->expects($this->any())
            ->method('isChildrenCalculated')
            ->willReturn($isChildrenCalculated);
        $quoteItemMock->expects($this->any())
            ->method('setIsChildrenCalculated')
            ->with($isChildrenCalculated)
            ->willReturnSelf();

        return $quoteItemMock;
    }
}

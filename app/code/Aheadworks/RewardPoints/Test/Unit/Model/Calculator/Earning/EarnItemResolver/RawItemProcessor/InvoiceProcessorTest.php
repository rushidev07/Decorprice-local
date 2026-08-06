<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor;

use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor\InvoiceProcessor;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor\InvoiceItemsResolver;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor\ItemGroupConverterInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;

/**
 * Test for \Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor\InvoiceProcessor
 */
class InvoiceProcessorTest extends TestCase
{
    /**
     * @var InvoiceProcessor
     */
    private $processor;

    /**
     * @var InvoiceItemsResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $invoiceItemsResolverMock;

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

        $this->invoiceItemsResolverMock = $this->createMock(InvoiceItemsResolver::class);
        $this->itemGroupConverterMock = $this->createMock(ItemGroupConverterInterface::class);

        $this->processor = $objectManager->getObject(
            InvoiceProcessor::class,
            [
                'invoiceItemsResolver' => $this->invoiceItemsResolverMock,
                'itemGroupConverter' => $this->itemGroupConverterMock,
            ]
        );
    }

    /**
     * Test getItemGroups method
     *
     * @param InvoiceItem[]|\PHPUnit_Framework_MockObject_MockObject[] $invoiceItems
     * @param array $invoiceItemGroups
     * @param array $itemGroups
     * @dataProvider getItemGroupsDataProvider
     */
    public function testGetItemGroups($invoiceItems, $invoiceItemGroups, $itemGroups)
    {
        $invoiceMock = $this->createMock(InvoiceInterface::class);
        $this->invoiceItemsResolverMock->expects($this->once())
            ->method('getItems')
            ->with($invoiceMock)
            ->willReturn($invoiceItems);

        $this->itemGroupConverterMock->expects($this->once())
            ->method('convert')
            ->with($invoiceItemGroups)
            ->willReturn($itemGroups);

        $this->assertEquals($itemGroups, $this->processor->getItemGroups($invoiceMock));
    }

    /**
     * @return array
     */
    public function getItemGroupsDataProvider()
    {
        $simpleMock = $this->getInvoiceItemMock(10, null);
        $configurableParentMock = $this->getInvoiceItemMock(11, null);
        $configurableChildMock = $this->getInvoiceItemMock(12, 11);

        return [
            [
                'invoiceItems' => [
                    $simpleMock,
                ],
                'invoiceItemGroups' => [
                    10 => [
                        10 => $simpleMock
                    ]
                ],
                'itemGroups' => [
                    [$this->createMock(ItemInterface::class)]
                ]
            ],
            [
                'invoiceItems' => [
                    $configurableParentMock,
                    $configurableChildMock
                ],
                'invoiceItemGroups' => [
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
                'invoiceItems' => [
                    $simpleMock,
                    $configurableParentMock,
                    $configurableChildMock
                ],
                'invoiceItemGroups' => [
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
     * Get invoice item mock
     *
     * @param int $itemId
     * @param int|null $parentItemId
     * @return InvoiceItem|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getInvoiceItemMock($itemId, $parentItemId)
    {
        $invoiceItemMock = $this->getMockBuilder(InvoiceItem::class)
                                ->disableOriginalConstructor()
                                ->setMethods(['getItemId', 'getParentItemId'])
                                ->getMock();
        $invoiceItemMock->expects($this->any())
            ->method('getItemId')
            ->willReturn($itemId);
        $invoiceItemMock->expects($this->any())
            ->method('getParentItemId')
            ->willReturn($parentItemId);

        return $invoiceItemMock;
    }
}

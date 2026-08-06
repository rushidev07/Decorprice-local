<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor;

use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor\CreditmemoProcessor;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor\CreditmemoItemsResolver;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor\ItemGroupConverterInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Model\Order\Creditmemo\Item as CreditmemoItem;

/**
 * Test for \Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor\CreditmemoProcessor
 */
class CreditmemoProcessorTest extends TestCase
{
    /**
     * @var CreditmemoProcessor
     */
    private $processor;

    /**
     * @var CreditmemoItemsResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditmemoItemsResolverMock;

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

        $this->creditmemoItemsResolverMock = $this->createMock(CreditmemoItemsResolver::class);
        $this->itemGroupConverterMock = $this->createMock(ItemGroupConverterInterface::class);

        $this->processor = $objectManager->getObject(
            CreditmemoProcessor::class,
            [
                'creditmemoItemsResolver' => $this->creditmemoItemsResolverMock,
                'itemGroupConverter' => $this->itemGroupConverterMock,
            ]
        );
    }

    /**
     * Test getItemGroups method
     *
     * @param CreditmemoItem[]|\PHPUnit_Framework_MockObject_MockObject[] $creditmemoItems
     * @param array $creditmemoItemGroups
     * @param array $itemGroups
     * @dataProvider getItemGroupsDataProvider
     */
    public function testGetItemGroups($creditmemoItems, $creditmemoItemGroups, $itemGroups)
    {
        $creditmemoMock = $this->createMock(CreditmemoInterface::class);
        $this->creditmemoItemsResolverMock->expects($this->once())
            ->method('getItems')
            ->with($creditmemoMock)
            ->willReturn($creditmemoItems);

        $this->itemGroupConverterMock->expects($this->once())
            ->method('convert')
            ->with($creditmemoItemGroups)
            ->willReturn($itemGroups);

        $this->assertEquals($itemGroups, $this->processor->getItemGroups($creditmemoMock));
    }

    /**
     * @return array
     */
    public function getItemGroupsDataProvider()
    {
        $simpleMock = $this->getCreditmemoItemMock(10, null);
        $configurableParentMock = $this->getCreditmemoItemMock(11, null);
        $configurableChildMock = $this->getCreditmemoItemMock(12, 11);

        return [
            [
                'creditmemoItems' => [
                    $simpleMock,
                ],
                'creditmemoItemGroups' => [
                    10 => [
                        10 => $simpleMock
                    ]
                ],
                'itemGroups' => [
                    [$this->createMock(ItemInterface::class)]
                ]
            ],
            [
                'creditmemoItems' => [
                    $configurableParentMock,
                    $configurableChildMock
                ],
                'creditmemoItemGroups' => [
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
                'creditmemoItems' => [
                    $simpleMock,
                    $configurableParentMock,
                    $configurableChildMock
                ],
                'creditmemoItemGroups' => [
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
     * Get creditmemo item mock
     *
     * @param int $itemId
     * @param int|null $parentItemId
     * @return CreditmemoItem|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getCreditmemoItemMock($itemId, $parentItemId)
    {
        $creditmemoItemMock = $this->getMockBuilder(CreditmemoItem::class)
                                   ->disableOriginalConstructor()
                                   ->setMethods(['getItemId', 'getParentItemId'])
                                   ->getMock();
        $creditmemoItemMock->expects($this->any())
            ->method('getItemId')
            ->willReturn($itemId);
        $creditmemoItemMock->expects($this->any())
            ->method('getParentItemId')
            ->willReturn($parentItemId);

        return $creditmemoItemMock;
    }
}

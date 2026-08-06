<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor;

use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor\InvoiceItemsResolver;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor\OrderItemsResolver;
use Magento\Sales\Model\Order\Item;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * Test for \Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor\InvoiceItemsResolver
 */
class InvoiceItemsResolverTest extends TestCase
{
    /**
     * @var InvoiceItemsResolver
     */
    private $resolver;

    /**
     * @var OrderItemsResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderItemsResolverMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->orderItemsResolverMock = $this->createMock(OrderItemsResolver::class);

        $this->resolver = $objectManager->getObject(
            InvoiceItemsResolver::class,
            [
                'orderItemsResolver' => $this->orderItemsResolverMock,
            ]
        );
    }

    /**
     * Test getItems method
     *
     * @param OrderItemInterface[]|\PHPUnit_Framework_MockObject_MockObject[] $orderItems
     * @param InvoiceItem[]|\PHPUnit_Framework_MockObject_MockObject[] $invoiceItems
     * @param InvoiceItem[]|\PHPUnit_Framework_MockObject_MockObject[] $resultItems
     * @dataProvider getItemsDataProvider
     */
    public function testGetItems($orderItems, $invoiceItems, $resultItems)
    {
        $orderId = 10;
        $invoiceMock = $this->createMock(InvoiceInterface::class);
        $invoiceMock->expects($this->once())
            ->method('getOrderId')
            ->willReturn($orderId);

        if (!empty($orderItems)) {
            $invoiceMock->expects($this->once())
                ->method('getItems')
                ->willReturn($invoiceItems);

            $this->orderItemsResolverMock->expects($this->once())
                ->method('getOrderItems')
                ->with($orderId)
                ->willReturn($orderItems);
        }

        $this->assertEquals($resultItems, $this->resolver->getItems($invoiceMock));
    }

    /**
     * @return array
     */
    public function getItemsDataProvider()
    {
        $simpleOrderMock = $this->getOrderItemMock(null, 'simple', false);
        $parentOrderItemMock = $this->getOrderItemMock(null, 'configurable', true);
        $childOrderItemMock = $this->getOrderItemMock(11, 'simple', false);

        $simpleInvoiceItemMock = $this->getInvoiceItemMock(20, 10, null, 'simple', false);
        $parentInvoiceItemMock = $this->getInvoiceItemMock(21, 11, null, 'configurable', true);
        $childInvoiceItemMock = $this->getInvoiceItemMock(22, 12, 21, 'simple', false);

        return [
            [
                'orderItems' => [
                    11 => $parentOrderItemMock,
                    12 => $childOrderItemMock,
                    10 => $simpleOrderMock
                ],
                'invoiceItems' => [
                    $parentInvoiceItemMock,
                    $childInvoiceItemMock,
                    $simpleInvoiceItemMock
                ],
                'resultItems' => [
                    21 => $parentInvoiceItemMock,
                    22 => $childInvoiceItemMock,
                    20 => $simpleInvoiceItemMock
                ]
            ],
            [
                'orderItems' => [],
                'invoiceItems' => [
                    $parentInvoiceItemMock,
                    $childInvoiceItemMock
                ],
                'resultItems' => []
            ]
        ];
    }

    /**
     * Get order item mock
     *
     * @param int|null $parentItemId
     * @param string $productType
     * @param bool $isChildrenCalculated
     * @return OrderItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getOrderItemMock($parentItemId, $productType, $isChildrenCalculated)
    {
        $orderItemMock = $this->createMock(Item::class);
        $orderItemMock->expects($this->any())
            ->method('getParentItemId')
            ->willReturn($parentItemId);
        $orderItemMock->expects($this->any())
            ->method('getProductType')
            ->willReturn($productType);
        $orderItemMock->expects($this->any())
            ->method('isChildrenCalculated')
            ->willReturn($isChildrenCalculated);

        return $orderItemMock;
    }

    /**
     * Get invoice item mock
     *
     * @param int $id
     * @param int $orderItemId
     * @param int|null $parentItemId
     * @param string $productType
     * @param bool $isChildrenCalculated
     * @return InvoiceItem|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getInvoiceItemMock($id, $orderItemId, $parentItemId, $productType, $isChildrenCalculated)
    {
        $invoiceItemMock = $this->createPartialMock(
            InvoiceItem::class,
            [
                'getEntityId',
                'getOrderItemId',
                'setItemId',
                'setParentItemId',
                'setProductType',
                'setIsChildrenCalculated'
            ]
        );
        $invoiceItemMock->expects($this->any())
            ->method('getEntityId')
            ->willReturn($id);
        $invoiceItemMock->expects($this->any())
            ->method('getOrderItemId')
            ->willReturn($orderItemId);
        $invoiceItemMock->expects($this->once())
            ->method('setItemId')
            ->with($id)
            ->willReturnSelf();
        $invoiceItemMock->expects($this->once())
            ->method('setParentItemId')
            ->with($parentItemId)
            ->willReturnSelf();
        $invoiceItemMock->expects($this->once())
            ->method('setProductType')
            ->with($productType)
            ->willReturnSelf();
        $invoiceItemMock->expects($this->once())
            ->method('setIsChildrenCalculated')
            ->with($isChildrenCalculated)
            ->willReturnSelf();

        return $invoiceItemMock;
    }
}

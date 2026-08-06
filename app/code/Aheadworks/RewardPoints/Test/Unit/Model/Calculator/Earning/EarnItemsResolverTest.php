<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Calculator\Earning;

use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemProcessorInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor\TypeProcessorInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor\CreditmemoProcessor;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor\InvoiceProcessor;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemsResolver;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemProcessor;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor\QuoteProcessor;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\RewardPoints\Model\Calculator\Earning\RuleApplier
 */
class EarnItemsResolverTest extends TestCase
{
    /**
     * @var EarnItemsResolver
     */
    private $resolver;

    /**
     * @var QuoteProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteProcessorMock;

    /**
     * @var InvoiceProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $invoiceProcessorMock;

    /**
     * @var CreditmemoProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditmemoProcessorMock;

    /**
     * @var ProductProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productProcessorMock;

    /**
     * @var ItemProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemProcessorMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->quoteProcessorMock = $this->createMock(QuoteProcessor::class);
        $this->invoiceProcessorMock = $this->createMock(InvoiceProcessor::class);
        $this->creditmemoProcessorMock = $this->createMock(CreditmemoProcessor::class);
        $this->productProcessorMock = $this->createMock(ProductProcessor::class);
        $this->itemProcessorMock = $this->createMock(ItemProcessor::class);

        $this->resolver = $objectManager->getObject(
            EarnItemsResolver::class,
            [
                'quoteProcessor' => $this->quoteProcessorMock,
                'invoiceProcessor' => $this->invoiceProcessorMock,
                'creditmemoProcessor' => $this->creditmemoProcessorMock,
                'productProcessor' => $this->productProcessorMock,
                'itemProcessor' => $this->itemProcessorMock,
            ]
        );
    }

    /**
     * Test getItemsByQuote method
     */
    public function testGetItemsByQuote()
    {
        $quoteMock = $this->createMock(Quote::class);
        $beforeTax = false;

        $groupSimple = [$this->createMock(ItemInterface::class)];
        $groupConfigurable = [
            $this->createMock(ItemInterface::class),
            $this->createMock(ItemInterface::class),
        ];

        $itemGroups = [
            $groupSimple,
            $groupConfigurable,
        ];

        $this->quoteProcessorMock->expects($this->once())
            ->method('getItemGroups')
            ->with($quoteMock)
            ->willReturn($itemGroups);

        $earnItemFirstMock = $this->createMock(EarnItemInterface::class);
        $earnItemSecondMock = $this->createMock(EarnItemInterface::class);
        $earnItems = [$earnItemFirstMock, $earnItemSecondMock];

        $this->itemProcessorMock->expects($this->exactly(2))
            ->method('getEarnItem')
            ->withConsecutive([$groupSimple, $beforeTax], [$groupConfigurable, $beforeTax])
            ->willReturnOnConsecutiveCalls($earnItemFirstMock, $earnItemSecondMock);

        $this->assertEquals($earnItems, $this->resolver->getItemsByQuote($quoteMock, $beforeTax));
    }

    /**
     * Test getItemsByQuote method if an exception occurs
     */
    public function testGetItemsByQuoteException()
    {
        $quoteMock = $this->createMock(Quote::class);
        $beforeTax = false;

        $groupSimple = [$this->createMock(ItemInterface::class)];

        $itemGroups = [$groupSimple,];

        $this->quoteProcessorMock->expects($this->once())
            ->method('getItemGroups')
            ->with($quoteMock)
            ->willReturn($itemGroups);

        $this->itemProcessorMock->expects($this->once())
            ->method('getEarnItem')
            ->with($groupSimple, $beforeTax)
            ->willThrowException(
                new ConfigurationMismatchException(
                    __('Item processor must implements %1', ItemProcessorInterface::class)
                )
            );

        $this->expectException(ConfigurationMismatchException::class);

        $this->resolver->getItemsByQuote($quoteMock, $beforeTax);
    }

    /**
     * Test getItemsByInvoice method
     */
    public function testGetItemsByInvoice()
    {
        $invoiceMock = $this->createMock(InvoiceInterface::class);
        $beforeTax = false;

        $groupSimple = [$this->createMock(ItemInterface::class)];
        $groupConfigurable = [
            $this->createMock(ItemInterface::class),
            $this->createMock(ItemInterface::class),
        ];

        $itemGroups = [
            $groupSimple,
            $groupConfigurable,
        ];

        $this->invoiceProcessorMock->expects($this->once())
            ->method('getItemGroups')
            ->with($invoiceMock)
            ->willReturn($itemGroups);

        $earnItemFirstMock = $this->createMock(EarnItemInterface::class);
        $earnItemSecondMock = $this->createMock(EarnItemInterface::class);
        $earnItems = [$earnItemFirstMock, $earnItemSecondMock];

        $this->itemProcessorMock->expects($this->exactly(2))
            ->method('getEarnItem')
            ->withConsecutive([$groupSimple, $beforeTax], [$groupConfigurable, $beforeTax])
            ->willReturnOnConsecutiveCalls($earnItemFirstMock, $earnItemSecondMock);

        $this->assertEquals($earnItems, $this->resolver->getItemsByInvoice($invoiceMock, $beforeTax));
    }

    /**
     * Test getItemsByInvoice method if an exception occurs
     */
    public function testGetItemsByInvoiceException()
    {
        $invoiceMock = $this->createMock(InvoiceInterface::class);
        $beforeTax = false;

        $groupSimple = [$this->createMock(ItemInterface::class)];

        $itemGroups = [$groupSimple];

        $this->invoiceProcessorMock->expects($this->once())
            ->method('getItemGroups')
            ->with($invoiceMock)
            ->willReturn($itemGroups);

        $this->itemProcessorMock->expects($this->once())
            ->method('getEarnItem')
            ->with($groupSimple, $beforeTax)
            ->willThrowException(
                new ConfigurationMismatchException(
                    __('Item processor must implements %1', ItemProcessorInterface::class)
                )
            );

        $this->expectException(ConfigurationMismatchException::class);

        $this->resolver->getItemsByInvoice($invoiceMock, $beforeTax);
    }

    /**
     * Test getItemsByCreditmemo method
     */
    public function testGetItemsByCreditmemo()
    {
        $creditmemoMock = $this->createMock(CreditmemoInterface::class);
        $beforeTax = false;

        $groupSimple = [$this->createMock(ItemInterface::class)];
        $groupConfigurable = [
            $this->createMock(ItemInterface::class),
            $this->createMock(ItemInterface::class),
        ];

        $itemGroups = [
            $groupSimple,
            $groupConfigurable,
        ];

        $this->creditmemoProcessorMock->expects($this->once())
            ->method('getItemGroups')
            ->with($creditmemoMock)
            ->willReturn($itemGroups);

        $earnItemFirstMock = $this->createMock(EarnItemInterface::class);
        $earnItemSecondMock = $this->createMock(EarnItemInterface::class);
        $earnItems = [$earnItemFirstMock, $earnItemSecondMock];

        $this->itemProcessorMock->expects($this->exactly(2))
            ->method('getEarnItem')
            ->withConsecutive([$groupSimple, $beforeTax], [$groupConfigurable, $beforeTax])
            ->willReturnOnConsecutiveCalls($earnItemFirstMock, $earnItemSecondMock);

        $this->assertEquals($earnItems, $this->resolver->getItemsByCreditmemo($creditmemoMock, $beforeTax));
    }

    /**
     * Test getItemsByCreditmemo method if an exception occurs
     */
    public function testGetItemsByCreditmemoException()
    {
        $creditmemoMock = $this->createMock(CreditmemoInterface::class);
        $beforeTax = false;

        $groupSimple = [$this->createMock(ItemInterface::class)];

        $itemGroups = [$groupSimple];

        $this->creditmemoProcessorMock->expects($this->once())
            ->method('getItemGroups')
            ->with($creditmemoMock)
            ->willReturn($itemGroups);

        $this->itemProcessorMock->expects($this->once())
            ->method('getEarnItem')
            ->with($groupSimple, $beforeTax)
            ->willThrowException(
                new ConfigurationMismatchException(
                    __('Item processor must implements %1', ItemProcessorInterface::class)
                )
            );

        $this->expectException(ConfigurationMismatchException::class);

        $this->resolver->getItemsByCreditmemo($creditmemoMock, $beforeTax);
    }

    /**
     * Test getItemsByProduct method
     */
    public function testGetItemsByProduct()
    {
        $productMock = $this->createMock(Product::class);
        $beforeTax = false;

        $earnItemFirstMock = $this->createMock(EarnItemInterface::class);
        $earnItemSecondMock = $this->createMock(EarnItemInterface::class);
        $earnItems = [$earnItemFirstMock, $earnItemSecondMock];

        $this->productProcessorMock->expects($this->once())
            ->method('getEarnItems')
            ->with($productMock, $beforeTax)
            ->willReturn($earnItems);

        $this->assertEquals($earnItems, $this->resolver->getItemsByProduct($productMock, $beforeTax));
    }

    /**
     * Test getItemsByProduct method if an exception occurs
     */
    public function testGetItemsByProductException()
    {
        $productMock = $this->createMock(Product::class);
        $beforeTax = false;

        $this->productProcessorMock->expects($this->once())
            ->method('getEarnItems')
            ->with($productMock, $beforeTax)
            ->willThrowException(
                new ConfigurationMismatchException(
                    __('Type processor must implements %1', TypeProcessorInterface::class)
                )
            );

        $this->expectException(ConfigurationMismatchException::class);

        $this->resolver->getItemsByProduct($productMock, $beforeTax);
    }
}

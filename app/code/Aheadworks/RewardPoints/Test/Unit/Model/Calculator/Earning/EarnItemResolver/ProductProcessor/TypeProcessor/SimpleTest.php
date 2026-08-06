<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Calculator\Earning\EarnItemResolver\ProductProcessor\TypeProcessor;

use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor\TypeProcessor\Simple;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor\CatalogPriceCalculator;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemInterfaceFactory;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor\TypeProcessor\Simple
 */
class SimpleTest extends TestCase
{
    /**
     * @var Simple
     */
    private $typeProcessor;

    /**
     * @var EarnItemInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $earnItemFactoryMock;

    /**
     * @var CatalogPriceCalculator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $catalogPriceCalculatorMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->earnItemFactoryMock = $this->createMock(EarnItemInterfaceFactory::class);
        $this->catalogPriceCalculatorMock = $this->createMock(CatalogPriceCalculator::class);

        $this->typeProcessor = $objectManager->getObject(
            Simple::class,
            [
                'earnItemFactory' => $this->earnItemFactoryMock,
                'catalogPriceCalculator' => $this->catalogPriceCalculatorMock,
            ]
        );
    }

    /**
     * Test getEarnItems method
     *
     * @param bool $beforeTax
     * @dataProvider getEarnItemsDataProvider
     */
    public function testGetEarnItems($beforeTax)
    {
        $productId = 125;
        $price = 55.5;
        $resultPrice = 40.66;
        $productMock = $this->getProductMock($productId, $price);
        $earnItemMock = $this->getEarnItemMock($productId, $resultPrice);
        $earnItems = [$earnItemMock];

        $this->catalogPriceCalculatorMock->expects($this->once())
            ->method('getFinalPriceAmount')
            ->with($productMock, $price, $beforeTax)
            ->willReturn($resultPrice);

        $this->earnItemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($earnItemMock);

        $this->assertEquals($earnItems, $this->typeProcessor->getEarnItems($productMock, $beforeTax));
    }

    /**
     * @return array
     */
    public function getEarnItemsDataProvider()
    {
        return [
            ['beforeTax' => true],
            ['beforeTax' => false],
        ];
    }

    /**
     * Get product mock
     *
     * @param int $productId
     * @param float $price
     * @return Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getProductMock($productId, $price)
    {
        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $productMock->expects($this->once())
            ->method('getFinalPrice')
            ->willReturn($price);

        return $productMock;
    }

    /**
     * Get earn item mock
     *
     * @param int $productId
     * @param float $price
     * @return EarnItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getEarnItemMock($productId, $price)
    {
        $earnItemMock = $this->createMock(EarnItemInterface::class);
        $earnItemMock->expects($this->once())
            ->method('setProductId')
            ->with($productId)
            ->willReturnSelf();
        $earnItemMock->expects($this->once())
            ->method('setBaseAmount')
            ->with($price)
            ->willReturnSelf();
        $earnItemMock->expects($this->once())
            ->method('setQty')
            ->with(1)
            ->willReturnSelf();

        return $earnItemMock;
    }
}

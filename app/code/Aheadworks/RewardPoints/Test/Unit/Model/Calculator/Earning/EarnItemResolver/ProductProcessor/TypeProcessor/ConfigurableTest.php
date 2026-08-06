<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Calculator\Earning\EarnItemResolver\ProductProcessor\TypeProcessor;

use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor\TypeProcessor\Configurable;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor\CatalogPriceCalculator;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemInterfaceFactory;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;

/**
 * Test for
 * \Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor\TypeProcessor\Configurable
 */
class ConfigurableTest extends TestCase
{
    /**
     * @var Configurable
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
            Configurable::class,
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
        $parentProductId = 125;
        $parenPrice = 55.5;

        $childFirstProductId = 126;
        $childFirstPrice = 55.5;
        $childFirstResultPrice = 57.57;

        $childSecondProductId = 127;
        $childSecondPrice = 88.8;
        $childSecondResultPrice = 89.89;

        $configurableTypeMock = $this->createMock(ConfigurableProductType::class);

        $parentProductMock = $this->getProductMock($parentProductId, $parenPrice, $configurableTypeMock);
        $childFirstProductMock = $this->getProductMock($childFirstProductId, $childFirstPrice);
        $childSecondProductMock = $this->getProductMock($childSecondProductId, $childSecondPrice);
        $childProducts = [$childFirstProductMock, $childSecondProductMock];

        $earnItemFirstMock = $this->getEarnItemMock($childFirstProductId, $childFirstResultPrice);
        $earnItemSecondMock = $this->getEarnItemMock($childSecondProductId, $childSecondResultPrice);
        $earnItems = [$earnItemFirstMock, $earnItemSecondMock];

        $configurableTypeMock->expects($this->once())
            ->method('getUsedProducts')
            ->with($parentProductMock)
            ->willReturn($childProducts);

        $this->catalogPriceCalculatorMock->expects($this->exactly(2))
            ->method('getFinalPriceAmount')
            ->withConsecutive(
                [$childFirstProductMock, $childFirstPrice, $beforeTax],
                [$childSecondProductMock, $childSecondPrice, $beforeTax]
            )
            ->willReturnOnConsecutiveCalls($childFirstResultPrice, $childSecondResultPrice);

        $this->earnItemFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturn($earnItemFirstMock, $earnItemSecondMock);

        $this->assertEquals($earnItems, $this->typeProcessor->getEarnItems($parentProductMock, $beforeTax));
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
     * @param ConfigurableProductType|\PHPUnit_Framework_MockObject_MockObject|null $type
     * @return Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getProductMock($productId, $price, $type = null)
    {
        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->any())
            ->method('getId')
            ->willReturn($productId);
        $productMock->expects($this->any())
            ->method('getFinalPrice')
            ->willReturn($price);

        if ($type) {
            $productMock->expects($this->any())
                ->method('getTypeInstance')
                ->willReturn($type);
        }

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

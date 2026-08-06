<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Calculator\Earning\EarnItemResolver\ProductProcessor\TypeProcessor;

use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor\TypeProcessor\Bundle;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor\CatalogPriceCalculator;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemInterfaceFactory;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Bundle\Pricing\Price\FinalPrice;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;

/**
 * Test for
 * \Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor\TypeProcessor\Bundle
 */
class BundleTest extends TestCase
{
    /**
     * @var Bundle
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
            Bundle::class,
            [
                'earnItemFactory' => $this->earnItemFactoryMock,
                'catalogPriceCalculator' => $this->catalogPriceCalculatorMock,
            ]
        );
    }

    /**
     * Test getEarnItems method
     *
     * @param int $productId
     * @param float $price
     * @param int $calculateParent
     * @param bool $beforeTax
     * @param float $resultPrice
     * @dataProvider getEarnItemsDataProvider
     */
    public function testGetEarnItems($productId, $price, $calculateParent, $beforeTax, $resultPrice)
    {
        $priceInfo = ($calculateParent == AbstractType::CALCULATE_CHILD);
        $productMock = $this->getProductMock($productId, $price, $priceInfo, $calculateParent, $beforeTax);

        if (!$priceInfo) {
            $this->catalogPriceCalculatorMock->expects($this->once())
                ->method('getFinalPriceAmount')
                ->with($productMock, $price, $beforeTax)
                ->willReturn($resultPrice);
        }

        $earnItemMock = $this->getEarnItemMock($productId, $resultPrice);
        $this->earnItemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($earnItemMock);

        $earnItems = [$earnItemMock];

        $this->assertEquals($earnItems, $this->typeProcessor->getEarnItems($productMock, $beforeTax));
    }

    /**
     * @return array
     */
    public function getEarnItemsDataProvider()
    {
        return [
            [
                'productId' => 125,
                'price' => 25,
                'calculateParent' => AbstractType::CALCULATE_PARENT,
                'beforeTax' => true,
                'resultPrice' => 30
            ],
            [
                'productId' => 125,
                'price' => 25,
                'calculateParent' => AbstractType::CALCULATE_PARENT,
                'beforeTax' => false,
                'resultPrice' => 30
            ],
            [
                'productId' => 125,
                'price' => 30,
                'calculateParent' => AbstractType::CALCULATE_CHILD,
                'beforeTax' => true,
                'resultPrice' => 30
            ],
            [
                'productId' => 125,
                'price' => 30,
                'calculateParent' => AbstractType::CALCULATE_CHILD,
                'beforeTax' => false,
                'resultPrice' => 30
            ],
        ];
    }

    /**
     * Get product mock
     *
     * @param int $productId
     * @param float $price
     * @param bool $priceInfo
     * @param int $priceType
     * @param bool $beforeTax
     * @return Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getProductMock($productId, $price, $priceInfo, $priceType, $beforeTax)
    {
        $productMock = $this->getMockBuilder(Product::class)
                            ->disableOriginalConstructor()
                            ->setMethods(['getId','getPriceType', 'getFinalPrice', 'getPriceInfo'])
                            ->getMock();
        $productMock->expects($this->any())
            ->method('getId')
            ->willReturn($productId);
        $productMock->expects($this->any())
            ->method('getPriceType')
            ->willReturn($priceType);

        if ($priceInfo) {
            $finalPriceMock = $this->createMock(FinalPrice::class);
            $priceInfoMock = $this->createMock(PriceInfoInterface::class);
            $priceInfoMock->expects($this->once())
                ->method('getPrice')
                ->with('final_price')
                ->willReturn($finalPriceMock);

            $productMock->expects($this->any())
                ->method('getPriceInfo')
                ->willReturn($priceInfoMock);

            $amountMock = $this->createMock(AmountInterface::class);
            $finalPriceMock->expects($this->once())
                ->method('getMaximalPrice')
                ->willReturn($amountMock);

            if ($beforeTax) {
                $amountMock->expects($this->once())
                    ->method('getValue')
                    ->with(['tax'])
                    ->willReturn($price);
            } else {
                $amountMock->expects($this->once())
                    ->method('getValue')
                    ->with(null)
                    ->willReturn($price);
            }
        } else {
            $productMock->expects($this->any())
                ->method('getFinalPrice')
                ->willReturn($price);
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

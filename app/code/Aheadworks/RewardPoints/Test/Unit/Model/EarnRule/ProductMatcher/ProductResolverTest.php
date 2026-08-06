<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\EarnRule\ProductMatcher;

use Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\ProductResolver;
use Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\ProductResolver\Pool;
use Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\ProductResolverInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\ConfigurationMismatchException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\ProductResolver
 */
class ProductResolverTest extends TestCase
{
    /**
     * @var ProductResolver
     */
    private $productResolver;

    /**
     * @var Pool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $poolMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->poolMock = $this->createMock(Pool::class);

        $this->productResolver = $objectManager->getObject(
            ProductResolver::class,
            [
                'pool' => $this->poolMock,
            ]
        );
    }

    /**
     * Test getProductsForValidation method
     */
    public function testGetProductsForValidation()
    {
        $productType = 'configurable';

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn($productType);
        $childProductMock = $this->createMock(Product::class);

        $productsForValidation = [$childProductMock];

        $resolverMock = $this->createMock(ProductResolverInterface::class);
        $this->poolMock->expects($this->once())
            ->method('getResolverByCode')
            ->with($productType)
            ->willReturn($resolverMock);

        $resolverMock->expects($this->once())
            ->method('getProductsForValidation')
            ->with($productMock)
            ->willReturn($productsForValidation);

        $this->assertEquals($productsForValidation, $this->productResolver->getProductsForValidation($productMock));
    }

    /**
     * Test getProductsForValidation method if an exception occurs
     */
    public function testGetProductsForValidationException()
    {
        $productType = 'configurable';

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn($productType);

        $this->poolMock->expects($this->once())
            ->method('getResolverByCode')
            ->with($productType)
            ->willThrowException(
                new ConfigurationMismatchException(
                    __('Product resolver must implements %1', ProductResolverInterface::class)
                )
            );

        $this->expectException(ConfigurationMismatchException::class);

        $this->productResolver->getProductsForValidation($productMock);
    }
}

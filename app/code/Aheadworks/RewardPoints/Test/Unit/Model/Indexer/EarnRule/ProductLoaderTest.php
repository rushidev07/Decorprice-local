<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Indexer\EarnRule;

use Aheadworks\RewardPoints\Model\Indexer\EarnRule\ProductLoader;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\RewardPoints\Model\Indexer\EarnRule
 */
class ProductLoaderTest extends TestCase
{
    /**
     * @var ProductLoader
     */
    private $model;

    /**
     * @var ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepositoryMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);

        $this->model = $objectManager->getObject(
            ProductLoader::class,
            [
                'productRepository' => $this->productRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
            ]
        );
    }

    /**
     * Test getProducts method
     */
    public function testGetProducts()
    {
        $productIds = [10, 20, 125];
        $productMock = $this->createMock(ProductInterface::class);
        $products = [$productMock];

        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->with('entity_id', $productIds, 'in')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $productSearchResultMock = $this->createMock(ProductSearchResultsInterface::class);
        $this->productRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($productSearchResultMock);

        $productSearchResultMock->expects($this->once())
            ->method('getItems')
            ->willReturn($products);

        $this->assertEquals($products, $this->model->getProducts($productIds));
    }
}

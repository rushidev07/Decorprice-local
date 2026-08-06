<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model;

use Aheadworks\RewardPoints\Api\Data\StorefrontLabelsInterface;
use Aheadworks\RewardPoints\Model\StorefrontLabels\ObjectResolver;
use Aheadworks\RewardPoints\Model\StorefrontLabelsResolver;
use Magento\Framework\Reflection\DataObjectProcessor;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;

/**
 * Class StorefrontLabelsResolverTest
 *
 * @package Aheadworks\RewardPoints\Test\Unit\Model
 */
class StorefrontLabelsResolverTest extends TestCase
{
    /**
     * @var StorefrontLabelsResolver
     */
    private $model;

    /**
     * @var ObjectResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectResolverMock;

    /**
     * @var DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectProcessorMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->objectResolverMock = $this->createMock(ObjectResolver::class);
        $this->dataObjectProcessorMock = $this->createMock(DataObjectProcessor::class);
        $this->model = $objectManager->getObject(
            StorefrontLabelsResolver::class,
            [
                'objectResolver' => $this->objectResolverMock,
                'dataObjectProcessor' => $this->dataObjectProcessorMock
            ]
        );
    }

    /**
     * Test getLabelsForStore method with set storeId
     */
    public function testGetLabelsForStoreWithSetStoreId()
    {
        $storeId = 1;
        $labelMock0 = $this->getMockForAbstractClass(StorefrontLabelsInterface::class);
        $labelMock0->expects($this->exactly(2))
            ->method('getStoreId')
            ->willReturn(Store::DEFAULT_STORE_ID);
        $labelMock1 = $this->getMockForAbstractClass(StorefrontLabelsInterface::class);
        $labelMock1->expects($this->exactly(2))
            ->method('getStoreId')
            ->willReturn($storeId);
        $labelsData = [$labelMock0, $labelMock1];

        $this->objectResolverMock->expects($this->exactly(2))
            ->method('resolve')
            ->withConsecutive(
                [$labelMock0],
                [$labelMock1]
            )->willReturnOnConsecutiveCalls(
                $labelMock0,
                $labelMock1
            );

        $this->assertSame($labelMock1, $this->model->getLabelsForStore($labelsData, $storeId));
    }

    /**
     * Test getLabelsForStore method with not exists storeId
     */
    public function testGetLabelsForStoreWithNotExistsStoreId()
    {
        $storeId = 1;
        $labelMock0 = $this->getDefaultStoreMock(2);
        $labelsData = [$labelMock0];

        $this->assertSame($labelMock0, $this->model->getLabelsForStore($labelsData, $storeId));
    }

    /**
     * Test getLabelsForStore method without storeId
     */
    public function testGetLabelsForStoreWithoutStoreId()
    {
        $labelMock0 = $this->getDefaultStoreMock(1);
        $labelsData = [$labelMock0];

        $this->assertSame($labelMock0, $this->model->getLabelsForStore($labelsData, null));
    }

    /**
     * Test getLabelsForStoreAsArray method
     */
    public function testGetLabelsForStoreAsArray()
    {
        $labelData0 = [
            StorefrontLabelsInterface::STORE_ID => Store::DEFAULT_STORE_ID,
            StorefrontLabelsInterface::PRODUCT_PROMO_TEXT => 'product promo text',
            StorefrontLabelsInterface::CATEGORY_PROMO_TEXT => 'category promo text'
        ];
        $labelsData = [$labelData0];
        $labelMock0 = $this->getMockForAbstractClass(StorefrontLabelsInterface::class);
        $labelMock0->expects($this->once())
            ->method('getStoreId')
            ->willReturn($labelData0[StorefrontLabelsInterface::STORE_ID]);

        $this->objectResolverMock->expects($this->once())
            ->method('resolve')
            ->with($labelData0)
            ->willReturn($labelMock0);

        $this->dataObjectProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($labelMock0, StorefrontLabelsInterface::class)
            ->willReturn($labelData0);

        $this->assertSame($labelData0, $this->model->getLabelsForStoreAsArray($labelsData, null));
    }

    /**
     * Retrieve default store mock
     *
     * @param int $exactly
     * @return StorefrontLabelsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getDefaultStoreMock($exactly)
    {
        $labelMock0 = $this->getMockForAbstractClass(StorefrontLabelsInterface::class);
        $labelMock0->expects($this->exactly($exactly))
            ->method('getStoreId')
            ->willReturn(Store::DEFAULT_STORE_ID);

        $this->objectResolverMock->expects($this->once())
            ->method('resolve')
            ->with($labelMock0)
            ->willReturn($labelMock0);

        return $labelMock0;
    }
}

<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model;

use Aheadworks\RewardPoints\Model\EarnRate;
use Aheadworks\RewardPoints\Model\EarnRateRepository;
use Aheadworks\RewardPoints\Model\ResourceModel\EarnRate as EarnRateResource;
use Aheadworks\RewardPoints\Api\Data\EarnRateInterface;
use Aheadworks\RewardPoints\Api\Data\EarnRateInterfaceFactory;
use Aheadworks\RewardPoints\Api\Data\EarnRateSearchResultsInterface;
use Aheadworks\RewardPoints\Api\Data\EarnRateSearchResultsInterfaceFactory;
use Aheadworks\RewardPoints\Model\ResourceModel\EarnRate\Collection as EarnRateCollection;
use Aheadworks\RewardPoints\Model\ResourceModel\EarnRate\CollectionFactory as EarnRateCollectionFactory;
use Aheadworks\RewardPoints\Model\Repository\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class Aheadworks\RewardPoints\Test\Unit\Model\EarnRateRepositoryTest
 */
class EarnRateRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EarnRateRepository
     */
    private $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EarnRateResource
     */
    private $resourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EntityManager
     */
    private $entityManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EarnRateInterfaceFactory
     */
    private $earnRateFactoryMock;

    /**
     * @var EarnRateSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $earnRateSearchResultsFactoryMock;

    /**
     * @var EarnRateCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $earnRateCollectionFactoryMock;

    /**
     * @var CollectionProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StoreManagerInterface
     */
    private $storeManagerMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->resourceMock = $this->getMockBuilder(EarnRateResource::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRateRowId'])
            ->getMock();

        $this->entityManagerMock = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['load', 'save', 'delete']
            )
            ->getMock();

        $this->earnRateFactoryMock = $this->getMockBuilder(EarnRateInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->earnRateSearchResultsFactoryMock = $this->createMock(EarnRateSearchResultsInterfaceFactory::class);
        $this->earnRateCollectionFactoryMock = $this->createMock(EarnRateCollectionFactory::class);
        $this->collectionProcessorMock = $this->createMock(CollectionProcessorInterface::class);

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore'])
            ->getMockForAbstractClass();

        $data = [
            'resource' => $this->resourceMock,
            'earnRateFactory' => $this->earnRateFactoryMock,
            'earnRateSearchResultsFactory' => $this->earnRateSearchResultsFactoryMock,
            'earnRateCollectionFactory' => $this->earnRateCollectionFactoryMock,
            'collectionProcessor' => $this->collectionProcessorMock,
            'entityManager' => $this->entityManagerMock,
            'storeManager' => $this->storeManagerMock,
        ];

        $this->object = $objectManager->getObject(EarnRateRepository::class, $data);
    }

    /**
     * Test get method
     */
    public function testGetMethod()
    {
        $customerGroupId = 1;
        $lifetimeSalesAmount = 100;
        $websiteId = 1;

        $rateId = 3;

        $earnRateModelMock = $this->getMockBuilder(EarnRateInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $earnRateModelMock->expects($this->once())
            ->method('getId')
            ->willReturn($rateId);

        $this->earnRateFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($earnRateModelMock);

        $this->resourceMock->expects($this->once())
            ->method('getRateRowId')
            ->with($customerGroupId, $lifetimeSalesAmount, $websiteId)
            ->willReturn($rateId);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($earnRateModelMock, $rateId)
            ->willReturn($earnRateModelMock);

        $this->object->get($customerGroupId, $lifetimeSalesAmount, $websiteId);
    }

    /**
     * Test get method for null website id param
     */
    public function testGetMethodNullWebsiteId()
    {
        $customerGroupId = 1;
        $lifetimeSalesAmount = 100;
        $websiteId = null;

        $rateId = 4;

        $earnRateModelMock = $this->getMockBuilder(EarnRateInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $earnRateModelMock->expects($this->once())
            ->method('getId')
            ->willReturn($rateId);

        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getWebsiteId'])
            ->getMockForAbstractClass();
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(1);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->earnRateFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($earnRateModelMock);

        $this->resourceMock->expects($this->once())
            ->method('getRateRowId')
            ->with($customerGroupId, $lifetimeSalesAmount, 1)
            ->willReturn($rateId);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($earnRateModelMock, $rateId)
            ->willReturn($earnRateModelMock);

        $this->object->get($customerGroupId, $lifetimeSalesAmount, $websiteId);
    }

    /**
     * Test getById method
     */
    public function testGetByIdMethod()
    {
        $rateId = 5;

        $earnRateModelMock = $this->getMockBuilder(EarnRateInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $earnRateModelMock->expects($this->once())
            ->method('getId')
            ->willReturn($rateId);

        $this->earnRateFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($earnRateModelMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($earnRateModelMock, $rateId)
            ->willReturn($earnRateModelMock);

        $expected = $this->object->getById($rateId);

        $this->assertSame($expected, $earnRateModelMock);
        $this->assertSame($expected, $this->object->getById($rateId));
    }

    /**
     * Test save method
     */
    public function testSaveMethod()
    {
        $rateId = 5;

        $earnRateModelMock = $this->getMockBuilder(EarnRateInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $earnRateModelMock->expects($this->once())
            ->method('getId')
            ->willReturn($rateId);

        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($earnRateModelMock)
            ->willReturn($earnRateModelMock);

        $expected = $this->object->save($earnRateModelMock);

        $this->assertSame($expected, $earnRateModelMock);
    }

    /**
     * Test delete method
     */
    public function testDeleteMethod()
    {
        $rateId = 5;

        $earnRateModelMock = $this->getMockBuilder(EarnRateInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $earnRateModelMock->expects($this->once())
            ->method('getId')
            ->willReturn($rateId);

        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($earnRateModelMock)
            ->willReturnSelf();

        $this->assertTrue($this->object->delete($earnRateModelMock));
    }

    /**
     * Test deleteById method
     */
    public function testDeleteByIdMethod()
    {
        $rateId = 5;

        $earnRateModelMock = $this->getMockBuilder(EarnRateInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();

        $earnRateModelMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($rateId);

        $this->earnRateFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($earnRateModelMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($earnRateModelMock, $rateId)
            ->willReturn($earnRateModelMock);

        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($earnRateModelMock)
            ->willReturnSelf();

        $this->assertTrue($this->object->deleteById($rateId));
    }

    /**
     * Test getList method
     *
     * @param bool $error
     * @dataProvider getListDataProvider
     */
    public function testGetList($error)
    {
        $collectionSize = 1;
        $rateId = 10;

        $searchCriteriaMock = $this->createMock(SearchCriteriaInterface::class);

        $collectionMock = $this->createMock(EarnRateCollection::class);
        $collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn($collectionSize);
        $this->earnRateCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $this->collectionProcessorMock->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $collectionMock);

        $searchResultsMock = $this->createMock(EarnRateSearchResultsInterface::class);
        $searchResultsMock->expects($this->atLeastOnce())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock)
            ->willReturnSelf();
        $searchResultsMock->expects($this->atLeastOnce())
            ->method('setTotalCount')
            ->with($collectionSize)
            ->willReturnSelf();
        $this->earnRateSearchResultsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchResultsMock);

        $rateModelMock = $this->createMock(EarnRate::class);
        $rateModelMock->expects($this->once())
            ->method('getId')
            ->willReturn($rateId);
        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$rateModelMock]);

        $rateMock = $this->createMock(EarnRateInterface::class);
        if ($error) {
            $rateMock->expects($this->atLeastOnce())
                ->method('getId')
                ->willReturn(null);
        } else {
            $rateMock->expects($this->atLeastOnce())
                ->method('getId')
                ->willReturn($rateId);
        }

        $this->earnRateFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($rateMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($rateMock, $rateId)
            ->willReturn($rateMock);

        try {
            if ($error) {
                $this->object->getList($searchCriteriaMock);
            } else {
                $searchResultsMock->expects($this->once())
                    ->method('setItems')
                    ->with([$rateMock])
                    ->willReturnSelf();

                $this->assertSame($searchResultsMock, $this->object->getList($searchCriteriaMock));
            }
        } catch (LocalizedException $e) {
            $this->assertEquals('No such entity with id = 10', $e->getMessage());
        }
    }

    /**
     * @return array
     */
    public function getListDataProvider()
    {
        return [
            ['error' => false],
            ['error' => true]
        ];
    }
}

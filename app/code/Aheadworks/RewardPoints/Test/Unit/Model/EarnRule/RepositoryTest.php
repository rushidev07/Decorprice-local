<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\EarnRule;

use Aheadworks\RewardPoints\Model\EarnRule;
use Aheadworks\RewardPoints\Model\EarnRule\Repository;
use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Api\Data\EarnRuleInterfaceFactory;
use Aheadworks\RewardPoints\Api\Data\EarnRuleSearchResultsInterface;
use Aheadworks\RewardPoints\Api\Data\EarnRuleSearchResultsInterfaceFactory;
use Aheadworks\RewardPoints\Model\ResourceModel\EarnRule\Collection as EarnRuleCollection;
use Aheadworks\RewardPoints\Model\ResourceModel\EarnRule\CollectionFactory as EarnRuleCollectionFactory;
use Aheadworks\RewardPoints\Model\Repository\CollectionProcessorInterface;
use Aheadworks\RewardPoints\Model\Indexer\EarnRule\Processor as EarnRuleIndexerProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Validator\Exception as ValidatorException;
use Aheadworks\RewardPoints\Model\StorefrontLabelsEntity\Store\Resolver as StorefrontLabelsEntityStoreResolver;

/**
 * Test for \Aheadworks\RewardPoints\Model\EarnRule\Repository
 */
class RepositoryTest extends TestCase
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var EarnRuleInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $earnRuleFactoryMock;

    /**
     * @var EarnRuleSearchResultInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $earnRuleSearchResultsFactoryMock;

    /**
     * @var EarnRuleCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $earnRuleCollectionFactoryMock;

    /**
     * @var JoinProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributesJoinProcessorMock;

    /**
     * @var CollectionProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessorMock;

    /**
     * @var EarnRuleIndexerProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexerProcessorMock;

    /**
     * @var StorefrontLabelsEntityStoreResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storefrontLabelsEntityStoreResolverMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->entityManagerMock = $this->createMock(EntityManager::class);
        $this->earnRuleFactoryMock = $this->createMock(EarnRuleInterfaceFactory::class);
        $this->earnRuleSearchResultsFactoryMock = $this->createMock(EarnRuleSearchResultsInterfaceFactory::class);
        $this->earnRuleCollectionFactoryMock = $this->createMock(EarnRuleCollectionFactory::class);
        $this->extensionAttributesJoinProcessorMock = $this->createMock(JoinProcessorInterface::class);
        $this->collectionProcessorMock = $this->createMock(CollectionProcessorInterface::class);
        $this->indexerProcessorMock = $this->createMock(EarnRuleIndexerProcessor::class);
        $this->storefrontLabelsEntityStoreResolverMock = $this->createMock(
            StorefrontLabelsEntityStoreResolver::class
        );

        $this->repository = $objectManager->getObject(
            Repository::class,
            [
                'entityManager' => $this->entityManagerMock,
                'earnRuleFactory' => $this->earnRuleFactoryMock,
                'earnRuleSearchResultsFactory' => $this->earnRuleSearchResultsFactoryMock,
                'earnRuleCollectionFactory' => $this->earnRuleCollectionFactoryMock,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessorMock,
                'collectionProcessor' => $this->collectionProcessorMock,
                'indexerProcessor' => $this->indexerProcessorMock,
                'storefrontLabelsEntityStoreResolver' => $this->storefrontLabelsEntityStoreResolverMock,
            ]
        );
    }

    /**
     * Test save method
     */
    public function testSave()
    {
        $ruleId = 10;
        $ruleMock = $this->createMock(EarnRuleInterface::class);
        $ruleMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($ruleId);
        $ruleMock->expects($this->once())
            ->method('validate')
            ->willReturnSelf();

        $this->earnRuleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($ruleMock);

        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($ruleMock)
            ->willReturn($ruleMock);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($ruleMock, $ruleId)
            ->willReturn($ruleMock);

        $this->indexerProcessorMock->expects($this->once())
            ->method('markIndexerAsInvalid')
            ->willReturnSelf();

        $this->assertSame($ruleMock, $this->repository->save($ruleMock));
    }

    /**
     * Test save method if an exception occurs
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Error!
     */
    public function testSaveException()
    {
        $errorMessage = 'Error!';
        $ruleMock = $this->createMock(EarnRuleInterface::class);
        $ruleMock->expects($this->once())
            ->method('validate')
            ->willReturnSelf();

        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($ruleMock)
            ->willThrowException(new \Exception($errorMessage));
$this->expectException(\Exception::class);
        $this->indexerProcessorMock->expects($this->never())
            ->method('markIndexerAsInvalid');

        $this->repository->save($ruleMock);
    }

    /**
     * Test save method if an validation exception occurs
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Error!
     */
    public function testSaveValidationException()
    {
        $errorMessage = 'Error!';
        $ruleMock = $this->createMock(EarnRuleInterface::class);
        $ruleMock->expects($this->once())
            ->method('validate')
            ->willThrowException(new ValidatorException(__($errorMessage)));
        $this->expectException(CouldNotSaveException::class);
        $this->entityManagerMock->expects($this->never())
            ->method('save');

        $this->indexerProcessorMock->expects($this->never())
            ->method('markIndexerAsInvalid');

        $this->repository->save($ruleMock);
    }

    /**
     * Test get method
     */
    public function testGet()
    {
        $ruleId = 10;
        $storeId = 3;
        $currentLabelsStoreId = $storeId;
        $arguments = ['store_id' => $currentLabelsStoreId];
        $ruleMock = $this->createMock(EarnRuleInterface::class);
        $ruleMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($ruleId);

        $this->earnRuleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($ruleMock);

        $this->storefrontLabelsEntityStoreResolverMock->expects($this->once())
            ->method('getStoreIdForCurrentLabels')
            ->with($storeId)
            ->willReturn($currentLabelsStoreId);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($ruleMock, $ruleId, $arguments)
            ->willReturn($ruleMock);

        $this->assertSame($ruleMock, $this->repository->get($ruleId, $storeId));
    }

    /**
     * Test get method
     */
    public function testGetStoreIdIsNotSet()
    {
        $ruleId = 10;
        $storeId = null;
        $currentLabelsStoreId = 3;
        $arguments = ['store_id' => $currentLabelsStoreId];
        $ruleMock = $this->createMock(EarnRuleInterface::class);
        $ruleMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($ruleId);

        $this->earnRuleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($ruleMock);

        $this->storefrontLabelsEntityStoreResolverMock->expects($this->once())
            ->method('getStoreIdForCurrentLabels')
            ->with($storeId)
            ->willReturn($currentLabelsStoreId);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($ruleMock, $ruleId, $arguments)
            ->willReturn($ruleMock);

        $this->assertSame($ruleMock, $this->repository->get($ruleId, $storeId));
    }

    /**
     * Test get method
     */
    public function testGetStoreIdIsNull()
    {
        $ruleId = 10;
        $storeId = null;
        $currentLabelsStoreId = null;
        $arguments = ['store_id' => $currentLabelsStoreId];
        $ruleMock = $this->createMock(EarnRuleInterface::class);
        $ruleMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($ruleId);

        $this->earnRuleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($ruleMock);

        $this->storefrontLabelsEntityStoreResolverMock->expects($this->once())
            ->method('getStoreIdForCurrentLabels')
            ->with($storeId)
            ->willReturn($currentLabelsStoreId);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($ruleMock, $ruleId, $arguments)
            ->willReturn($ruleMock);

        $this->assertSame($ruleMock, $this->repository->get($ruleId, $storeId));
    }

    /**
     * Test get method if specified rule does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 10
     */
    public function testGetException()
    {
        $ruleId = 10;
        $storeId = 3;
        $currentLabelsStoreId = $storeId;
        $arguments = ['store_id' => $currentLabelsStoreId];
        $ruleMock = $this->createMock(EarnRuleInterface::class);
        $ruleMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->earnRuleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($ruleMock);

        $this->storefrontLabelsEntityStoreResolverMock->expects($this->once())
            ->method('getStoreIdForCurrentLabels')
            ->with($storeId)
            ->willReturn($currentLabelsStoreId);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($ruleMock, $ruleId, $arguments)
            ->willReturn($ruleMock);
        $this->expectException(NoSuchEntityException::class);
        $this->repository->get($ruleId, $storeId);
    }

    /**
     * Test get method if specified rule does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 10
     */
    public function testGetExceptionStoreIdIsNotSet()
    {
        $ruleId = 10;
        $storeId = null;
        $currentLabelsStoreId = 3;
        $arguments = ['store_id' => $currentLabelsStoreId];
        $ruleMock = $this->createMock(EarnRuleInterface::class);
        $ruleMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->earnRuleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($ruleMock);

        $this->storefrontLabelsEntityStoreResolverMock->expects($this->once())
            ->method('getStoreIdForCurrentLabels')
            ->with($storeId)
            ->willReturn($currentLabelsStoreId);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($ruleMock, $ruleId, $arguments)
            ->willReturn($ruleMock);
        $this->expectException(NoSuchEntityException::class);
        $this->repository->get($ruleId, $storeId);
    }

    /**
     * Test get method if specified rule does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 10
     */
    public function testGetExceptionStoreIdIsNull()
    {
        $ruleId = 10;
        $storeId = null;
        $currentLabelsStoreId = null;
        $arguments = ['store_id' => $currentLabelsStoreId];
        $ruleMock = $this->createMock(EarnRuleInterface::class);
        $ruleMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->earnRuleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($ruleMock);

        $this->storefrontLabelsEntityStoreResolverMock->expects($this->once())
            ->method('getStoreIdForCurrentLabels')
            ->with($storeId)
            ->willReturn($currentLabelsStoreId);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($ruleMock, $ruleId, $arguments)
            ->willReturn($ruleMock);
        $this->expectException(NoSuchEntityException::class);
        $this->repository->get($ruleId, $storeId);
    }

    /**
     * Test getList method
     *
     * @param bool $error
     * @param int|null $storeId
     * @param int|null $currentLabelsStoreId
     * @dataProvider getListDataProvider
     */
    public function testGetList($error, $storeId, $currentLabelsStoreId)
    {
        $collectionSize = 1;
        $ruleId = 10;

        $searchCriteriaMock = $this->createMock(SearchCriteriaInterface::class);

        $collectionMock = $this->createMock(EarnRuleCollection::class);
        $collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn($collectionSize);
        $this->earnRuleCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $this->extensionAttributesJoinProcessorMock->expects($this->once())
            ->method('process')
            ->with($collectionMock, EarnRuleInterface::class);
        $this->collectionProcessorMock->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $collectionMock);

        $this->storefrontLabelsEntityStoreResolverMock->expects($this->any())
            ->method('getStoreIdForCurrentLabels')
            ->willReturnMap(
                [
                    [
                        $storeId,
                        $currentLabelsStoreId
                    ],
                    [
                        $currentLabelsStoreId,
                        $currentLabelsStoreId
                    ]
                ]
            );
        $collectionMock->expects($this->any())
            ->method('setStoreId')
            ->with($currentLabelsStoreId);

        $searchResultsMock = $this->createMock(EarnRuleSearchResultsInterface::class);
        $searchResultsMock->expects($this->atLeastOnce())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock)
            ->willReturnSelf();
        $searchResultsMock->expects($this->atLeastOnce())
            ->method('setTotalCount')
            ->with($collectionSize)
            ->willReturnSelf();
        $this->earnRuleSearchResultsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchResultsMock);

        $ruleModelMock = $this->createMock(EarnRule::class);
        $ruleModelMock->expects($this->once())
            ->method('getId')
            ->willReturn($ruleId);
        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$ruleModelMock]);

        $ruleMock = $this->createMock(EarnRuleInterface::class);
        if ($error) {
            $ruleMock->expects($this->atLeastOnce())
                ->method('getId')
                ->willReturn(null);
        } else {
            $ruleMock->expects($this->atLeastOnce())
                ->method('getId')
                ->willReturn($ruleId);
        }

        $this->earnRuleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($ruleMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($ruleMock, $ruleId)
            ->willReturn($ruleMock);

        try {
            if ($error) {
                $this->repository->getList($searchCriteriaMock, $storeId);
            } else {
                $searchResultsMock->expects($this->once())
                    ->method('setItems')
                    ->with([$ruleMock])
                    ->willReturnSelf();

                $this->assertSame($searchResultsMock, $this->repository->getList($searchCriteriaMock, $storeId));
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
            [
                'error' => false,
                'storeId' => 3,
                'currentLabelsStoreId' => 3,
            ],
            [
                'error' => false,
                'storeId' => null,
                'currentLabelsStoreId' => 3,
            ],
            [
                'error' => false,
                'storeId' => null,
                'currentLabelsStoreId' => null,
            ],
            [
                'error' => true,
                'storeId' => 3,
                'currentLabelsStoreId' => 3,
            ],
            [
                'error' => true,
                'storeId' => null,
                'currentLabelsStoreId' => 3,
            ],
            [
                'error' => true,
                'storeId' => null,
                'currentLabelsStoreId' => null,
            ]
        ];
    }

    /**
     * Test delete method
     */
    public function testDelete()
    {
        $ruleId = 10;
        $ruleMock = $this->createMock(EarnRuleInterface::class);
        $ruleMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($ruleId);

        $this->earnRuleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($ruleMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($ruleMock, $ruleId)
            ->willReturn($ruleMock);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($ruleMock)
            ->willReturn(true);

        $this->assertTrue($this->repository->delete($ruleMock));
    }

    /**
     * Test delete method if specified rule does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 10
     */
    public function testDeleteException()
    {
        $ruleId = 10;
        $ruleOneMock = $this->createMock(EarnRuleInterface::class);
        $ruleOneMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($ruleId);

        $ruleTwoMock = $this->createMock(EarnRuleInterface::class);
        $ruleTwoMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->earnRuleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($ruleTwoMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($ruleOneMock, $ruleId)
            ->willReturn($ruleTwoMock);
        $this->expectException(NoSuchEntityException::class);
        $this->repository->delete($ruleOneMock);
    }

    /**
     * Test deleteById method
     */
    public function testDeleteById()
    {
        $ruleId = 10;
        $ruleMock = $this->createMock(EarnRuleInterface::class);
        $ruleMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($ruleId);

        $this->earnRuleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($ruleMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($ruleMock, $ruleId)
            ->willReturn($ruleMock);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($ruleMock)
            ->willReturn(true);

        $this->assertTrue($this->repository->deleteById($ruleId));
    }

    /**
     * Test deleteById method if specified rule does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 10
     */
    public function testDeleteByIdException()
    {
        $ruleId = 10;
        $ruleMock = $this->createMock(EarnRuleInterface::class);
        $ruleMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->earnRuleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($ruleMock);

        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($ruleMock, $ruleId)
            ->willReturn($ruleMock);
        $this->expectException(NoSuchEntityException::class);
        $this->repository->deleteById($ruleId);
    }
}

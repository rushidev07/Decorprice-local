<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\EarnRule\Applier;

use Aheadworks\RewardPoints\Model\EarnRule\Applier\RuleLoader;
use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Api\Data\EarnRuleSearchResultsInterface;
use Aheadworks\RewardPoints\Api\EarnRuleRepositoryInterface;
use Aheadworks\RewardPoints\Model\ResourceModel\EarnRule as EarnRuleResource;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;

/**
 * Test for \Aheadworks\RewardPoints\Model\EarnRule\Applier\RuleLoader
 */
class RuleLoaderTest extends TestCase
{
    /**
     * @var RuleLoader
     */
    private $ruleLoader;

    /**
     * @var EarnRuleResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $earnRuleResourceMock;

    /**
     * @var EarnRuleRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $earnRuleRepositoryMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var SortOrderBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sortOrderBuilderMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->earnRuleResourceMock = $this->createMock(EarnRuleResource::class);
        $this->earnRuleRepositoryMock = $this->createMock(EarnRuleRepositoryInterface::class);
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $this->sortOrderBuilderMock = $this->createMock(SortOrderBuilder::class);

        $this->ruleLoader = $objectManager->getObject(
            RuleLoader::class,
            [
                'earnRuleResource' => $this->earnRuleResourceMock,
                'earnRuleRepository' => $this->earnRuleRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'sortOrderBuilder' => $this->sortOrderBuilderMock
            ]
        );
    }

    /**
     * Test getRulesForApply method
     */
    public function testGetRulesForApply()
    {
        $productId = 100;
        $customerGroupId = 10;
        $websiteId = 11;
        $currentDate = '2018-01-01';
        $ruleIds = [1, 2, 3];

        $this->earnRuleResourceMock->expects($this->once())
            ->method('getRuleIdsToApply')
            ->with($productId, $customerGroupId, $websiteId, $currentDate)
            ->willReturn($ruleIds);

        $sorOrderPriorityMock = $this->createMock(SortOrder::class);
        $sorOrderIdMock = $this->createMock(SortOrder::class);

        $this->sortOrderBuilderMock->expects($this->exactly(2))
            ->method('setField')
            ->withConsecutive([EarnRuleInterface::PRIORITY], [EarnRuleInterface::ID])
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->exactly(2))
            ->method('setAscendingDirection')
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls($sorOrderPriorityMock, $sorOrderIdMock);

        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->with(EarnRuleInterface::ID, $ruleIds, 'in')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('setSortOrders')
            ->with([$sorOrderPriorityMock, $sorOrderIdMock])
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $searchResultsMock = $this->createMock(EarnRuleSearchResultsInterface::class);
        $this->earnRuleRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($searchResultsMock);

        $ruleMock = $this->createMock(EarnRuleInterface::class);
        $rules = [$ruleMock];
        $searchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn($rules);

        $this->assertEquals(
            $rules,
            $this->ruleLoader->getRulesForApply($productId, $customerGroupId, $websiteId, $currentDate)
        );
    }

    /**
     * Test getRulesForApply method if an error occurs
     */
    public function testGetRulesForApplyError()
    {
        $productId = 100;
        $customerGroupId = 10;
        $websiteId = 11;
        $currentDate = '2018-01-01';
        $ruleIds = [1, 2, 3];

        $this->earnRuleResourceMock->expects($this->once())
            ->method('getRuleIdsToApply')
            ->with($productId, $customerGroupId, $websiteId, $currentDate)
            ->willReturn($ruleIds);

        $sorOrderPriorityMock = $this->createMock(SortOrder::class);
        $sorOrderIdMock = $this->createMock(SortOrder::class);

        $this->sortOrderBuilderMock->expects($this->exactly(2))
            ->method('setField')
            ->withConsecutive([EarnRuleInterface::PRIORITY], [EarnRuleInterface::ID])
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->exactly(2))
            ->method('setAscendingDirection')
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls($sorOrderPriorityMock, $sorOrderIdMock);

        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->with(EarnRuleInterface::ID, $ruleIds, 'in')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('setSortOrders')
            ->with([$sorOrderPriorityMock, $sorOrderIdMock])
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $this->earnRuleRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willThrowException(new LocalizedException(__('Error!')));

        $this->assertEquals(
            [],
            $this->ruleLoader->getRulesForApply($productId, $customerGroupId, $websiteId, $currentDate)
        );
    }
}

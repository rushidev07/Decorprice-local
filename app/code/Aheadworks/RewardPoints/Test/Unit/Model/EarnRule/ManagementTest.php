<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\EarnRule;

use Aheadworks\RewardPoints\Api\Data\EarnRuleSearchResultsInterface;
use Aheadworks\RewardPoints\Api\EarnRuleRepositoryInterface;
use Aheadworks\RewardPoints\Model\EarnRule\Management;
use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Api\Data\EarnRuleInterfaceFactory;
use Aheadworks\RewardPoints\Model\DateTime;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Test for \Aheadworks\RewardPoints\Model\EarnRule\Management
 */
class ManagementTest extends TestCase
{
    /**
     * @var Management
     */
    private $management;

    /**
     * @var EarnRuleRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $earnRuleRepositoryMock;

    /**
     * @var EarnRuleInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $earnRuleFactoryMock;

    /**
     * @var DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeMock;

    /**
     * @var DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

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

        $this->earnRuleRepositoryMock = $this->createMock(EarnRuleRepositoryInterface::class);
        $this->earnRuleFactoryMock = $this->createMock(EarnRuleInterfaceFactory::class);
        $this->dateTimeMock = $this->createMock(DateTime::class);
        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper::class);
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);

        $this->management = $objectManager->getObject(
            Management::class,
            [
                'earnRuleRepository' => $this->earnRuleRepositoryMock,
                'earnRuleFactory' => $this->earnRuleFactoryMock,
                'dateTime' => $this->dateTimeMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
            ]
        );
    }

    /**
     * Test enable method
     */
    public function testEnable()
    {
        $ruleId = 10;
        $ruleMock = $this->createMock(EarnRuleInterface::class);
        $ruleMock->expects($this->once())
            ->method('setStatus')
            ->with(EarnRuleInterface::STATUS_ENABLED)
            ->willReturnSelf();

        $this->earnRuleRepositoryMock->expects($this->once())
            ->method('get')
            ->with($ruleId)
            ->willReturn($ruleMock);
        $this->earnRuleRepositoryMock->expects($this->once())
            ->method('save')
            ->with($ruleMock)
            ->willReturn($ruleMock);

        $this->assertSame($ruleMock, $this->management->enable($ruleId));
    }

    /**
     * Test enable method if no rule found
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity!
     */
    public function testEnableNoRule()
    {
        $ruleId = 10;
        $errorMessage = 'No such entity!';

        $this->earnRuleRepositoryMock->expects($this->once())
            ->method('get')
            ->with($ruleId)
            ->willThrowException(new NoSuchEntityException(__('No such entity!')));

        $this->expectException(NoSuchEntityException::class);
        $this->management->enable($ruleId);
    }

    /**
     * Test enable method if the rule can not be saved
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Could not save the rule!
     */
    public function testEnableNotSaved()
    {
        $ruleId = 10;
        $errorMessage = 'Could not save the rule!';

        $ruleMock = $this->createMock(EarnRuleInterface::class);
        $ruleMock->expects($this->once())
            ->method('setStatus')
            ->with(EarnRuleInterface::STATUS_ENABLED)
            ->willReturnSelf();

        $this->earnRuleRepositoryMock->expects($this->once())
            ->method('get')
            ->with($ruleId)
            ->willReturn($ruleMock);
        $this->earnRuleRepositoryMock->expects($this->once())
            ->method('save')
            ->with($ruleMock)
            ->willThrowException(new \Exception('Error!'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error!');
        $this->management->enable($ruleId);
    }

    /**
     * Test disable method
     */
    public function testDisable()
    {
        $ruleId = 10;
        $ruleMock = $this->createMock(EarnRuleInterface::class);
        $ruleMock->expects($this->once())
            ->method('setStatus')
            ->with(EarnRuleInterface::STATUS_DISABLED)
            ->willReturnSelf();

        $this->earnRuleRepositoryMock->expects($this->once())
            ->method('get')
            ->with($ruleId)
            ->willReturn($ruleMock);
        $this->earnRuleRepositoryMock->expects($this->once())
            ->method('save')
            ->with($ruleMock)
            ->willReturn($ruleMock);

        $this->assertSame($ruleMock, $this->management->disable($ruleId));
    }

    /**
     * Test disable method if no rule found
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity!
     */
    public function testDisableNoRule()
    {
        $ruleId = 10;
        $errorMessage = 'No such entity!';

        $this->earnRuleRepositoryMock->expects($this->once())
            ->method('get')
            ->with($ruleId)
            ->willThrowException(new NoSuchEntityException(__($errorMessage)));
        $this->expectException(NoSuchEntityException::class);
        $this->management->disable($ruleId);
    }

    /**
     * Test disable method if the rule can not be saved
     *
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not save the rule!
     */
    public function testDisableNotSaved()
    {
        $ruleId = 10;
        $errorMessage = 'Could not save the rule!';

        $ruleMock = $this->createMock(EarnRuleInterface::class);
        $ruleMock->expects($this->once())
            ->method('setStatus')
            ->with(EarnRuleInterface::STATUS_DISABLED)
            ->willReturnSelf();

        $this->earnRuleRepositoryMock->expects($this->once())
            ->method('get')
            ->with($ruleId)
            ->willReturn($ruleMock);
        $this->earnRuleRepositoryMock->expects($this->once())
            ->method('save')
            ->with($ruleMock)
            ->willThrowException(new CouldNotSaveException(__($errorMessage)));
        $this->expectException(CouldNotSaveException::class);
        $this->management->disable($ruleId);
    }

    /**
     * Test createRule method
     */
    public function testCreateRule()
    {
        $ruleData = [
            EarnRuleInterface::NAME => 'Sample Rule'
        ];

        $ruleMock = $this->createMock(EarnRuleInterface::class);
        $this->earnRuleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($ruleMock);

        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($ruleMock, $ruleData, EarnRuleInterface::class)
            ->willReturnSelf();

        $this->earnRuleRepositoryMock->expects($this->once())
            ->method('save')
            ->with($ruleMock)
            ->willReturn($ruleMock);

        $this->assertSame($ruleMock, $this->management->createRule($ruleData));
    }

    /**
     * Test createRule method if a save error occurs
     *
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Error!
     */
    public function testCreateRuleSaveError()
    {
        $ruleData = [
            EarnRuleInterface::NAME => 'Sample Rule'
        ];

        $ruleMock = $this->createMock(EarnRuleInterface::class);
        $this->earnRuleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($ruleMock);

        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($ruleMock, $ruleData, EarnRuleInterface::class)
            ->willReturnSelf();

        $this->earnRuleRepositoryMock->expects($this->once())
            ->method('save')
            ->with($ruleMock)
            ->willThrowException(new CouldNotSaveException(__('Error!')));
        $this->expectException(CouldNotSaveException::class);
        $this->management->createRule($ruleData);
    }

    /**
     * Test updateRule method
     */
    public function testUpdateRule()
    {
        $ruleId = 10;
        $ruleData = [
            EarnRuleInterface::ID => $ruleId,
            EarnRuleInterface::NAME => 'Sample Rule'
        ];
        $ruleMock = $this->createMock(EarnRuleInterface::class);

        $this->earnRuleRepositoryMock->expects($this->once())
            ->method('get')
            ->with($ruleId)
            ->willReturn($ruleMock);

        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($ruleMock, $ruleData, EarnRuleInterface::class)
            ->willReturnSelf();

        $this->earnRuleRepositoryMock->expects($this->once())
            ->method('save')
            ->with($ruleMock)
            ->willReturn($ruleMock);

        $this->assertSame($ruleMock, $this->management->updateRule($ruleId, $ruleData));
    }

    /**
     * Test updateRule method if no rule found
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity!
     */
    public function testUpdateRuleNoRule()
    {
        $ruleId = 10;
        $ruleData = [
            EarnRuleInterface::ID => $ruleId,
            EarnRuleInterface::NAME => 'Sample Rule'
        ];

        $this->earnRuleRepositoryMock->expects($this->once())
            ->method('get')
            ->with($ruleId)
            ->willThrowException(new NoSuchEntityException(__('No such entity!')));
        $this->expectException(NoSuchEntityException::class);
        $this->management->updateRule($ruleId, $ruleData);
    }

    /**
     * Test updateRule method a save error occurs
     *
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Error!
     */
    public function testUpdateRuleSaveError()
    {
        $ruleId = 10;
        $ruleData = [
            EarnRuleInterface::ID => $ruleId,
            EarnRuleInterface::NAME => 'Sample Rule'
        ];
        $ruleMock = $this->createMock(EarnRuleInterface::class);

        $this->earnRuleRepositoryMock->expects($this->once())
            ->method('get')
            ->with($ruleId)
            ->willReturn($ruleMock);

        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($ruleMock, $ruleData, EarnRuleInterface::class)
            ->willReturnSelf();

        $this->earnRuleRepositoryMock->expects($this->once())
            ->method('save')
            ->with($ruleMock)
            ->willThrowException(new CouldNotSaveException(__('Error!')));
        $this->expectException(CouldNotSaveException::class);
        $this->management->updateRule($ruleId, $ruleData);
    }

    /**
     * Test getActiveRules method
     */
    public function testGetActiveRules()
    {
        $ruleMock = $this->createMock(EarnRuleInterface::class);
        $rules = [$ruleMock];

        $todayDate = '2018-01-01';
        $this->dateTimeMock->expects($this->once())
            ->method('getTodayDate')
            ->willReturn($todayDate);

        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $this->searchCriteriaBuilderMock->expects($this->exactly(2))
            ->method('addFilter')
            ->withConsecutive(
                [EarnRuleInterface::STATUS, EarnRuleInterface::STATUS_ENABLED, 'eq'],
                [EarnRuleInterface::TO_DATE, $todayDate]
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $searchResultMock = $this->createMock(EarnRuleSearchResultsInterface::class);
        $this->earnRuleRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($searchResultMock);

        $searchResultMock->expects($this->once())
            ->method('getItems')
            ->willReturn($rules);

        $this->assertEquals($rules, $this->management->getActiveRules());
    }

    /**
     * Test getActiveRules method if an error occurs
     */
    public function testGetActiveRulesError()
    {
        $todayDate = '2018-01-01';
        $this->dateTimeMock->expects($this->once())
            ->method('getTodayDate')
            ->willReturn($todayDate);

        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $this->searchCriteriaBuilderMock->expects($this->exactly(2))
            ->method('addFilter')
            ->withConsecutive(
                [EarnRuleInterface::STATUS, EarnRuleInterface::STATUS_ENABLED, 'eq'],
                [EarnRuleInterface::TO_DATE, $todayDate]
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $searchResultMock = $this->createMock(EarnRuleSearchResultsInterface::class);
        $this->earnRuleRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willThrowException(new LocalizedException(__('Error!')));

        $searchResultMock->expects($this->never())
            ->method('getItems');

        $this->assertEquals([], $this->management->getActiveRules());
    }
}

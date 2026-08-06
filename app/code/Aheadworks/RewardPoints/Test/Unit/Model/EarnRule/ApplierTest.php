<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\EarnRule;

use Aheadworks\RewardPoints\Model\EarnRule\Applier;
use Aheadworks\RewardPoints\Api\Data\ActionInterface;
use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Model\DateTime;
use Aheadworks\RewardPoints\Model\EarnRule\Applier\ActionApplier;
use Aheadworks\RewardPoints\Model\EarnRule\Applier\RuleLoader;
use Aheadworks\RewardPoints\Model\Calculator\ResultInterface;
use Aheadworks\RewardPoints\Model\Calculator\ResultInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Test for \Aheadworks\RewardPoints\Model\Calculator\Earning\RuleApplier
 */
class ApplierTest extends TestCase
{
    /**
     * @var Applier
     */
    private $applier;

    /**
     * @var RuleLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ruleLoaderMock;

    /**
     * @var ActionApplier|\PHPUnit_Framework_MockObject_MockObject
     */
    private $actionApplierMock;

    /**
     * @var CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var ResultInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->ruleLoaderMock = $this->createMock(RuleLoader::class);
        $this->actionApplierMock = $this->createMock(ActionApplier::class);
        $this->customerRepositoryMock = $this->createMock(CustomerRepositoryInterface::class);
        $this->resultFactoryMock = $this->createMock(ResultInterfaceFactory::class);
        $this->dateTimeMock = $this->createMock(DateTime::class);

        $this->applier = $objectManager->getObject(
            Applier::class,
            [
                'ruleLoader' => $this->ruleLoaderMock,
                'actionApplier' => $this->actionApplierMock,
                'customerRepository' => $this->customerRepositoryMock,
                'resultFactory' => $this->resultFactoryMock,
                'dateTime' => $this->dateTimeMock
            ]
        );
    }

    /**
     * Test apply method
     */
    public function testApply()
    {
        $pointsFirst = 30;
        $pointsSecond = 40;
        $pointsFinal = 200;
        $customerId = 10;
        $customerGroupId = 11;
        $productId = 125;
        $websiteId = 20;
        $todayDate = '2018-01-01';
        $qty = 1;

        $actionFirstMock = $this->createMock(ActionInterface::class);
        $actionSecondMock = $this->createMock(ActionInterface::class);
        $rules = [
            $this->getRuleMock(31, $actionFirstMock, false),
            $this->getRuleMock(32, $actionSecondMock, false),
        ];
        $appliedRuleIds = [31, 32];

        $customerMock = $this->createMock(CustomerInterface::class);
        $customerMock->expects($this->once())
            ->method('getGroupId')
            ->willReturn($customerGroupId);
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customerMock);

        $this->dateTimeMock->expects($this->once())
            ->method('getTodayDate')
            ->willReturn($todayDate);

        $this->ruleLoaderMock->expects($this->once())
            ->method('getRulesForApply')
            ->with($productId, $customerGroupId, $websiteId, $todayDate)
            ->willReturn($rules);

        $this->actionApplierMock->expects($this->exactly(2))
            ->method('apply')
            ->withConsecutive([$pointsFirst, $qty, $actionFirstMock], [$pointsSecond, $qty, $actionSecondMock])
            ->willReturnOnConsecutiveCalls($pointsSecond, $pointsFinal);

        $resultMock = $this->getResultMock($pointsFinal, $appliedRuleIds);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultMock);

        $this->assertEquals(
            $resultMock,
            $this->applier->apply($pointsFirst, $qty, $productId, $customerId, $websiteId)
        );
    }

    /**
     * Test apply method if "DiscardSubsequentRules" is enabled
     */
    public function testApplyDiscardSubsequentRules()
    {
        $points = 30;
        $pointsFinal = 200;
        $customerId = 10;
        $customerGroupId = 11;
        $productId = 125;
        $websiteId = 20;
        $todayDate = '2018-01-01';
        $qty = 1;

        $actionFirstMock = $this->createMock(ActionInterface::class);
        $actionSecondMock = $this->createMock(ActionInterface::class);
        $rules = [
            $this->getRuleMock(31, $actionFirstMock, true),
            $this->getRuleMock(32, $actionSecondMock, false),
        ];
        $appliedRuleIds = [31];

        $customerMock = $this->createMock(CustomerInterface::class);
        $customerMock->expects($this->once())
            ->method('getGroupId')
            ->willReturn($customerGroupId);
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customerMock);

        $this->dateTimeMock->expects($this->once())
            ->method('getTodayDate')
            ->willReturn($todayDate);

        $this->ruleLoaderMock->expects($this->once())
            ->method('getRulesForApply')
            ->with($productId, $customerGroupId, $websiteId, $todayDate)
            ->willReturn($rules);

        $this->actionApplierMock->expects($this->once())
            ->method('apply')
            ->with($points, $qty, $actionFirstMock)
            ->willReturn($pointsFinal);

        $resultMock = $this->getResultMock($pointsFinal, $appliedRuleIds);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultMock);

        $this->assertEquals(
            $resultMock,
            $this->applier->apply($points, $qty, $productId, $customerId, $websiteId)
        );
    }

    /**
     * Test apply method if no rules to apply
     */
    public function testApplyNoRulesToApply()
    {
        $points = 30;
        $customerId = 10;
        $customerGroupId = 11;
        $productId = 125;
        $websiteId = 20;
        $todayDate = '2018-01-01';
        $qty = 1;
        $rules = [];
        $appliedRuleIds = [];

        $customerMock = $this->createMock(CustomerInterface::class);
        $customerMock->expects($this->once())
            ->method('getGroupId')
            ->willReturn($customerGroupId);
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customerMock);

        $this->dateTimeMock->expects($this->once())
            ->method('getTodayDate')
            ->willReturn($todayDate);

        $this->ruleLoaderMock->expects($this->once())
            ->method('getRulesForApply')
            ->with($productId, $customerGroupId, $websiteId, $todayDate)
            ->willReturn($rules);

        $this->actionApplierMock->expects($this->never())
            ->method('apply');

        $resultMock = $this->getResultMock($points, $appliedRuleIds);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultMock);

        $this->assertEquals(
            $resultMock,
            $this->applier->apply($points, $qty, $productId, $customerId, $websiteId)
        );
    }

    /**
     * Test apply method if no customer found
     */
    public function testApplyNoCustomer()
    {
        $points = 30;
        $customerId = 10;
        $productId = 125;
        $websiteId = 20;
        $qty = 1;
        $appliedRuleIds = [];

        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willThrowException(new NoSuchEntityException(__('No such entity!')));

        $resultMock = $this->getResultMock($points, $appliedRuleIds);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultMock);

        $this->assertEquals(
            $resultMock,
            $this->applier->apply($points, $qty, $productId, $customerId, $websiteId)
        );
    }

    /**
     * Test applyByCustomerGroup method
     */
    public function testApplyByCustomerGroup()
    {
        $pointsFirst = 30;
        $pointsSecond = 40;
        $pointsFinal = 200;
        $customerGroupId = 11;
        $productId = 125;
        $websiteId = 20;
        $todayDate = '2018-01-01';
        $qty = 1;

        $actionFirstMock = $this->createMock(ActionInterface::class);
        $actionSecondMock = $this->createMock(ActionInterface::class);
        $rules = [
            $this->getRuleMock(31, $actionFirstMock, false),
            $this->getRuleMock(32, $actionSecondMock, false),
        ];
        $appliedRuleIds = [31, 32];

        $this->dateTimeMock->expects($this->once())
            ->method('getTodayDate')
            ->willReturn($todayDate);

        $this->ruleLoaderMock->expects($this->once())
            ->method('getRulesForApply')
            ->with($productId, $customerGroupId, $websiteId, $todayDate)
            ->willReturn($rules);

        $this->actionApplierMock->expects($this->exactly(2))
            ->method('apply')
            ->withConsecutive([$pointsFirst, $qty, $actionFirstMock], [$pointsSecond, $qty, $actionSecondMock])
            ->willReturnOnConsecutiveCalls($pointsSecond, $pointsFinal);

        $resultMock = $this->getResultMock($pointsFinal, $appliedRuleIds);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultMock);

        $this->assertEquals(
            $resultMock,
            $this->applier->applyByCustomerGroup($pointsFirst, $qty, $productId, $customerGroupId, $websiteId)
        );
    }

    /**
     * Test applyByCustomerGroup method and round points amount to integer value
     */
    public function testApplyByCustomerGroupPointsAmountRounded()
    {
        $pointsFirst = 2.75;
        $pointsSecond = 13.75; // rule * 5
        $pointsFinal = 18.75; // rule + 5
        $pointsRounded = 18;
        $customerGroupId = 11;
        $productId = 125;
        $websiteId = 20;
        $todayDate = '2018-01-01';
        $qty = 1;

        $actionFirstMock = $this->createMock(ActionInterface::class);
        $actionSecondMock = $this->createMock(ActionInterface::class);
        $rules = [
            $this->getRuleMock(31, $actionFirstMock, false),
            $this->getRuleMock(32, $actionSecondMock, false),
        ];
        $appliedRuleIds = [31, 32];

        $this->dateTimeMock->expects($this->once())
            ->method('getTodayDate')
            ->willReturn($todayDate);

        $this->ruleLoaderMock->expects($this->once())
            ->method('getRulesForApply')
            ->with($productId, $customerGroupId, $websiteId, $todayDate)
            ->willReturn($rules);

        $this->actionApplierMock->expects($this->exactly(2))
            ->method('apply')
            ->withConsecutive([$pointsFirst, $qty, $actionFirstMock], [$pointsSecond, $qty, $actionSecondMock])
            ->willReturnOnConsecutiveCalls($pointsSecond, $pointsFinal);

        $resultMock = $this->getResultMock($pointsRounded, $appliedRuleIds);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultMock);

        $this->assertEquals(
            $resultMock,
            $this->applier->applyByCustomerGroup($pointsFirst, $qty, $productId, $customerGroupId, $websiteId)
        );
    }

    /**
     * Test applyByCustomerGroup method if "DiscardSubsequentRules" is enabled
     */
    public function testApplyByCustomerGroupDiscardSubsequentRules()
    {
        $pointsFirst = 30;
        $pointsSecond = 40;
        $pointsFinal = 40;
        $customerGroupId = 11;
        $productId = 125;
        $websiteId = 20;
        $todayDate = '2018-01-01';
        $qty = 1;

        $actionFirstMock = $this->createMock(ActionInterface::class);
        $actionSecondMock = $this->createMock(ActionInterface::class);
        $rules = [
            $this->getRuleMock(31, $actionFirstMock, true),
            $this->getRuleMock(32, $actionSecondMock, false),
        ];
        $appliedRuleIds = [31];

        $this->dateTimeMock->expects($this->once())
            ->method('getTodayDate')
            ->willReturn($todayDate);

        $this->ruleLoaderMock->expects($this->once())
            ->method('getRulesForApply')
            ->with($productId, $customerGroupId, $websiteId, $todayDate)
            ->willReturn($rules);

        $this->actionApplierMock->expects($this->exactly(1))
            ->method('apply')
            ->with($pointsFirst, $qty, $actionFirstMock)
            ->willReturn($pointsSecond);

        $resultMock = $this->getResultMock($pointsFinal, $appliedRuleIds);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultMock);

        $this->assertEquals(
            $resultMock,
            $this->applier->applyByCustomerGroup($pointsFirst, $qty, $productId, $customerGroupId, $websiteId)
        );
    }

    /**
     * Test applyByCustomerGroup method if no rules to apply
     */
    public function testApplyByCustomerGroupNoRulesToApply()
    {
        $points = 30;
        $customerGroupId = 11;
        $productId = 125;
        $websiteId = 20;
        $todayDate = '2018-01-01';
        $qty = 1;
        $rules = [];
        $appliedRuleIds = [];

        $this->dateTimeMock->expects($this->once())
            ->method('getTodayDate')
            ->willReturn($todayDate);

        $this->ruleLoaderMock->expects($this->once())
            ->method('getRulesForApply')
            ->with($productId, $customerGroupId, $websiteId, $todayDate)
            ->willReturn($rules);

        $this->actionApplierMock->expects($this->never())
            ->method('apply');

        $resultMock = $this->getResultMock($points, $appliedRuleIds);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultMock);

        $this->assertEquals(
            $resultMock,
            $this->applier->applyByCustomerGroup($points, $qty, $productId, $customerGroupId, $websiteId)
        );
    }

    /**
     * Test getAppliedRuleIds method
     */
    public function testGetAppliedRuleIds()
    {
        $productId = 125;
        $customerGroupId = 11;
        $websiteId = 20;
        $todayDate = '2018-01-01';

        $actionFirstMock = $this->createMock(ActionInterface::class);
        $actionSecondMock = $this->createMock(ActionInterface::class);
        $rules = [
            $this->getRuleMock(31, $actionFirstMock, false),
            $this->getRuleMock(32, $actionSecondMock, false),
        ];
        $appliedRuleIds = [31, 32];

        $this->dateTimeMock->expects($this->once())
            ->method('getTodayDate')
            ->willReturn($todayDate);

        $this->ruleLoaderMock->expects($this->once())
            ->method('getRulesForApply')
            ->with($productId, $customerGroupId, $websiteId, $todayDate)
            ->willReturn($rules);

        $this->assertEquals(
            $appliedRuleIds,
            $this->applier->getAppliedRuleIds($productId, $customerGroupId, $websiteId)
        );
    }

    /**
     * Test getAppliedRuleIds method if "DiscardSubsequentRules" is enabled
     */
    public function testGetAppliedRuleIdsDiscardSubsequentRules()
    {
        $productId = 125;
        $customerGroupId = 11;
        $websiteId = 20;
        $todayDate = '2018-01-01';

        $actionFirstMock = $this->createMock(ActionInterface::class);
        $actionSecondMock = $this->createMock(ActionInterface::class);
        $rules = [
            $this->getRuleMock(31, $actionFirstMock, true),
            $this->getRuleMock(32, $actionSecondMock, false),
        ];
        $appliedRuleIds = [31];

        $this->dateTimeMock->expects($this->once())
            ->method('getTodayDate')
            ->willReturn($todayDate);

        $this->ruleLoaderMock->expects($this->once())
            ->method('getRulesForApply')
            ->with($productId, $customerGroupId, $websiteId, $todayDate)
            ->willReturn($rules);

        $this->assertEquals(
            $appliedRuleIds,
            $this->applier->getAppliedRuleIds($productId, $customerGroupId, $websiteId)
        );
    }

    /**
     * Test getAppliedRuleIds method if no rules to apply
     */
    public function testGetAppliedRuleIdsNoRules()
    {
        $productId = 125;
        $customerGroupId = 11;
        $websiteId = 20;
        $todayDate = '2018-01-01';

        $rules = [];
        $appliedRuleIds = [];

        $this->dateTimeMock->expects($this->once())
            ->method('getTodayDate')
            ->willReturn($todayDate);

        $this->ruleLoaderMock->expects($this->once())
            ->method('getRulesForApply')
            ->with($productId, $customerGroupId, $websiteId, $todayDate)
            ->willReturn($rules);

        $this->assertEquals(
            $appliedRuleIds,
            $this->applier->getAppliedRuleIds($productId, $customerGroupId, $websiteId)
        );
    }

    /**
     * Get rule mock
     *
     * @param int $id
     * @param ActionInterface|\PHPUnit_Framework_MockObject_MockObject $actionMock
     * @param bool $discardSubsequentRules
     * @return EarnRuleInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getRuleMock($id, $actionMock, $discardSubsequentRules)
    {
        $ruleMock = $this->createMock(EarnRuleInterface::class);
        $ruleMock->expects($this->any())
            ->method('getId')
            ->willReturn($id);
        $ruleMock->expects($this->any())
            ->method('getAction')
            ->willReturn($actionMock);
        $ruleMock->expects($this->any())
            ->method('getDiscardSubsequentRules')
            ->willReturn($discardSubsequentRules);

        return $ruleMock;
    }

    /**
     * Get result mock
     *
     * @param float $pointsFinal
     * @param int[] $appliedRuleIds
     * @return ResultInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getResultMock($pointsFinal, $appliedRuleIds)
    {
        $resultMock = $this->createMock(ResultInterface::class);
        $resultMock->expects($this->once())
            ->method('setPoints')
            ->with($pointsFinal)
            ->willReturnSelf();
        $resultMock->expects($this->once())
            ->method('setAppliedRuleIds')
            ->with($appliedRuleIds)
            ->willReturnSelf();

        return $resultMock;
    }
}

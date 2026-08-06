<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Calculator\Earning;

use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\Predictor;
use Aheadworks\RewardPoints\Model\Calculator\Earning\Calculator;
use Aheadworks\RewardPoints\Model\Calculator\ResultInterface;
use Aheadworks\RewardPoints\Model\Config;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\RewardPoints\Model\Calculator\Earning\Predictor
 */
class PredictorTest extends TestCase
{
    /**
     * @var Predictor
     */
    private $predictor;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var Calculator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $calculatorMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->configMock = $this->createMock(Config::class);
        $this->calculatorMock = $this->createMock(Calculator::class);

        $this->predictor = $objectManager->getObject(
            Predictor::class,
            [
                'config' => $this->configMock,
                'calculator' => $this->calculatorMock,
            ]
        );
    }

    /**
     * Test calculateMaxPointsForCustomer method
     */
    public function testCalculateMaxPointsForCustomer()
    {
        $customerId = 10;
        $websiteId = 2;

        $earnItemFirstMock = $this->createMock(EarnItemInterface::class);
        $earnItemSecondMock = $this->createMock(EarnItemInterface::class);
        $earnItems = [$earnItemFirstMock, $earnItemSecondMock];

        $resultFirstMock = $this->getCalculationResultMock(100);
        $resultSecondMock = $this->getCalculationResultMock(102.5);
        $this->calculatorMock->expects($this->exactly(2))
            ->method('calculate')
            ->withConsecutive(
                [[$earnItemFirstMock], $customerId, $websiteId],
                [[$earnItemSecondMock], $customerId, $websiteId]
            )
            ->willReturnOnConsecutiveCalls($resultFirstMock, $resultSecondMock);

        $this->assertSame(
            $resultSecondMock,
            $this->predictor->calculateMaxPointsForCustomer($earnItems, $customerId, $websiteId)
        );
    }

    /**
     * Test calculateMaxPointsForCustomer method (other case)
     */
    public function testCalculateMaxPointsForCustomerOtherCase()
    {
        $customerId = 10;
        $websiteId = 2;

        $earnItemFirstMock = $this->createMock(EarnItemInterface::class);
        $earnItemSecondMock = $this->createMock(EarnItemInterface::class);
        $earnItems = [$earnItemFirstMock, $earnItemSecondMock];

        $resultFirstMock = $this->getCalculationResultMock(100);
        $resultSecondMock = $this->getCalculationResultMock(99);
        $this->calculatorMock->expects($this->exactly(2))
            ->method('calculate')
            ->withConsecutive(
                [[$earnItemFirstMock], $customerId, $websiteId],
                [[$earnItemSecondMock], $customerId, $websiteId]
            )
            ->willReturnOnConsecutiveCalls($resultFirstMock, $resultSecondMock);

        $this->assertSame(
            $resultFirstMock,
            $this->predictor->calculateMaxPointsForCustomer($earnItems, $customerId, $websiteId)
        );
    }

    /**
     * Test calculateMaxPointsForCustomer method if merge rule ids enabled
     */
    public function testCalculateMaxPointsForCustomerMergeRuleIds()
    {
        $customerId = 10;
        $websiteId = 2;
        $mergeRuleIds = true;

        $earnItemFirstMock = $this->createMock(EarnItemInterface::class);
        $earnItemSecondMock = $this->createMock(EarnItemInterface::class);
        $earnItemThirdMock = $this->createMock(EarnItemInterface::class);
        $earnItems = [$earnItemFirstMock, $earnItemSecondMock, $earnItemThirdMock];

        $resultFirstMock = $this->getCalculationResultMock(100, [1, 2]);
        $resultSecondMock = $this->getCalculationResultMock(102.5, [2, 3], [1, 2, 3]);
        $resultThirdMock = $this->getCalculationResultMock(99, []);
        $this->calculatorMock->expects($this->exactly(3))
            ->method('calculate')
            ->withConsecutive(
                [[$earnItemFirstMock], $customerId, $websiteId],
                [[$earnItemSecondMock], $customerId, $websiteId],
                [[$earnItemThirdMock], $customerId, $websiteId]
            )
            ->willReturnOnConsecutiveCalls($resultFirstMock, $resultSecondMock, $resultThirdMock);

        $this->assertSame(
            $resultSecondMock,
            $this->predictor->calculateMaxPointsForCustomer($earnItems, $customerId, $websiteId, $mergeRuleIds)
        );
    }

    /**
     * Test calculateMaxPointsForCustomer method if no items specified
     */
    public function testCalculateMaxPointsForCustomerNoItems()
    {
        $customerId = 10;
        $websiteId = 2;
        $earnItems = [];

        $resultMock = $this->createMock(ResultInterface::class);

        $this->calculatorMock->expects($this->never())
            ->method('calculate');
        $this->calculatorMock->expects($this->once())
            ->method('getEmptyResult')
            ->willReturn($resultMock);

        $this->assertSame(
            $resultMock,
            $this->predictor->calculateMaxPointsForCustomer($earnItems, $customerId, $websiteId)
        );
    }

    /**
     * Test calculateMaxPointsForGuest method
     */
    public function testCalculateMaxPointsForGuest()
    {
        $customerGroupId = 10;
        $websiteId = 2;

        $this->configMock->expects($this->once())
            ->method('getDefaultCustomerGroupIdForGuest')
            ->willReturn($customerGroupId);

        $earnItemFirstMock = $this->createMock(EarnItemInterface::class);
        $earnItemSecondMock = $this->createMock(EarnItemInterface::class);
        $earnItems = [$earnItemFirstMock, $earnItemSecondMock];

        $resultFirstMock = $this->getCalculationResultMock(100);
        $resultSecondMock = $this->getCalculationResultMock(120);

        $this->calculatorMock->expects($this->exactly(2))
            ->method('calculateByCustomerGroup')
            ->withConsecutive(
                [[$earnItemFirstMock], $customerGroupId, $websiteId],
                [[$earnItemSecondMock], $customerGroupId, $websiteId]
            )
            ->willReturnOnConsecutiveCalls($resultFirstMock, $resultSecondMock);

        $this->assertSame(
            $resultSecondMock,
            $this->predictor->calculateMaxPointsForGuest($earnItems, $websiteId)
        );
    }

    /**
     * Test calculateMaxPointsForGuest method if merge rule ids enabled
     */
    public function testCalculateMaxPointsForGuestMergeRuleIds()
    {
        $customerGroupId = 10;
        $websiteId = 2;
        $mergeRuleIds = true;

        $this->configMock->expects($this->once())
            ->method('getDefaultCustomerGroupIdForGuest')
            ->willReturn($customerGroupId);

        $earnItemFirstMock = $this->createMock(EarnItemInterface::class);
        $earnItemSecondMock = $this->createMock(EarnItemInterface::class);
        $earnItemThirdMock = $this->createMock(EarnItemInterface::class);
        $earnItems = [$earnItemFirstMock, $earnItemSecondMock, $earnItemThirdMock];

        $resultFirstMock = $this->getCalculationResultMock(100, [1, 3]);
        $resultSecondMock = $this->getCalculationResultMock(120, [2], [1, 3, 2]);
        $resultThirdMock = $this->getCalculationResultMock(90, [2, 3]);

        $this->calculatorMock->expects($this->exactly(3))
            ->method('calculateByCustomerGroup')
            ->withConsecutive(
                [[$earnItemFirstMock], $customerGroupId, $websiteId],
                [[$earnItemSecondMock], $customerGroupId, $websiteId],
                [[$earnItemThirdMock], $customerGroupId, $websiteId]
            )
            ->willReturnOnConsecutiveCalls($resultFirstMock, $resultSecondMock, $resultThirdMock);

        $this->assertSame(
            $resultSecondMock,
            $this->predictor->calculateMaxPointsForGuest($earnItems, $websiteId, $mergeRuleIds)
        );
    }

    /**
     * Test calculateMaxPointsForGuest method if no items specified
     */
    public function testCalculateMaxPointsForGuestNoItems()
    {
        $websiteId = 2;
        $earnItems = [];

        $resultMock = $this->createMock(ResultInterface::class);

        $this->calculatorMock->expects($this->never())
            ->method('calculate');
        $this->calculatorMock->expects($this->once())
            ->method('getEmptyResult')
            ->willReturn($resultMock);

        $this->assertSame(
            $resultMock,
            $this->predictor->calculateMaxPointsForGuest($earnItems, $websiteId)
        );
    }

    /**
     * Test calculateMaxPointsForCustomerGroup method
     */
    public function testCalculateMaxPointsForCustomerGroup()
    {
        $customerGroupId = 10;
        $websiteId = 2;

        $this->configMock->expects($this->never())
            ->method('getDefaultCustomerGroupIdForGuest');

        $earnItemFirstMock = $this->createMock(EarnItemInterface::class);
        $earnItemSecondMock = $this->createMock(EarnItemInterface::class);
        $earnItems = [$earnItemFirstMock, $earnItemSecondMock];

        $resultFirstMock = $this->getCalculationResultMock(100);
        $resultSecondMock = $this->getCalculationResultMock(120);

        $this->calculatorMock->expects($this->exactly(2))
            ->method('calculateByCustomerGroup')
            ->withConsecutive(
                [[$earnItemFirstMock], $customerGroupId, $websiteId],
                [[$earnItemSecondMock], $customerGroupId, $websiteId]
            )
            ->willReturnOnConsecutiveCalls($resultFirstMock, $resultSecondMock);

        $this->assertSame(
            $resultSecondMock,
            $this->predictor->calculateMaxPointsForCustomerGroup($earnItems, $websiteId, $customerGroupId)
        );
    }

    /**
     * Test calculateMaxPointsForCustomerGroup method if merge rule ids enabled
     */
    public function testCalculateMaxPointsForCustomerGroupMergeRuleIds()
    {
        $customerGroupId = 10;
        $websiteId = 2;
        $mergeRuleIds = true;

        $this->configMock->expects($this->never())
            ->method('getDefaultCustomerGroupIdForGuest');

        $earnItemFirstMock = $this->createMock(EarnItemInterface::class);
        $earnItemSecondMock = $this->createMock(EarnItemInterface::class);
        $earnItemThirdMock = $this->createMock(EarnItemInterface::class);
        $earnItems = [$earnItemFirstMock, $earnItemSecondMock, $earnItemThirdMock];

        $resultFirstMock = $this->getCalculationResultMock(100, [1, 3]);
        $resultSecondMock = $this->getCalculationResultMock(120, [2], [1, 3, 2]);
        $resultThirdMock = $this->getCalculationResultMock(90, [2, 3]);

        $this->calculatorMock->expects($this->exactly(3))
            ->method('calculateByCustomerGroup')
            ->withConsecutive(
                [[$earnItemFirstMock], $customerGroupId, $websiteId],
                [[$earnItemSecondMock], $customerGroupId, $websiteId],
                [[$earnItemThirdMock], $customerGroupId, $websiteId]
            )
            ->willReturnOnConsecutiveCalls($resultFirstMock, $resultSecondMock, $resultThirdMock);

        $this->assertSame(
            $resultSecondMock,
            $this->predictor->calculateMaxPointsForCustomerGroup(
                $earnItems,
                $websiteId,
                $customerGroupId,
                $mergeRuleIds
            )
        );
    }

    /**
     * Test calculateMaxPointsForCustomerGroup method if no items specified
     */
    public function testCalculateMaxPointsForCustomerGroupNoItems()
    {
        $customerGroupId = 12;
        $websiteId = 2;
        $earnItems = [];

        $this->configMock->expects($this->never())
            ->method('getDefaultCustomerGroupIdForGuest');

        $resultMock = $this->createMock(ResultInterface::class);

        $this->calculatorMock->expects($this->never())
            ->method('calculate');
        $this->calculatorMock->expects($this->once())
            ->method('getEmptyResult')
            ->willReturn($resultMock);

        $this->assertSame(
            $resultMock,
            $this->predictor->calculateMaxPointsForCustomerGroup($earnItems, $websiteId, $customerGroupId)
        );
    }

    /**
     * Get calculation result mock
     *
     * @param float $points
     * @param int[] $ruleIds
     * @return ResultInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getCalculationResultMock($points, $ruleIds = [], $newRuleIds = [])
    {
        $resultMock = $this->createMock(ResultInterface::class);
        $resultMock->expects($this->any())
            ->method('getPoints')
            ->willReturn($points);
        $resultMock->expects($this->any())
            ->method('getAppliedRuleIds')
            ->willReturn($ruleIds);

        if (!empty($newRuleIds)) {
            $resultMock->expects($this->once())
                ->method('setAppliedRuleIds')
                ->with($newRuleIds)
                ->willReturnSelf($newRuleIds);
        }

        return $resultMock;
    }
}

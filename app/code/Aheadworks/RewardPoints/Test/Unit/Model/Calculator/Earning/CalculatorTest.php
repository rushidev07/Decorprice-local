<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Calculator\Earning;

use Aheadworks\RewardPoints\Api\Data\EarnRateInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\Calculator;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemInterface;
use Aheadworks\RewardPoints\Model\Calculator\RateCalculator;
use Aheadworks\RewardPoints\Model\EarnRule\Applier;
use Aheadworks\RewardPoints\Model\Calculator\ResultInterface;
use Aheadworks\RewardPoints\Model\Calculator\ResultInterfaceFactory;
use Aheadworks\RewardPoints\Model\Calculator\Earning\Calculator\RateResolver;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\RewardPoints\Model\Calculator\Earning\Calculator
 */
class CalculatorTest extends TestCase
{
    /**
     * @var Calculator
     */
    private $calculator;

    /**
     * @var RateCalculator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rateCalculatorMock;

    /**
     * @var RateResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rateResolverMock;

    /**
     * @var Applier|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ruleApplierMock;

    /**
     * @var ResultInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->rateCalculatorMock = $this->createMock(RateCalculator::class);
        $this->rateResolverMock = $this->createMock(RateResolver::class);
        $this->ruleApplierMock = $this->createMock(Applier::class);
        $this->resultFactoryMock = $this->createMock(ResultInterfaceFactory::class);

        $this->calculator = $objectManager->getObject(
            Calculator::class,
            [
                'rateCalculator' => $this->rateCalculatorMock,
                'rateResolver' => $this->rateResolverMock,
                'ruleApplier' => $this->ruleApplierMock,
                'resultFactory' => $this->resultFactoryMock
            ]
        );
    }

    /**
     * Test calculate method
     */
    public function testCalculate()
    {
        $items = [
            $this->getEarnItemMock(125, 20.5, 2),
            $this->getEarnItemMock(126, 10, 1)
        ];
        $customerId = 11;
        $websiteId = 3;

        $this->rateCalculatorMock->expects($this->exactly(2))
            ->method('calculateEarnPointsRaw')
            ->withConsecutive(
                [$customerId, 20.5, $websiteId],
                [$customerId, 10, $websiteId]
            )
            ->willReturnOnConsecutiveCalls(205, 100);

        $this->ruleApplierMock->expects($this->exactly(2))
            ->method('apply')
            ->withConsecutive(
                [205, 2, 125, $customerId, $websiteId],
                [100, 1, 126, $customerId, $websiteId]
            )
            ->willReturnOnConsecutiveCalls($this->getResultMock(300, [7]), $this->getResultMock(400, [8]));

        $resultMock = $this->getResultMock(700, [7, 8], true);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultMock);

        $this->assertEquals($resultMock, $this->calculator->calculate($items, $customerId, $websiteId));
    }

    /**
     * Test calculate method when need to round points amounts for multiple items
     */
    public function testCalculateRoundAmountsForMultipleItems()
    {
        $items = [
            $this->getEarnItemMock(11, 27.5, 1),
            $this->getEarnItemMock(12, 30, 1),
            $this->getEarnItemMock(13, 37.5, 1)
        ];
        $customerId = 1;
        $websiteId = 1;

        $this->rateCalculatorMock->expects($this->exactly(3))
            ->method('calculateEarnPointsRaw')
            ->withConsecutive(
                [$customerId, 27.5, $websiteId],
                [$customerId, 30, $websiteId],
                [$customerId, 37.5, $websiteId]
            )
            ->willReturnOnConsecutiveCalls(2.75, 3, 3.75);

        $this->ruleApplierMock->expects($this->exactly(3))
            ->method('apply')
            ->withConsecutive(
                [2.75, 1, 11, $customerId, $websiteId],
                [3, 1, 12, $customerId, $websiteId],
                [3.75, 1, 13, $customerId, $websiteId]
            )
            ->willReturnOnConsecutiveCalls(
                $this->getResultMock(13, [7]),
                $this->getResultMock(15, [7]),
                $this->getResultMock(3, [])
            );

        $resultMock = $this->getResultMock(31, [7], true);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultMock);

        $this->assertEquals($resultMock, $this->calculator->calculate($items, $customerId, $websiteId));
    }

    /**
     * Test calculate method if no items specified
     */
    public function testCalculateNoItems()
    {
        $items = [];
        $customerId = 11;
        $websiteId = 3;

        $this->rateCalculatorMock->expects($this->never())
            ->method('calculateEarnPointsRaw');

        $this->ruleApplierMock->expects($this->never())
            ->method('apply');

        $resultMock = $this->getResultMock(0, [], true);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultMock);

        $this->assertEquals($resultMock, $this->calculator->calculate($items, $customerId, $websiteId));
    }

    /**
     * Test calculate method if empty item specified
     */
    public function testCalculateEmptyItem()
    {
        $items = [
            $this->getEarnItemMock(null, 0, 0),
        ];
        $customerId = 11;
        $websiteId = 3;

        $this->rateCalculatorMock->expects($this->once())
            ->method('calculateEarnPointsRaw')
            ->with($customerId, 0, $websiteId)
            ->willReturn(0);

        $this->ruleApplierMock->expects($this->never())
            ->method('apply');

        $resultMock = $this->getResultMock(0, [], true);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultMock);

        $this->assertEquals($resultMock, $this->calculator->calculate($items, $customerId, $websiteId));
    }

    /**
     * Test calculateByCustomerGroup method
     */
    public function testCalculateByCustomerGroup()
    {
        $items = [
            $this->getEarnItemMock(125, 20.5, 2),
            $this->getEarnItemMock(126, 10, 1)
        ];
        $customerGroupId = 5;
        $websiteId = 3;

        $earnRateMock = $this->createMock(EarnRateInterface::class);
        $this->rateResolverMock->expects($this->once())
            ->method('getEarnRate')
            ->with($customerGroupId, $websiteId)
            ->willReturn($earnRateMock);
        $this->rateCalculatorMock->expects($this->exactly(2))
            ->method('calculateEarnPointsByRateRaw')
            ->withConsecutive(
                [$earnRateMock, 20.5],
                [$earnRateMock, 10]
            )
            ->willReturnOnConsecutiveCalls(205, 100);

        $this->ruleApplierMock->expects($this->exactly(2))
            ->method('applyByCustomerGroup')
            ->withConsecutive(
                [205, 2, 125, $customerGroupId, $websiteId],
                [100, 1, 126, $customerGroupId, $websiteId]
            )
            ->willReturnOnConsecutiveCalls(
                $this->getResultMock(300, [7]),
                $this->getResultMock(400, [8])
            );

        $resultMock = $this->getResultMock(700, [7, 8], true);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultMock);

        $this->assertEquals(
            $resultMock,
            $this->calculator->calculateByCustomerGroup($items, $customerGroupId, $websiteId)
        );
    }

    /**
     * Test calculateByCustomerGroup method if no rate found
     */
    public function testCalculateByCustomerGroupNoRate()
    {
        $items = [
            $this->getEarnItemMock(125, 20.5, 2),
            $this->getEarnItemMock(126, 10, 1)
        ];
        $customerGroupId = 5;
        $websiteId = 3;

        $this->rateResolverMock->expects($this->once())
            ->method('getEarnRate')
            ->with($customerGroupId, $websiteId)
            ->willReturn(null);
        $this->rateCalculatorMock->expects($this->never())
            ->method('calculateEarnPointsByRateRaw');

        $this->ruleApplierMock->expects($this->exactly(2))
            ->method('applyByCustomerGroup')
            ->withConsecutive(
                [0, 2, 125, $customerGroupId, $websiteId],
                [0, 1, 126, $customerGroupId, $websiteId]
            )
            ->willReturnOnConsecutiveCalls(
                $this->getResultMock(300, [7]),
                $this->getResultMock(400, [8])
            );

        $resultMock = $this->getResultMock(700, [7, 8], true);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultMock);

        $this->assertEquals(
            $resultMock,
            $this->calculator->calculateByCustomerGroup($items, $customerGroupId, $websiteId)
        );
    }

    /**
     * Test calculateByCustomerGroup method if no items specified
     */
    public function testCalculateByCustomerGroupNoItems()
    {
        $items = [];
        $customerGroupId = 5;
        $websiteId = 3;

        $earnRateMock = $this->createMock(EarnRateInterface::class);
        $this->rateResolverMock->expects($this->once())
            ->method('getEarnRate')
            ->with($customerGroupId, $websiteId)
            ->willReturn($earnRateMock);

        $this->rateCalculatorMock->expects($this->never())
            ->method('calculateEarnPointsByRateRaw');

        $this->ruleApplierMock->expects($this->never())
            ->method('apply');

        $resultMock = $this->getResultMock(0, [], true);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultMock);

        $this->assertEquals(
            $resultMock,
            $this->calculator->calculateByCustomerGroup($items, $customerGroupId, $websiteId)
        );
    }

    /**
     * Test calculateByCustomerGroup method if empty item specified
     */
    public function testCalculateByCustomerGroupEmptyItem()
    {
        $items = [
            $this->getEarnItemMock(null, 0, 0),
        ];
        $customerGroupId = 5;
        $websiteId = 3;

        $earnRateMock = $this->createMock(EarnRateInterface::class);
        $this->rateResolverMock->expects($this->once())
            ->method('getEarnRate')
            ->with($customerGroupId, $websiteId)
            ->willReturn($earnRateMock);

        $this->rateCalculatorMock->expects($this->once())
            ->method('calculateEarnPointsByRateRaw')
            ->with($earnRateMock, 0)
            ->willReturn(0);

        $this->ruleApplierMock->expects($this->never())
            ->method('apply');

        $resultMock = $this->getResultMock(0, [], true);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultMock);

        $this->assertEquals(
            $resultMock,
            $this->calculator->calculateByCustomerGroup($items, $customerGroupId, $websiteId)
        );
    }

    /**
     * Test getEmptyResult method
     */
    public function testGetEmptyResult()
    {
        $resultMock = $this->getResultMock(0, [], true);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultMock);

        $this->assertEquals($resultMock, $this->calculator->getEmptyResult());
    }

    /**
     * Get earn item mock
     *
     * @param int $productId
     * @param float $baseAmount
     * @param float $qty
     * @return EarnItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getEarnItemMock($productId, $baseAmount, $qty)
    {
        $itemMock = $this->createMock(EarnItemInterface::class);
        $itemMock->expects($this->any())
            ->method('getProductId')
            ->willReturn($productId);
        $itemMock->expects($this->any())
            ->method('getBaseAmount')
            ->willReturn($baseAmount);
        $itemMock->expects($this->any())
            ->method('getQty')
            ->willReturn($qty);

        return $itemMock;
    }

    /**
     * Get result mock
     *
     * @param float $pointsFinal
     * @param int[] $appliedRuleIds
     * @param bool|false $forceSet
     * @return ResultInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getResultMock($pointsFinal, $appliedRuleIds, $forceSet = false)
    {
        $resultMock = $this->createMock(ResultInterface::class);
        if ($forceSet) {
            $resultMock->expects($this->once())
                ->method('setPoints')
                ->with($pointsFinal)
                ->willReturnSelf();
            $resultMock->expects($this->once())
                ->method('setAppliedRuleIds')
                ->with($appliedRuleIds)
                ->willReturnSelf();
        } else {
            $resultMock->expects($this->any())
                ->method('getPoints')
                ->willReturn($pointsFinal);
            $resultMock->expects($this->any())
                ->method('getAppliedRuleIds')
                ->willReturn($appliedRuleIds);
        }

        return $resultMock;
    }
}

<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\EarnRule\Applier;

use Aheadworks\RewardPoints\Model\EarnRule\Applier\ActionApplier;
use Aheadworks\RewardPoints\Api\Data\ActionInterface;
use Aheadworks\RewardPoints\Model\EarnRule\Action\Type as ActionType;
use Aheadworks\RewardPoints\Model\EarnRule\Action\TypePool as ActionTypePool;
use Aheadworks\RewardPoints\Model\EarnRule\Action\ProcessorInterface as ActionProcessorInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Api\AttributeInterface;

/**
 * Test for \Aheadworks\RewardPoints\Model\EarnRule\Applier\ActionApplier
 */
class ActionApplierTest extends TestCase
{
    /**
     * @var ActionApplier
     */
    private $actionApplier;

    /**
     * @var ActionTypePool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $actionTypePoolMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->actionTypePoolMock = $this->createMock(ActionTypePool::class);

        $this->actionApplier = $objectManager->getObject(
            ActionApplier::class,
            [
                'actionTypePool' => $this->actionTypePoolMock
            ]
        );
    }

    /**
     * Test apply method
     */
    public function testApply()
    {
        $points = 10;
        $actionTypeCode = 'sample_action';
        $actionAttributes = [$this->createMock(AttributeInterface::class)];
        $result = 20;
        $qty = 1;

        $actionMock = $this->createMock(ActionInterface::class);
        $actionMock->expects($this->once())
            ->method('getType')
            ->willReturn($actionTypeCode);
        $actionMock->expects($this->once())
            ->method('getAttributes')
            ->willReturn($actionAttributes);

        $actionType = $this->createMock(ActionType::class);
        $this->actionTypePoolMock->expects($this->once())
            ->method('getTypeByCode')
            ->with($actionTypeCode)
            ->willReturn($actionType);

        $actionProcessorMock = $this->createMock(ActionProcessorInterface::class);
        $actionType->expects($this->once())
            ->method('getProcessor')
            ->willReturn($actionProcessorMock);

        $actionProcessorMock->expects($this->once())
            ->method('process')
            ->with($points, $qty, $actionAttributes)
            ->willReturn($result);

        $this->assertEquals($result, $this->actionApplier->apply($points, $qty, $actionMock));
    }

    /**
     * Test apply method if an error accurs
     */
    public function testApplyException()
    {
        $points = 10;
        $qty = 1;
        $actionTypeCode = 'sample_action';

        $actionMock = $this->createMock(ActionInterface::class);
        $actionMock->expects($this->once())
            ->method('getType')
            ->willReturn($actionTypeCode);

        $this->actionTypePoolMock->expects($this->once())
            ->method('getTypeByCode')
            ->with($actionTypeCode)
            ->willThrowException(new \Exception('Unknown action type: sample_action requested'));

        $this->assertEquals($points, $this->actionApplier->apply($points, $qty, $actionMock));
    }
}

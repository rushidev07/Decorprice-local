<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\EarnRule\Action\Processor;

use Aheadworks\RewardPoints\Model\EarnRule\Action\Processor\FixedAmount;
use Aheadworks\RewardPoints\Model\Action\AttributeProcessor;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\DataObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\RewardPoints\Model\EarnRule\Action\Processor\FixedAmount
 */
class FixedAmountTest extends TestCase
{
    /**
     * @var FixedAmount
     */
    private $processor;

    /**
     * @var AttributeProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeProcessorMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->attributeProcessorMock = $this->createMock(AttributeProcessor::class);

        $this->processor = $objectManager->getObject(
            FixedAmount::class,
            [
                'attributeProcessor' => $this->attributeProcessorMock,
            ]
        );
    }

    /**
     * Test process method
     *
     * @param float $points
     * @param float $qty
     * @param float $amount
     * @param $result
     * @dataProvider processDataProvider
     */
    public function testProcess($points, $qty, $amount, $result)
    {
        $attributes = [$this->createMock(AttributeInterface::class)];

        $this->attributeProcessorMock->expects($this->once())
            ->method('getAttributeValueByCode')
            ->with('amount', $attributes)
            ->willReturn($amount);

        $this->assertEquals($result, $this->processor->process($points, $qty, $attributes));
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            [
                'points' => 0,
                'qty' => 1,
                'amount' => 1.5,
                'result' => 1.5
            ],
            [
                'points' => 4,
                'qty' => 1,
                'amount' => 1.5,
                'result' => 5.5
            ],
            [
                'points' => 4,
                'qty' => 2,
                'amount' => 1.5,
                'result' => 7
            ],
            [
                'points' => 4,
                'qty' => 2,
                'amount' => 0,
                'result' => 4
            ],
        ];
    }
}

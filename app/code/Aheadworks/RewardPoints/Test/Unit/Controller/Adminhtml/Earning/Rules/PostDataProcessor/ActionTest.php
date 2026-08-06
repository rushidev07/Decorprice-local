<?php
namespace Aheadworks\RewardPoints\Test\Unit\Controller\Adminhtml\Earning\Rules\PostDataProcessor;

use Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\PostDataProcessor\Action;
use Aheadworks\RewardPoints\Api\Data\ActionInterface;
use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Model\Action\Converter as ActionConverter;
use Aheadworks\RewardPoints\Model\EarnRule\Action\TypePool as ActionTypePool;
use Aheadworks\RewardPoints\Model\Action as RuleAction;
use Aheadworks\RewardPoints\Model\EarnRule\Action\TypeInterface as ActionTypeInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Exception\ConfigurationMismatchException;

/**
 * Test for \Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\PostDataProcessor\Action
 */
class ActionTest extends TestCase
{
    /**
     * @var Action
     */
    private $processor;

    /**
     * @var ActionConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $actionConverterMock;

    /**
     * @var ActionTypePool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $actionTypePool;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->actionConverterMock = $this->createMock(ActionConverter::class);
        $this->actionTypePool = $this->createMock(ActionTypePool::class);

        $this->processor = $objectManager->getObject(
            Action::class,
            [
                'actionConverter' => $this->actionConverterMock,
                'actionTypePool' => $this->actionTypePool,
            ]
        );
    }

    /**
     * Test process method
     *
     * @param array $data
     * @param string|null $typeCode
     * @param ActionTypeInterface|\PHPUnit_Framework_MockObject_MockObject|null $actionType
     * @param ActionInterface|\PHPUnit_Framework_MockObject_MockObject $action
     * @param array $actionData
     * @param array $result
     * @dataProvider processDataProvider
     */
    public function testProcess($data, $typeCode, $actionType, $actionData, $action, $result)
    {
        if ($typeCode) {
            $this->actionTypePool->expects($this->once())
                ->method('getTypeByCode')
                ->with($typeCode)
                ->willReturn($actionType);

            $this->actionConverterMock->expects($this->once())
                ->method('arrayToDataModel')
                ->with($actionData)
                ->willReturn($action);
        }

        $this->assertEquals($result, $this->processor->process($data));
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        $actionTypeCode = 'type_code';

        $attributeOneCode = 'one';
        $attributeOneValue = '100';
        $attributeTwoCode = 'two';
        $attributeTwoValue = 'TWO';

        $attributeCodes = [$attributeOneCode, $attributeTwoCode];

        $actionTypeMock = $this->createMock(ActionTypeInterface::class);
        $actionTypeMock->expects($this->any())
            ->method('getAttributeCodes')
            ->willReturn($attributeCodes);

        $actionMock = $this->createMock(ActionInterface::class);

        return [
            [
                'data' => [
                    'sample' => 'data'
                ],
                'typeCode' => null,
                'actionType' => null,
                'actionData' => [],
                'action' => null,
                'result' => [
                    'sample' => 'data'
                ]
            ],
            [
                'data' => [
                    EarnRuleInterface::ACTION => [],
                    'sample' => 'data'
                ],
                'typeCode' => null,
                'actionType' => null,
                'actionData' => [],
                'action' => null,
                'result' => [
                    EarnRuleInterface::ACTION => [],
                    'sample' => 'data'
                ]
            ],
            [
                'data' => [
                    EarnRuleInterface::ACTION => [
                        RuleAction::TYPE => $actionTypeCode,
                        $attributeOneCode => $attributeOneValue,
                        $attributeTwoCode => $attributeTwoValue,
                    ],
                    'sample' => 'data'
                ],
                'typeCode' => $actionTypeCode,
                'actionType' => $actionTypeMock,
                'actionData' => [
                    RuleAction::TYPE => $actionTypeCode,
                    RuleAction::ATTRIBUTES => [
                        $attributeOneCode => $attributeOneValue,
                        $attributeTwoCode => $attributeTwoValue,
                    ]
                ],
                'action' => $actionMock,
                'result' => [
                    EarnRuleInterface::ACTION => $actionMock,
                    'sample' => 'data',
                ]
            ],
            [
                'data' => [
                    EarnRuleInterface::ACTION => [
                        RuleAction::TYPE => $actionTypeCode,
                    ],
                    'sample' => 'data'
                ],
                'typeCode' => $actionTypeCode,
                'actionType' => $actionTypeMock,
                'actionData' => [
                    RuleAction::TYPE => $actionTypeCode,
                    RuleAction::ATTRIBUTES => []
                ],
                'action' => $actionMock,
                'result' => [
                    EarnRuleInterface::ACTION => $actionMock,
                    'sample' => 'data',
                ]
            ],
        ];
    }

    /**
     * Test process method if an exception occurs
     */
    public function testProcessException()
    {
        $typeCode = 'sample_type';
        $data = [
            EarnRuleInterface::ACTION => [
                RuleAction::TYPE => $typeCode,
            ]
        ];
        $exceptionMessage = __('Unknown action type: %1 requested', $typeCode);

        $this->actionTypePool->expects($this->once())
            ->method('getTypeByCode')
            ->with($typeCode)
            ->willThrowException(
                new ConfigurationMismatchException($exceptionMessage)
            );

        $this->expectException(ConfigurationMismatchException::class);

        $this->processor->process($data);
    }
}

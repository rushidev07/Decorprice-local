<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Action;

use Aheadworks\RewardPoints\Model\Action\Converter;
use Aheadworks\RewardPoints\Api\Data\ActionInterface;
use Aheadworks\RewardPoints\Api\Data\ActionInterfaceFactory;
use Aheadworks\RewardPoints\Model\Action;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\AttributeInterfaceFactory;

/**
 * Test for \Aheadworks\RewardPoints\Model\Action\Converter
 */
class ConverterTest extends TestCase
{
    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var ActionInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $actionFactoryMock;

    /**
     * @var AttributeInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeFactoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->actionFactoryMock = $this->createMock(ActionInterfaceFactory::class);
        $this->attributeFactoryMock = $this->createMock(AttributeInterfaceFactory::class);

        $this->converter = $objectManager->getObject(
            Converter::class,
            [
                'actionFactory' => $this->actionFactoryMock,
                'attributeFactory' => $this->attributeFactoryMock,
            ]
        );
    }

    /**
     * Test arrayToDataModel method
     */
    public function testArrayToDataModel()
    {
        $type = 'sample_type';
        $attributeOneCode = 'code_one';
        $attributeOneValue = '111';
        $attributeTwoCode = 'code_two';
        $attributeTwoValue = 'TWO';

        $data = [
            Action::TYPE => $type,
            Action::ATTRIBUTES => [
                $attributeOneCode => $attributeOneValue,
                $attributeTwoCode => $attributeTwoValue,
            ]
        ];

        $actionMock = $this->createMock(ActionInterface::class);
        $this->actionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($actionMock);

        $attributeOneMock = $this->getAttributeMock($attributeOneCode, $attributeOneValue);
        $attributeTwoMock = $this->getAttributeMock($attributeTwoCode, $attributeTwoValue);

        $this->attributeFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls($attributeOneMock, $attributeTwoMock);

        $this->assertSame($actionMock, $this->converter->arrayToDataModel($data));
    }

    /**
     * Test dataModelToArray method
     *
     * @param ActionInterface|\PHPUnit_Framework_MockObject_MockObject $displayAction
     * @param array $result
     * @dataProvider dataModelToArrayDataProvider
     */
    public function testDataModelToArray($displayAction, $result)
    {
        $this->assertEquals($result, $this->converter->dataModelToArray($displayAction));
    }

    /**
     * @return array
     */
    public function dataModelToArrayDataProvider()
    {
        $type = 'sample_type';
        $attributeOneCode = 'code_one';
        $attributeOneValue = '111';
        $attributeOneMock = $this->getAttributeMock($attributeOneCode, $attributeOneValue);
        $attributeTwoCode = 'code_two';
        $attributeTwoValue = 'TWO';
        $attributeTwoMock = $this->getAttributeMock($attributeTwoCode, $attributeTwoValue);

        return [
            [
                'displayAction' => $this->getActionMock($type, []),
                'result' => [
                    Action::TYPE => $type,
                    Action::ATTRIBUTES => []
                ]
            ],
            [
                'displayAction' => $this->getActionMock($type, [$attributeOneMock]),
                'result' => [
                    Action::TYPE => $type,
                    Action::ATTRIBUTES => [
                        $attributeOneCode => $attributeOneValue,
                    ]
                ]
            ],
            [
                'displayAction' => $this->getActionMock($type, [$attributeOneMock, $attributeTwoMock]),
                'result' => [
                    Action::TYPE => $type,
                    Action::ATTRIBUTES => [
                        $attributeOneCode => $attributeOneValue,
                        $attributeTwoCode => $attributeTwoValue,
                    ]
                ]
            ],
        ];
    }

    /**
     * Get attribute mock
     *
     * @param string $code
     * @param string $value
     * @return AttributeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getAttributeMock($code, $value)
    {
        $attributeMock = $this->createMock(AttributeInterface::class);
        $attributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn($code);
        $attributeMock->expects($this->any())
            ->method('setAttributeCode')
            ->with($code)
            ->willReturnSelf();
        $attributeMock->expects($this->any())
            ->method('getValue')
            ->willReturn($value);
        $attributeMock->expects($this->any())
            ->method('setValue')
            ->with($value)
            ->willReturnSelf();

        return $attributeMock;
    }

    /**
     * Get action mock
     *
     * @param string $type
     * @param AttributeInterface[]|\PHPUnit_Framework_MockObject_MockObject[] $attributes
     * @return ActionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getActionMock($type, $attributes)
    {
        $actionMock = $this->createMock(ActionInterface::class);
        $actionMock->expects($this->any())
            ->method('getType')
            ->willReturn($type);
        $actionMock->expects($this->any())
            ->method('getAttributes')
            ->willReturn($attributes);

        return $actionMock;
    }
}

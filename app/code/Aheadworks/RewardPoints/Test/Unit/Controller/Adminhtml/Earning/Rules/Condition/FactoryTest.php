<?php
namespace Aheadworks\RewardPoints\Test\Unit\Controller\Adminhtml\Earning\Rules\Condition;

use Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\Condition\Factory as RuleConditionFactory;
use Magento\Rule\Model\Condition\AbstractCondition;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\CatalogRule\Model\Rule\Condition\Product as ProductCondition;
use Magento\CatalogRule\Model\Rule;
use Magento\Framework\Exception\ConfigurationMismatchException;

/**
 * Test for \Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\Condition\Factory
 */
class FactoryTest extends TestCase
{
    /**
     * @var RuleConditionFactory
     */
    private $factory;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);

        $this->factory = $objectManager->getObject(
            RuleConditionFactory::class,
            [
                'objectManager' => $this->objectManagerMock,
            ]
        );
    }

    /**
     * Test process method
     *
     * @param string|null $attribute
     * @dataProvider processDataProvider
     * @throws \Exception
     */
    public function testProcess($attribute)
    {
        $type = ProductCondition::class;
        $id = '1--1';
        $prefix = 'conditions';
        $jsFormObject = 'rule_conditions_fieldset';
        $formName = 'aw_reward_points_earning_rules_form';

        $conditionMock = $this->createMock($type);
        $ruleMock = $this->createMock(Rule::class);

        $this->setupMocks($conditionMock, $id, $type, $ruleMock, $prefix, $attribute, $jsFormObject, $formName);

        $this->objectManagerMock->expects($this->exactly(2))
            ->method('create')
            ->withConsecutive([ProductCondition::class], [Rule::class])
            ->willReturnOnConsecutiveCalls($conditionMock, $ruleMock);

        $this->assertEquals(
            $conditionMock,
            $this->factory->create($type, $id, $prefix, $attribute, $jsFormObject, $formName)
        );
    }

    /**
     * Set up mocks
     *
     * @param ConditionCombine|\PHPUnit_Framework_MockObject_MockObject $conditionMock
     * @param string $id
     * @param string $type
     * @param Rule|\PHPUnit_Framework_MockObject_MockObject $ruleMock
     * @param string $prefix
     * @param string|null $attribute
     * @param string $jsFormObject
     * @param string $formName
     */
    private function setupMocks($conditionMock, $id, $type, $ruleMock, $prefix, $attribute, $jsFormObject, $formName)
    {
        $conditionMock->expects($this->any())
                      ->method('__call')
                      ->willReturnSelf();
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            ['attribute' => 'value'],
            ['attribute' => null]
        ];
    }

    /**
     * Test process method if incorrect condition specified
     */
    public function testProcessIncorrectCondition()
    {
        $type = DataObject::class;
        $id = '1--1';
        $prefix = 'conditions';
        $attribute = null;
        $jsFormObject = 'rule_conditions_fieldset';
        $formName = 'aw_reward_points_earning_rules_form';

        $dataObjectMock = $this->createMock($type);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(DataObject::class)
            ->willReturn($dataObjectMock);

        $this->expectException(ConfigurationMismatchException::class);

        $this->factory->create($type, $id, $prefix, $attribute, $jsFormObject, $formName);
    }
}

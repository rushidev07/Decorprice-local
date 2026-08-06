<?php
namespace Aheadworks\RewardPoints\Test\Unit\Controller\Adminhtml\Earning\Rules\PostDataProcessor;

use Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\PostDataProcessor\Condition;
use Aheadworks\RewardPoints\Api\Data\ConditionInterface;
use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Model\EarnRule\Condition\Rule as ConditionRule;
use Aheadworks\RewardPoints\Model\EarnRule\Condition\RuleFactory as ConditionRuleFactory;
use Aheadworks\RewardPoints\Model\EarnRule\Condition\Converter as ConditionConverter;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\CatalogRule\Model\Rule\Condition\Combine as CombineCondition;
use Magento\CatalogRule\Model\Rule\Condition\Product as ProductCondition;

/**
 * Test for \Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\PostDataProcessor\Condition
 */
class ConditionTest extends TestCase
{
    /**
     * @var Condition
     */
    private $processor;

    /**
     * @var ConditionConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $conditionConverterMock;

    /**
     * @var ConditionRuleFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $conditionRuleFactoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->conditionConverterMock = $this->createMock(ConditionConverter::class);
        $this->conditionRuleFactoryMock = $this->createMock(ConditionRuleFactory::class);

        $this->processor = $objectManager->getObject(
            Condition::class,
            [
                'conditionConverter' => $this->conditionConverterMock,
                'conditionRuleFactory' => $this->conditionRuleFactoryMock,
            ]
        );
    }

    /**
     * Test process method
     *
     * @param array $data
     * @param array $convertedData
     * @param ConditionInterface|\PHPUnit_Framework_MockObject_MockObject $condition
     * @param array $result
     * @dataProvider processDataProvider
     */
    public function testProcess($data, $convertedData, $condition, $result)
    {
        $this->conditionConverterMock->expects($this->once())
            ->method('arrayToDataModel')
            ->with($convertedData)
            ->willReturn($condition);

        $this->assertEquals($result, $this->processor->process($data));
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        $conditionMock = $this->createMock(ConditionInterface::class);
        return [
            [
                'data' => [
                    'rule' => [
                        ConditionRule::CONDITIONS_PREFIX => [
                            '1' => [
                                'type' => CombineCondition::class,
                                'aggregator' => 'all',
                                'value' => '1',
                                'new_child' => '',
                            ],
                            '1--1' => [
                                'type' => ProductCondition::class,
                                'attribute' => 'category_ids',
                                'operator' => '==',
                                'value' => '50',
                            ],
                        ],
                    ]
                ],
                'convertedData' => [
                    'type' => CombineCondition::class,
                    'aggregator' => 'all',
                    'value' => '1',
                    'new_child' => '',
                    'conditions' => [
                        '1' => [
                            'type' => ProductCondition::class,
                            'attribute' => 'category_ids',
                            'operator' => '==',
                            'value' => '50'
                        ]
                    ]
                ],
                'condition' => $conditionMock,
                'result' => [
                    'rule' => [
                        ConditionRule::CONDITIONS_PREFIX => [
                            '1' => [
                                'type' => CombineCondition::class,
                                'aggregator' => 'all',
                                'value' => '1',
                                'new_child' => '',
                            ],
                            '1--1' => [
                                'type' => ProductCondition::class,
                                'attribute' => 'category_ids',
                                'operator' => '==',
                                'value' => '50',
                            ],
                        ],
                    ],
                    EarnRuleInterface::CONDITION => $conditionMock
                ]
            ],
            [
                'data' => [
                    'rule' => []
                ],
                'convertedData' => [],
                'condition' => $conditionMock,
                'result' => [
                    'rule' => [],
                    EarnRuleInterface::CONDITION => $conditionMock
                ]
            ],
            [
                'data' => [],
                'convertedData' => [],
                'condition' => $conditionMock,
                'result' => [
                    EarnRuleInterface::CONDITION => $conditionMock
                ]
            ]
        ];
    }
}

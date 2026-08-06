<?php
namespace Aheadworks\RewardPoints\Model\EarnRule\Condition;

use Aheadworks\RewardPoints\Api\Data\ConditionInterface;
use Aheadworks\RewardPoints\Api\Data\ConditionInterfaceFactory;
use Aheadworks\RewardPoints\Model\EarnRule\Condition\Rule as ConditionRule;

/**
 * Class Converter
 * @package Aheadworks\RewardPoints\Model\EarnRule\Condition
 */
class Converter
{
    /**
     * @var ConditionInterfaceFactory
     */
    private $conditionFactory;

    /**
     * @param ConditionInterfaceFactory $conditionFactory
     */
    public function __construct(
        ConditionInterfaceFactory $conditionFactory
    ) {
        $this->conditionFactory = $conditionFactory;
    }

    /**
     * Convert recursive array into condition data model
     *
     * @param array $input
     * @return ConditionInterface
     */
    public function arrayToDataModel(array $input)
    {
        /** @var ConditionInterface $conditionModel */
        $conditionModel = $this->conditionFactory->create();
        foreach ($input as $key => $value) {
            switch ($key) {
                case 'type':
                    $conditionModel->setType($value);
                    break;
                case 'attribute':
                    $conditionModel->setAttribute($value);
                    break;
                case 'operator':
                    $conditionModel->setOperator($value);
                    break;
                case 'value':
                    $conditionModel->setValue($value);
                    break;
                case 'value_type':
                    $conditionModel->setValueType($value);
                    break;
                case 'aggregator':
                    $conditionModel->setAggregator($value);
                    break;
                case 'conditions':
                case ConditionRule::CONDITIONS_PREFIX:
                    $conditions = [];
                    /** @var array $condition */
                    foreach ($value as $condition) {
                        $conditions[] = $this->arrayToDataModel($condition);
                    }
                    $conditionModel->setConditions($conditions);
                    break;
                default:
            }
        }
        return $conditionModel;
    }

    /**
     * Convert recursive condition data model into array
     *
     * @param ConditionInterface $dataModel
     * @return array
     */
    public function dataModelToArray(ConditionInterface $dataModel)
    {
        $output = [
            'type' => $dataModel->getType(),
            'attribute' => $dataModel->getAttribute(),
            'operator' => $dataModel->getOperator(),
            'value' => $dataModel->getValue(),
            'value_type' => $dataModel->getValueType(),
            'aggregator' => $dataModel->getAggregator()
        ];

        foreach ((array)$dataModel->getConditions() as $conditions) {
            $output['conditions'][] = $this->dataModelToArray($conditions);
        }
        return $output;
    }
}

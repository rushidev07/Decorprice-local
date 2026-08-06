<?php
namespace Aheadworks\RewardPoints\Model\EarnRule\Condition\Rule;

use Aheadworks\RewardPoints\Model\EarnRule\Condition\Rule as ConditionRule;
use Aheadworks\RewardPoints\Model\EarnRule\Condition\RuleFactory as ConditionRuleFactory;
use Aheadworks\RewardPoints\Model\EarnRule\Condition\Converter as ConditionConverter;
use Aheadworks\RewardPoints\Api\Data\ConditionInterface;

/**
 * Class Loader
 * @package Aheadworks\RewardPoints\Model\EarnRule\Condition\Rule
 */
class Loader
{
    /**
     * @var ConditionRuleFactory
     */
    private $conditionRuleFactory;

    /**
     * @var ConditionConverter
     */
    private $conditionConverter;

    /**
     * @param ConditionConverter $conditionConverter
     * @param ConditionRuleFactory $conditionRuleFactory
     */
    public function __construct(
        ConditionConverter $conditionConverter,
        ConditionRuleFactory $conditionRuleFactory
    ) {
        $this->conditionConverter = $conditionConverter;
        $this->conditionRuleFactory = $conditionRuleFactory;
    }

    /**
     * Create condition rule by corresponding condition object
     *
     * @param ConditionInterface|null $condition
     * @return ConditionRule
     */
    public function loadRule($condition = null)
    {
        /** @var ConditionRule $conditionRule */
        $conditionRule = $this->conditionRuleFactory->create();
        if (empty($condition)) {
            $conditionRule->setConditions([])
                ->getConditions()
                ->asArray();
        } else {
            $conditionArray = $this->conditionConverter->dataModelToArray($condition);
            $conditionRule->setConditions([])
                ->getConditions()
                ->loadArray($conditionArray);
        }

        return $conditionRule;
    }
}

<?php
namespace Aheadworks\RewardPoints\Block\Adminhtml\EarnRule\Edit\Conditions\Form;

use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\Edit as RuleEditAction;
use Aheadworks\RewardPoints\Model\EarnRule\Condition\Rule\Loader as ConditionRuleLoader;
use Aheadworks\RewardPoints\Model\EarnRule\Condition\Rule as ConditionRule;
use Magento\Framework\Registry;

/**
 * Class DataProvider
 * @package Aheadworks\RewardPoints\Block\Adminhtml\EarnRule\Edit\Conditions\Form
 */
class DataProvider
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ConditionRuleLoader
     */
    private $conditionRuleLoader;

    /**
     * @param Registry $registry
     * @param ConditionRuleLoader $conditionRuleLoader
     */
    public function __construct(
        Registry $registry,
        ConditionRuleLoader $conditionRuleLoader
    ) {
        $this->registry = $registry;
        $this->conditionRuleLoader = $conditionRuleLoader;
    }

    /**
     * Get condition rule
     *
     * @return ConditionRule
     */
    public function getConditionRule()
    {
        /** @var EarnRuleInterface $rule */
        $rule = $this->registry->registry(RuleEditAction::CURRENT_RULE_KEY);
        $condition = $rule ? $rule->getCondition() : null;

        /** @var ConditionRule $ruleModel */
        $ruleModel = $this->conditionRuleLoader->loadRule($condition);

        return $ruleModel;
    }
}

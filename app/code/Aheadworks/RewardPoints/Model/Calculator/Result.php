<?php
namespace Aheadworks\RewardPoints\Model\Calculator;

use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Class Result
 * @package Aheadworks\RewardPoints\Model\Calculator
 * @codeCoverageIgnore
 */
class Result extends AbstractSimpleObject implements ResultInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPoints()
    {
        return $this->_get(self::POINTS);
    }

    /**
     * {@inheritdoc}
     */
    public function setPoints($points)
    {
        return $this->setData(self::POINTS, $points);
    }

    /**
     * {@inheritdoc}
     */
    public function getAppliedRuleIds()
    {
        return $this->_get(self::APPLIED_RULE_IDS);
    }

    /**
     * {@inheritdoc}
     */
    public function setAppliedRuleIds($ruleIds)
    {
        return $this->setData(self::APPLIED_RULE_IDS, $ruleIds);
    }
}

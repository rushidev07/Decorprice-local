<?php
namespace Aheadworks\RewardPoints\Model\Calculator;

/**
 * Interface ResultInterface
 * @package Aheadworks\RewardPoints\Model\Calculator
 */
interface ResultInterface
{
    /**#@+
     * Constants for keys.
     */
    const POINTS            = 'points';
    const APPLIED_RULE_IDS  = 'applied_rule_ids';
    /**#@-*/

    /**
     * Get points
     *
     * @return int
     */
    public function getPoints();

    /**
     * Set points
     *
     * @param int $points
     * @return $this
     */
    public function setPoints($points);

    /**
     * Get applied rule ids
     *
     * @return int[]
     */
    public function getAppliedRuleIds();

    /**
     * Set applied rule ids
     *
     * @param int[] $ruleIds
     * @return $this
     */
    public function setAppliedRuleIds($ruleIds);
}

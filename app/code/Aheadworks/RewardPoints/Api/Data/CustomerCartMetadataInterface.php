<?php
namespace Aheadworks\RewardPoints\Api\Data;

/**
 * Interface CustomerCartMetadataInterface
 *
 * @package Aheadworks\RewardPoints\Api\Data
 */
interface CustomerCartMetadataInterface
{
    /**#@+
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case
     */
    const REWARD_POINTS_BALANCE_QTY
        = "reward_points_balance_qty";
    const CAN_APPLY_REWARD_POINTS
        = "can_apply_reward_points";
    const REWARD_POINTS_MAX_ALLOWED_QTY_TO_APPLY
        = "reward_points_max_allowed_qty_to_apply";
    const REWARD_POINTS_CONVERSION_RATE_POINT_TO_CURRENCY_VALUE
        = "reward_points_conversion_rate_point_to_currency_value";
    const ARE_REWARD_POINTS_APPLIED
        = "are_reward_points_applied";
    const APPLIED_REWARD_POINTS_QTY
        = "applied_reward_points_qty";
    const APPLIED_REWARD_POINTS_AMOUNT
        = "applied_reward_points_amount";
    /**#@-*/

    /**
     * Get reward points balance qty of the specific customer
     *
     * @return int
     */
    public function getRewardPointsBalanceQty();

    /**
     * Set reward points balance qty of the specific customer
     *
     * @param int $rewardPointsBalanceQty
     * @return $this
     */
    public function setRewardPointsBalanceQty($rewardPointsBalanceQty);

    /**
     * Get flag if customer can apply reward points to the specific cart
     *
     * @return bool
     */
    public function getCanApplyRewardPoints();

    /**
     * Set flag if customer can apply reward points to the specific cart
     *
     * @param bool $canApplyRewardPoints
     * @return $this
     */
    public function setCanApplyRewardPoints($canApplyRewardPoints);

    /**
     * Retrieve max allowed qty of reward points to apply to the specific customer cart
     *
     * @return int
     */
    public function getRewardPointsMaxAllowedQtyToApply();

    /**
     * Set max allowed qty of reward points to apply to the specific customer cart
     *
     * @param int $rewardPointsMaxAllowedQtyToApply
     * @return $this
     */
    public function setRewardPointsMaxAllowedQtyToApply($rewardPointsMaxAllowedQtyToApply);

    /**
     * Retrieve value of conversion rate from reward points to the currency
     *
     * @return float
     */
    public function getRewardPointsConversionRatePointToCurrencyValue();

    /**
     * Set value of conversion rate from reward points to the currency
     *
     * @param float $rewardPointsConversionRatePointToCurrencyValue
     * @return $this
     */
    public function setRewardPointsConversionRatePointToCurrencyValue($rewardPointsConversionRatePointToCurrencyValue);

    /**
     * Get flag if reward points are already applied to the specific cart
     *
     * @return bool
     */
    public function getAreRewardPointsApplied();

    /**
     * Set flag if reward points are already applied to the specific cart
     *
     * @param bool $areRewardPointsApplied
     * @return $this
     */
    public function setAreRewardPointsApplied($areRewardPointsApplied);

    /**
     * Retrieve qty of already applied reward points for the specific customer cart
     *
     * @return int
     */
    public function getAppliedRewardPointsQty();

    /**
     * Set qty of already applied reward points for the specific customer cart
     *
     * @param int $appliedRewardPointsQty
     * @return $this
     */
    public function setAppliedRewardPointsQty($appliedRewardPointsQty);

    /**
     * Retrieve currency amount for already applied reward points for the specific customer cart
     *
     * @return float
     */
    public function getAppliedRewardPointsAmount();

    /**
     * Set currency amount for already applied reward points for the specific customer cart
     *
     * @param float $appliedRewardPointsAmount
     * @return $this
     */
    public function setAppliedRewardPointsAmount($appliedRewardPointsAmount);
}

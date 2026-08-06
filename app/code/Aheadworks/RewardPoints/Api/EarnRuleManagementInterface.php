<?php
namespace Aheadworks\RewardPoints\Api;

use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class EarnRuleManagementInterface
 * @package Aheadworks\RewardPoints\Api
 * @api
 */
interface EarnRuleManagementInterface
{
    /**
     * Enable the rule
     *
     * @param int $ruleId
     * @return EarnRuleInterface
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    public function enable($ruleId);

    /**
     * Disable the rule
     *
     * @param int $ruleId
     * @return EarnRuleInterface
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    public function disable($ruleId);

    /**
     * Create the rule
     *
     * @param array $ruleData
     * @return EarnRuleInterface
     * @throws CouldNotSaveException
     */
    public function createRule($ruleData);

    /**
     * Update the rule
     *
     * @param int $ruleId
     * @param array $ruleData
     * @return EarnRuleInterface
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    public function updateRule($ruleId, $ruleData);

    /**
     * Get active rules
     *
     * @return EarnRuleInterface[]
     */
    public function getActiveRules();
}

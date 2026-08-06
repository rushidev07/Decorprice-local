<?php
namespace Aheadworks\RewardPoints\Model\Calculator\Earning;

use Aheadworks\RewardPoints\Model\Calculator\ResultInterface;
use Aheadworks\RewardPoints\Model\Config;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Predictor
 * @package Aheadworks\RewardPoints\Model\Calculator\Earning
 */
class Predictor
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Calculator
     */
    private $calculator;

    /**
     * @param Config $config
     * @param Calculator $calculator
     */
    public function __construct(
        Config $config,
        Calculator $calculator
    ) {
        $this->config = $config;
        $this->calculator = $calculator;
    }

    /**
     * Calculate max possible earning points for a customer
     *
     * @param EarnItemInterface[] $items
     * @param int $customerId
     * @param int $websiteId
     * @param bool|false $mergeRuleIds
     * @return ResultInterface
     */
    public function calculateMaxPointsForCustomer($items, $customerId, $websiteId, $mergeRuleIds = false)
    {
        if (empty($items)) {
            return $this->calculator->getEmptyResult();
        }

        $results = [];
        foreach ($items as $item) {
            $results[] = $this->calculator->calculate([$item], $customerId, $websiteId);
        }

        /** @var ResultInterface $maxResult */
        $maxResult = reset($results);
        /** @var ResultInterface $result */
        foreach ($results as $result) {
            if ($result->getPoints() > $maxResult->getPoints()) {
                $maxResult = $result;
            }
        }

        if ($mergeRuleIds) {
            $maxResult->setAppliedRuleIds($this->getMergedAppliedRuleIds($results));
        }

        return $maxResult;
    }

    /**
     * Calculate max possible earning points for a customer group
     *
     * @param EarnItemInterface[] $items
     * @param int $websiteId
     * @param int $customerGroupId
     * @param bool|false $mergeRuleIds
     * @return ResultInterface
     */
    public function calculateMaxPointsForCustomerGroup($items, $websiteId, $customerGroupId, $mergeRuleIds = false)
    {
        if (empty($items)) {
            return $this->calculator->getEmptyResult();
        }

        /** @var ResultInterface[] $results */
        $results = [];
        foreach ($items as $item) {
            $results[] = $this->calculator->calculateByCustomerGroup([$item], $customerGroupId, $websiteId);
        }

        /** @var ResultInterface $maxResult */
        $maxResult = reset($results);
        foreach ($results as $result) {
            if ($result->getPoints() > $maxResult->getPoints()) {
                $maxResult = $result;
            }
        }

        if ($mergeRuleIds) {
            $maxResult->setAppliedRuleIds($this->getMergedAppliedRuleIds($results));
        }

        return $maxResult;
    }

    /**
     * Calculate max possible earning points for a guest
     *
     * @param EarnItemInterface[] $items
     * @param int $websiteId
     * @param bool|false $mergeRuleIds
     * @return ResultInterface
     */
    public function calculateMaxPointsForGuest($items, $websiteId, $mergeRuleIds = false)
    {
        $customerGroupId = $this->config->getDefaultCustomerGroupIdForGuest();
        $maxResult = $this->calculateMaxPointsForCustomerGroup($items, $websiteId, $customerGroupId, $mergeRuleIds);
        return $maxResult;
    }

    /**
     * @param ResultInterface[] $results
     * @return int[]
     * phpcs:disable Magento2.Performance.ForeachArrayMerge
     */
    private function getMergedAppliedRuleIds($results)
    {
        $appliedRuleIds = [];
            /** @var ResultInterface $result */
        foreach ($results as $result) {
            $appliedRuleIds = array_unique(array_merge($appliedRuleIds, $result->getAppliedRuleIds()));
        }

        return $appliedRuleIds;
    }
}

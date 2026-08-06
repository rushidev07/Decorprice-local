<?php
namespace Aheadworks\RewardPoints\Model\Calculator\Earning;

use Aheadworks\RewardPoints\Api\Data\EarnRateInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\Calculator\RateResolver;
use Aheadworks\RewardPoints\Model\Calculator\RateCalculator;
use Aheadworks\RewardPoints\Model\Calculator\ResultInterface;
use Aheadworks\RewardPoints\Model\Calculator\ResultInterfaceFactory;
use Aheadworks\RewardPoints\Model\EarnRule\Applier;

/**
 * Class Calculator
 * @package Aheadworks\RewardPoints\Model\Calculator\Earning
 */
class Calculator
{
    /**
     * @var RateCalculator
     */
    private $rateCalculator;

    /**
     * @var RateResolver
     */
    private $rateResolver;

    /**
     * @var Applier
     */
    private $ruleApplier;

    /**
     * @var ResultInterfaceFactory
     */
    private $resultFactory;

    /**
     * @param RateCalculator $rateCalculator
     * @param RateResolver $rateResolver
     * @param Applier $ruleApplier
     * @param ResultInterfaceFactory $resultFactory
     */
    public function __construct(
        RateCalculator $rateCalculator,
        RateResolver $rateResolver,
        Applier $ruleApplier,
        ResultInterfaceFactory $resultFactory
    ) {
        $this->rateCalculator = $rateCalculator;
        $this->rateResolver = $rateResolver;
        $this->ruleApplier = $ruleApplier;
        $this->resultFactory = $resultFactory;
    }

    /**
     * Calculate earning points for the customer
     *
     * @param EarnItemInterface[] $items
     * @param int $customerId
     * @param int $websiteId
     * @return ResultInterface
     * phpcs:disable Magento2.Performance.ForeachArrayMerge
     */
    public function calculate($items, $customerId, $websiteId)
    {
        $points = 0;
        $appliedRules = [];
        /** @var EarnItem $item */
        foreach ($items as $item) {
            $itemPoints = $this->rateCalculator->calculateEarnPointsRaw(
                $customerId,
                $item->getBaseAmount(),
                $websiteId
            );
            if ($item->getProductId()) {
                /** @var ResultInterface $applyResult */
                $applyResult = $this->ruleApplier->apply(
                    $itemPoints,
                    $item->getQty(),
                    $item->getProductId(),
                    $customerId,
                    $websiteId
                );
                $points += $applyResult->getPoints();
                $appliedRules = array_unique(array_merge($appliedRules, $applyResult->getAppliedRuleIds()));
            }
        }
        /** @var ResultInterface $result */
        $result = $this->resultFactory->create();
        $result
            ->setPoints((int)$points)
            ->setAppliedRuleIds($appliedRules);

        return $result;
    }

    /**
     * Calculate earning points for the customer group
     *
     * @param EarnItemInterface[] $items
     * @param int $customerGroupId
     * @param int $websiteId
     * @return ResultInterface
     * phpcs:disable Magento2.Performance.ForeachArrayMerge
     */
    public function calculateByCustomerGroup($items, $customerGroupId, $websiteId)
    {
        $points = 0;
        $appliedRules = [];
        /** @var EarnRateInterface|null $rate */
        $rate = $this->rateResolver->getEarnRate($customerGroupId, $websiteId);

        /** @var EarnItem $item */
        foreach ($items as $item) {
            $itemPoints = $rate
                ? $this->rateCalculator->calculateEarnPointsByRateRaw($rate, $item->getBaseAmount())
                : 0;
            if ($item->getProductId()) {
                /** @var ResultInterface $applyResult */
                $applyResult = $this->ruleApplier->applyByCustomerGroup(
                    $itemPoints,
                    $item->getQty(),
                    $item->getProductId(),
                    $customerGroupId,
                    $websiteId
                );
                $points += $applyResult->getPoints();
                $appliedRules = array_unique(array_merge($appliedRules, $applyResult->getAppliedRuleIds()));
            }
        }

        /** @var ResultInterface $result */
        $result = $this->resultFactory->create();
        $result
            ->setPoints((int)$points)
            ->setAppliedRuleIds($appliedRules);

        return $result;
    }

    /**
     * Get empty result
     *
     * @return ResultInterface
     */
    public function getEmptyResult()
    {
        /** @var ResultInterface $result */
        $result = $this->resultFactory->create();
        $result
            ->setPoints(0)
            ->setAppliedRuleIds([]);

        return $result;
    }
}

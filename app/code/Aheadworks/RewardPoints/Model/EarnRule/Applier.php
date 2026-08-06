<?php
namespace Aheadworks\RewardPoints\Model\EarnRule;

use Aheadworks\RewardPoints\Model\DateTime;
use Aheadworks\RewardPoints\Model\EarnRule\Applier\ActionApplier;
use Aheadworks\RewardPoints\Model\EarnRule\Applier\RuleLoader;
use Aheadworks\RewardPoints\Model\Calculator\ResultInterface;
use Aheadworks\RewardPoints\Model\Calculator\ResultInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Applier
 * @package Aheadworks\RewardPoints\Model\EarnRule
 */
class Applier
{
    /**
     * @var RuleLoader
     */
    private $ruleLoader;

    /**
     * @var ActionApplier
     */
    private $actionApplier;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ResultInterfaceFactory
     */
    private $resultFactory;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param RuleLoader $ruleLoader
     * @param ActionApplier $actionApplier
     * @param CustomerRepositoryInterface $customerRepository
     * @param ResultInterfaceFactory $resultFactory
     * @param DateTime $dateTime
     */
    public function __construct(
        RuleLoader $ruleLoader,
        ActionApplier $actionApplier,
        CustomerRepositoryInterface $customerRepository,
        ResultInterfaceFactory $resultFactory,
        DateTime $dateTime
    ) {
        $this->ruleLoader = $ruleLoader;
        $this->actionApplier = $actionApplier;
        $this->customerRepository = $customerRepository;
        $this->resultFactory = $resultFactory;
        $this->dateTime = $dateTime;
    }

    /**
     * Apply earning rules
     *
     * @param int $productId
     * @param float $qty
     * @param float $points
     * @param int $customerId
     * @param int $websiteId
     * @return ResultInterface
     */
    public function apply($points, $qty, $productId, $customerId, $websiteId)
    {
        $appliedRuleIds = [];
        try {
            /** @var CustomerInterface $customer */
            $customer = $this->customerRepository->getById($customerId);
            $result = $this->applyByCustomerGroup($points, $qty, $productId, $customer->getGroupId(), $websiteId);
        } catch (LocalizedException $e) {
            /** @var ResultInterface $result */
            $result = $this->resultFactory->create();
            $result
                ->setPoints((int)$points)
                ->setAppliedRuleIds($appliedRuleIds);
        }

        return $result;
    }

    /**
     * Apply earning rules by customer group
     *
     * @param int $productId
     * @param float $qty
     * @param float $points
     * @param int $customerGroupId
     * @param int $websiteId
     * @return ResultInterface
     */
    public function applyByCustomerGroup($points, $qty, $productId, $customerGroupId, $websiteId)
    {
        $appliedRuleIds = [];
        $currentDate = $this->dateTime->getTodayDate();

        $rules = $this->ruleLoader->getRulesForApply($productId, $customerGroupId, $websiteId, $currentDate);
        foreach ($rules as $rule) {
            $points = $this->actionApplier->apply($points, $qty, $rule->getAction());
            $appliedRuleIds[] = $rule->getId();
            if ($rule->getDiscardSubsequentRules()) {
                break;
            }
        }

        /** @var ResultInterface $result */
        $result = $this->resultFactory->create();
        $result
            ->setPoints((int)$points)
            ->setAppliedRuleIds($appliedRuleIds);

        return $result;
    }

    /**
     * Get applied rule ids
     *
     * @param int $productId
     * @param int $customerGroupId
     * @param int $websiteId
     * @return int[]
     */
    public function getAppliedRuleIds($productId, $customerGroupId, $websiteId)
    {
        $appliedRuleIds = [];
        $currentDate = $this->dateTime->getTodayDate();
        $rules = $this->ruleLoader->getRulesForApply($productId, $customerGroupId, $websiteId, $currentDate);
        foreach ($rules as $rule) {
            $appliedRuleIds[] = $rule->getId();
            if ($rule->getDiscardSubsequentRules()) {
                break;
            }
        }
        return $appliedRuleIds;
    }
}

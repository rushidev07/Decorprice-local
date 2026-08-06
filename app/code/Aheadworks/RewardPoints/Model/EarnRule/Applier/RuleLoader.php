<?php
namespace Aheadworks\RewardPoints\Model\EarnRule\Applier;

use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Api\Data\EarnRuleSearchResultsInterface;
use Aheadworks\RewardPoints\Api\EarnRuleRepositoryInterface;
use Aheadworks\RewardPoints\Model\ResourceModel\EarnRule as EarnRuleResource;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class RuleLoader
 * @package Aheadworks\RewardPoints\Model\EarnRule\Applier
 */
class RuleLoader
{
    /**
     * @var EarnRuleResource
     */
    private $earnRuleResource;

    /**
     * @var EarnRuleRepositoryInterface
     */
    private $earnRuleRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @param EarnRuleResource $earnRuleResource
     * @param EarnRuleRepositoryInterface $earnRuleRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        EarnRuleResource $earnRuleResource,
        EarnRuleRepositoryInterface $earnRuleRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->earnRuleResource = $earnRuleResource;
        $this->earnRuleRepository = $earnRuleRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * Get rules for apply
     *
     * @param int $productId
     * @param int $customerGroupId
     * @param int $websiteId
     * @param string $currentDate
     * @return EarnRuleInterface[]
     */
    public function getRulesForApply($productId, $customerGroupId, $websiteId, $currentDate)
    {
        $ruleIds = $this->earnRuleResource->getRuleIdsToApply($productId, $customerGroupId, $websiteId, $currentDate);

        $orderByPriority = $this->sortOrderBuilder
            ->setField(EarnRuleInterface::PRIORITY)
            ->setAscendingDirection()
            ->create();

        $orderById = $this->sortOrderBuilder
            ->setField(EarnRuleInterface::ID)
            ->setAscendingDirection()
            ->create();

        $this->searchCriteriaBuilder
            ->addFilter(EarnRuleInterface::ID, $ruleIds, 'in')
            ->setSortOrders([$orderByPriority, $orderById]);

        try {
            /** @var EarnRuleSearchResultsInterface $result */
            $result = $this->earnRuleRepository->getList($this->searchCriteriaBuilder->create());
            $rules = $result->getItems();
        } catch (LocalizedException $e) {
            $rules = [];
        }

        return $rules;
    }
}

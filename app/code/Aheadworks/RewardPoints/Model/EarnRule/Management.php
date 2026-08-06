<?php
namespace Aheadworks\RewardPoints\Model\EarnRule;

use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Api\Data\EarnRuleInterfaceFactory;
use Aheadworks\RewardPoints\Api\Data\EarnRuleSearchResultsInterface;
use Aheadworks\RewardPoints\Api\EarnRuleManagementInterface;
use Aheadworks\RewardPoints\Api\EarnRuleRepositoryInterface;
use Aheadworks\RewardPoints\Model\DateTime;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Management
 * @package Aheadworks\RewardPoints\Model\EarnRule
 */
class Management implements EarnRuleManagementInterface
{
    /**
     * @var EarnRuleRepositoryInterface
     */
    private $earnRuleRepository;

    /**
     * @var EarnRuleInterfaceFactory
     */
    private $earnRuleFactory;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param EarnRuleRepositoryInterface $earnRuleRepository
     * @param EarnRuleInterfaceFactory $earnRuleFactory
     * @param DateTime $dateTime
     * @param DataObjectHelper $dataObjectHelper
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        EarnRuleRepositoryInterface $earnRuleRepository,
        EarnRuleInterfaceFactory $earnRuleFactory,
        DateTime $dateTime,
        DataObjectHelper $dataObjectHelper,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->earnRuleRepository = $earnRuleRepository;
        $this->earnRuleFactory = $earnRuleFactory;
        $this->dateTime = $dateTime;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function enable($ruleId)
    {
        /** @var EarnRuleInterface $rule */
        $rule = $this->earnRuleRepository->get($ruleId);
        $rule->setStatus(EarnRuleInterface::STATUS_ENABLED);
        $rule = $this->earnRuleRepository->save($rule);

        return $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function disable($ruleId)
    {
        /** @var EarnRuleInterface $rule */
        $rule = $this->earnRuleRepository->get($ruleId);
        $rule->setStatus(EarnRuleInterface::STATUS_DISABLED);
        $rule = $this->earnRuleRepository->save($rule);

        return $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function createRule($ruleData)
    {
        /** @var EarnRuleInterface $rule */
        $rule = $this->earnRuleFactory->create();

        $this->dataObjectHelper->populateWithArray($rule, $ruleData, EarnRuleInterface::class);
        $rule = $this->earnRuleRepository->save($rule);

        return $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function updateRule($ruleId, $ruleData)
    {
        /** @var EarnRuleInterface $rule */
        $rule = $this->earnRuleRepository->get($ruleId);

        $this->dataObjectHelper->populateWithArray($rule, $ruleData, EarnRuleInterface::class);
        $rule = $this->earnRuleRepository->save($rule);

        return $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveRules()
    {
        $todayDate = $this->dateTime->getTodayDate();
        $this->searchCriteriaBuilder
            ->addFilter(EarnRuleInterface::STATUS, EarnRuleInterface::STATUS_ENABLED, 'eq')
            ->addFilter(EarnRuleInterface::TO_DATE, $todayDate);

        try {
            /** @var EarnRuleSearchResultsInterface $result */
            $result = $this->earnRuleRepository->getList($this->searchCriteriaBuilder->create());
            /** @var EarnRuleInterface[] $rules */
            $rules = $result ->getItems();
        } catch (LocalizedException $e) {
            $rules = [];
        }
        return $rules;
    }
}

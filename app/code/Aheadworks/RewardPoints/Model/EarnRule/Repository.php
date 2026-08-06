<?php
namespace Aheadworks\RewardPoints\Model\EarnRule;

use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Api\Data\EarnRuleInterfaceFactory;
use Aheadworks\RewardPoints\Api\Data\EarnRuleSearchResultsInterface;
use Aheadworks\RewardPoints\Api\Data\EarnRuleSearchResultsInterfaceFactory;
use Aheadworks\RewardPoints\Model\Indexer\EarnRule\Processor as EarnRuleIndexerProcessor;
use Aheadworks\RewardPoints\Model\Repository\CollectionProcessorInterface;
use Aheadworks\RewardPoints\Model\ResourceModel\EarnRule\Collection as EarnRuleCollection;
use Aheadworks\RewardPoints\Model\ResourceModel\EarnRule\CollectionFactory as EarnRuleCollectionFactory;
use Aheadworks\RewardPoints\Api\EarnRuleRepositoryInterface;
use Aheadworks\RewardPoints\Model\EarnRule as EarnRuleModel;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Aheadworks\RewardPoints\Model\StorefrontLabelsEntity\Store\Resolver as StorefrontLabelsEntityStoreResolver;

/**
 * Class Repository
 * @package Aheadworks\RewardPoints\Model\EarnRule
 */
class Repository implements EarnRuleRepositoryInterface
{
    /**
     * @var EarnRuleInterface[]
     */
    private $instances = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EarnRuleInterfaceFactory
     */
    private $earnRuleFactory;

    /**
     * @var EarnRuleSearchResultInterfaceFactory
     */
    private $earnRuleSearchResultsFactory;

    /**
     * @var EarnRuleCollectionFactory
     */
    private $earnRuleCollectionFactory;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var EarnRuleIndexerProcessor
     */
    private $indexerProcessor;

    /**
     * @var StorefrontLabelsEntityStoreResolver
     */
    protected $storefrontLabelsEntityStoreResolver;

    /**
     * @param EntityManager $entityManager
     * @param EarnRuleInterfaceFactory $earnRuleFactory
     * @param EarnRuleSearchResultsInterfaceFactory $earnRuleSearchResultsFactory
     * @param EarnRuleCollectionFactory $earnRuleCollectionFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     * @param EarnRuleIndexerProcessor $indexerProcessor
     * @param StorefrontLabelsEntityStoreResolver $storefrontLabelsEntityStoreResolver
     */
    public function __construct(
        EntityManager $entityManager,
        EarnRuleInterfaceFactory $earnRuleFactory,
        EarnRuleSearchResultsInterfaceFactory $earnRuleSearchResultsFactory,
        EarnRuleCollectionFactory $earnRuleCollectionFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        CollectionProcessorInterface $collectionProcessor,
        EarnRuleIndexerProcessor $indexerProcessor,
        StorefrontLabelsEntityStoreResolver $storefrontLabelsEntityStoreResolver
    ) {
        $this->entityManager = $entityManager;
        $this->earnRuleFactory = $earnRuleFactory;
        $this->earnRuleSearchResultsFactory = $earnRuleSearchResultsFactory;
        $this->earnRuleCollectionFactory = $earnRuleCollectionFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor;
        $this->indexerProcessor = $indexerProcessor;
        $this->storefrontLabelsEntityStoreResolver = $storefrontLabelsEntityStoreResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function save(EarnRuleInterface $rule)
    {
        try {
            $rule->validate();
            $this->entityManager->save($rule);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        $this->indexerProcessor->markIndexerAsInvalid();
        unset($this->instances[$rule->getId()]);
        return $this->get($rule->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function get($ruleId, $storeId = null)
    {
        if (!isset($this->instances[$ruleId])) {
            /** @var EarnRuleInterface $rule */
            $rule = $this->earnRuleFactory->create();
            $arguments = [
                'store_id' => $this->storefrontLabelsEntityStoreResolver->getStoreIdForCurrentLabels($storeId)
            ];
            $this->entityManager->load($rule, $ruleId, $arguments);
            if (!$rule->getId()) {
                throw NoSuchEntityException::singleField('id', $ruleId);
            }
            $this->instances[$ruleId] = $rule;
        }
        return $this->instances[$ruleId];
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria, $storeId = null)
    {
        try {
            /** @var EarnRuleCollection $collection */
            $collection = $this->earnRuleCollectionFactory->create();

            $this->extensionAttributesJoinProcessor->process($collection, EarnRuleInterface::class);
            $this->collectionProcessor->process($searchCriteria, $collection);

            $storeIdForCurrentLabels = $this->storefrontLabelsEntityStoreResolver->getStoreIdForCurrentLabels($storeId);
            $collection->setStoreId($storeIdForCurrentLabels);

            /** @var EarnRuleSearchResultsInterface $searchResults */
            $searchResults = $this->earnRuleSearchResultsFactory->create();
            $searchResults->setSearchCriteria($searchCriteria);
            $searchResults->setTotalCount($collection->getSize());

            $objects = [];
            /** @var EarnRuleModel $item */
            foreach ($collection->getItems() as $item) {
                $objects[] = $this->get($item->getId(), $storeIdForCurrentLabels);
            }
            $searchResults->setItems($objects);
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(EarnRuleInterface $rule)
    {
        return $this->deleteById($rule->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($ruleId)
    {
        /** @var EarnRuleInterface $rule */
        $rule = $this->earnRuleFactory->create();
        $this->entityManager->load($rule, $ruleId);
        if (!$rule->getId()) {
            throw NoSuchEntityException::singleField('id', $ruleId);
        }
        $this->entityManager->delete($rule);
        unset($this->instances[$ruleId]);
        return true;
    }
}

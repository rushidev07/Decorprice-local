<?php
namespace Aheadworks\RewardPoints\Model;

use Aheadworks\RewardPoints\Api\EarnRateRepositoryInterface;
use Aheadworks\RewardPoints\Api\Data\EarnRateInterface;
use Aheadworks\RewardPoints\Api\Data\EarnRateInterfaceFactory;
use Aheadworks\RewardPoints\Api\Data\EarnRateSearchResultsInterface;
use Aheadworks\RewardPoints\Api\Data\EarnRateSearchResultsInterfaceFactory;
use Aheadworks\RewardPoints\Model\EarnRate as EarnRateModel;
use Aheadworks\RewardPoints\Model\ResourceModel\EarnRate as EarnRateResource;
use Aheadworks\RewardPoints\Model\ResourceModel\EarnRate\Collection as EarnRateCollection;
use Aheadworks\RewardPoints\Model\ResourceModel\EarnRate\CollectionFactory as EarnRateCollectionFactory;
use Aheadworks\RewardPoints\Model\Repository\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Aheadworks\RewardPoints\Model\EarnRateRepository
 */
class EarnRateRepository implements EarnRateRepositoryInterface
{
    /**
     * @var EarnRateResource
     */
    private $resource;

    /**
     * @var EarnRateInterfaceFactory
     */
    private $earnRateFactory;

    /**
     * @var EarnRateSearchResultsInterfaceFactory
     */
    private $earnRateSearchResultsFactory;

    /**
     * @var EarnRateCollectionFactory
     */
    private $earnRateCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EarnRateInterface[]
     */
    private $instancesById = [];

    /**
     * @param EarnRateResource $resource
     * @param EarnRateInterfaceFactory $earnRateFactory
     * @param EarnRateSearchResultsInterfaceFactory $earnRateSearchResultsFactory
     * @param EarnRateCollectionFactory $earnRateCollectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param EntityManager $entityManager
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        EarnRateResource $resource,
        EarnRateInterfaceFactory $earnRateFactory,
        EarnRateSearchResultsInterfaceFactory $earnRateSearchResultsFactory,
        EarnRateCollectionFactory $earnRateCollectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        EntityManager $entityManager,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->earnRateFactory = $earnRateFactory;
        $this->earnRateSearchResultsFactory = $earnRateSearchResultsFactory;
        $this->earnRateCollectionFactory = $earnRateCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->entityManager = $entityManager;
        $this->storeManager = $storeManager;
    }

    /**
     *  {@inheritDoc}
     */
    public function get($customerGroupId, $lifetimeSalesAmount, $websiteId = null)
    {
        if (null == $websiteId) {
            $websiteId = $this->getWebsiteId();
        }
        $rateId = $this->resource->getRateRowId($customerGroupId, $lifetimeSalesAmount, $websiteId);
        return $this->getById($rateId);
    }

    /**
     * {@inheritDoc}
     */
    public function getById($id)
    {
        if (isset($this->instancesById[$id])) {
            return $this->instancesById[$id];
        }
        $earnRate = $this->earnRateFactory->create();
        $this->entityManager->load($earnRate, $id);
        $this->instancesById[$earnRate->getId()] = $earnRate;
        return $earnRate;
    }

    /**
     *  {@inheritDoc}
     */
    public function save(EarnRateInterface $earnRate)
    {
        $this->entityManager->save($earnRate);
        $this->instancesById[$earnRate->getId()] = $earnRate;
        return $earnRate;
    }

    /**
     *  {@inheritDoc}
     */
    public function delete(EarnRateInterface $earnRate)
    {
        unset($this->instancesById[$earnRate->getId()]);
        $this->entityManager->delete($earnRate);
        return true;
    }

    /**
     *  {@inheritDoc}
     */
    public function deleteById($id)
    {
        $earnRate = $this->getById($id);
        return $this->delete($earnRate);
    }

    /**
     *  {@inheritDoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var EarnRateCollection $collection */
        $collection = $this->earnRateCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var EarnRateSearchResultsInterface $searchResults */
        $searchResults = $this->earnRateSearchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setTotalCount($collection->getSize());

        $objects = [];
        /** @var EarnRateModel $item */
        foreach ($collection->getItems() as $item) {
            $objects[] = $this->getById($item->getId());
        }
        $searchResults->setItems($objects);

        return $searchResults;
    }

    /**
     * Retrieve website id
     *
     * @return int
     */
    private function getWebsiteId()
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }
}

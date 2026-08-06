<?php
namespace Aheadworks\RewardPoints\Model\ResourceModel\StorefrontLabelsEntity;

use Aheadworks\RewardPoints\Model\ResourceModel\AbstractCollection as BaseAbstractCollection;
use Aheadworks\RewardPoints\Model\StorefrontLabelsResolver;
use Aheadworks\RewardPoints\Api\Data\StorefrontLabelsInterface;
use Aheadworks\RewardPoints\Api\Data\StorefrontLabelsEntityInterface;
use Aheadworks\RewardPoints\Model\ResourceModel\StorefrontLabels\Repository as StorefrontLabelsRepository;
use Magento\Framework\Api\SortOrder;

/**
 * Class AbstractCollection
 *
 * @package Aheadworks\RewardPoints\Model\ResourceModel\StorefrontLabelsEntity
 * @codeCoverageIgnore
 */
abstract class AbstractCollection extends BaseAbstractCollection
{
    /**
     * @var int
     */
    protected $storeId;

    /**
     * @var StorefrontLabelsResolver
     */
    protected $storefrontLabelsResolver;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param StorefrontLabelsResolver $storefrontLabelsResolver
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        StorefrontLabelsResolver $storefrontLabelsResolver,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->storefrontLabelsResolver = $storefrontLabelsResolver;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Set store id for entity labels retrieving
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * Get store id for entity labels retrieving
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        $this->attachLabels();
        $this->addCurrentLabels();
        return parent::_afterLoad();
    }

    /**
     * Attach labels on storefront per store view
     *
     * @return void
     */
    protected function attachLabels()
    {
        $this->attachRelationTable(
            StorefrontLabelsRepository::MAIN_TABLE_NAME,
            $this->getIdFieldName(),
            'entity_id',
            [
                StorefrontLabelsInterface::PRODUCT_PROMO_TEXT,
                StorefrontLabelsInterface::CATEGORY_PROMO_TEXT,
                StorefrontLabelsInterface::STORE_ID
            ],
            StorefrontLabelsEntityInterface::LABELS,
            [
                [
                    'field' => 'entity_type',
                    'condition' => '=',
                    'value' => $this->getStorefrontLabelsEntityType()
                ]
            ],
            [
                'field' => StorefrontLabelsInterface::STORE_ID,
                'direction' => SortOrder::SORT_ASC
            ],
            true
        );
    }

    /**
     * Retrieve type of entity with storefront labels
     *
     * @return string
     */
    abstract protected function getStorefrontLabelsEntityType();

    /**
     * Add labels on storefront for specific store view
     *
     * @return $this
     */
    protected function addCurrentLabels()
    {
        $currentStoreId = $this->getStoreId();
        if (isset($currentStoreId)) {
            foreach ($this as $item) {
                $labelsData = $item->getData(StorefrontLabelsEntityInterface::LABELS);
                if (is_array($labelsData)) {
                    $currentLabelsRecord = $this->storefrontLabelsResolver
                        ->getLabelsForStoreAsArray($labelsData, $currentStoreId);
                    $item->setData(StorefrontLabelsEntityInterface::CURRENT_LABELS, $currentLabelsRecord);
                }
            }
        }
        return $this;
    }
}

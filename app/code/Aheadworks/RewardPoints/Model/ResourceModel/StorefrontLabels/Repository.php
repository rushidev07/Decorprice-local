<?php
namespace Aheadworks\RewardPoints\Model\ResourceModel\StorefrontLabels;

use Aheadworks\RewardPoints\Api\Data\StorefrontLabelsEntityInterface;
use Aheadworks\RewardPoints\Api\Data\StorefrontLabelsInterface;
use Aheadworks\RewardPoints\Api\Data\StorefrontLabelsInterfaceFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class Repository
 *
 * @package Aheadworks\RewardPoints\Model\ResourceModel\StorefrontLabels
 * @codeCoverageIgnore
 */
class Repository
{
    /**#@+
     * Constants defined for storefront label table
     */
    const MAIN_TABLE_NAME = 'aw_rp_label';
    /**#@-*/

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var StorefrontLabelsInterfaceFactory
     */
    private $storefrontLabelsFactory;

    /**
     * @param ResourceConnection $resourceConnection
     * @param MetadataPool $metadataPool
     * @param DataObjectHelper $dataObjectHelper
     * @param StorefrontLabelsInterfaceFactory $storefrontLabelsFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool,
        DataObjectHelper $dataObjectHelper,
        StorefrontLabelsInterfaceFactory $storefrontLabelsFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->storefrontLabelsFactory = $storefrontLabelsFactory;
    }

    /**
     * Save storefront labels
     *
     * @param StorefrontLabelsEntityInterface $entity
     * @return bool
     * @throws \Exception
     */
    public function save($entity)
    {
        if (!(int)$entity->getEntityId()) {
            return false;
        }

        $this->deleteByEntity($entity);
        $labelsData = $this->extractLabelsDataFromEntity($entity);
        $this->insertLabelsData($labelsData);

        return true;
    }

    /**
     * Retrieve labels data for specified entity
     *
     * @param StorefrontLabelsEntityInterface $entity
     * @return StorefrontLabelsInterface[]
     * @throws \Exception
     */
    public function get($entity)
    {
        $labelsData = $this->getLabelsDataForEntity(
            $entity->getEntityId(),
            $entity->getStorefrontLabelsEntityType()
        );
        $labelsObjects = $this->getLabelObjects($labelsData);
        return $labelsObjects;
    }

    /**
     * Delete all existed labels data for specified entity id and type
     *
     * @param int $id
     * @param string $storefrontLabelEntityType
     * @return bool
     * @throws \Exception
     */
    public function delete($id, $storefrontLabelEntityType)
    {
        $this->getConnection()->delete(
            $this->getTableName(),
            [
                'entity_id = ?' => $id,
                'entity_type = ?' => $storefrontLabelEntityType
            ]
        );
        return true;
    }

    /**
     * Delete all existed labels data for specified entity
     *
     * @param StorefrontLabelsEntityInterface $entity
     * @return bool
     * @throws \Exception
     */
    public function deleteByEntity($entity)
    {
        return $this->delete($entity->getEntityId(), $entity->getStorefrontLabelsEntityType());
    }

    /**
     * Retrieve labels data for specified entity
     *
     * @param int $entityId
     * @param string $entityType
     * @return array
     * @throws \Exception
     */
    private function getLabelsDataForEntity($entityId, $entityType)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName(self::MAIN_TABLE_NAME))
            ->where('entity_id = :entity_id')
            ->where('entity_type = :entity_type')
            ->order('store_id ' . SortOrder::SORT_ASC);
        $labelsData = $connection->fetchAll(
            $select,
            [
                'entity_id' => $entityId,
                'entity_type' => $entityType
            ]
        );
        return $labelsData;
    }

    /**
     * Retrieve storefront labels from data array
     *
     * @param array $labelsData
     * @return StorefrontLabelsInterface[]
     */
    protected function getLabelObjects($labelsData)
    {
        $labels = [];
        foreach ($labelsData as $labelsDataRow) {
            /** @var StorefrontLabelsInterface $labelsRecord */
            $labelsObject = $this->storefrontLabelsFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $labelsObject,
                $labelsDataRow,
                StorefrontLabelsInterface::class
            );
            $labels[] = $labelsObject;
        }
        return $labels;
    }

    /**
     * Extract from entity array of current labels data to insert
     *
     * @param StorefrontLabelsEntityInterface $entity
     * @return array
     */
    private function extractLabelsDataFromEntity($entity)
    {
        $currentLabelsData = [];
        /** @var StorefrontLabelsInterface $labelsRecord */
        foreach ($entity->getLabels() as $labelsRecord) {
            $currentLabelsData[] = [
                'entity_id'             => (int)$entity->getEntityId(),
                'entity_type'           => $entity->getStorefrontLabelsEntityType(),
                'store_id'              => $labelsRecord->getStoreId(),
                'product_promo_text'    => $labelsRecord->getProductPromoText(),
                'category_promo_text'   => $labelsRecord->getCategoryPromoText(),
            ];
        }
        return $currentLabelsData;
    }

    /**
     * Insert labels data
     *
     * @param array $labelsRecordsToInsert
     * @return $this
     * @throws \Exception
     */
    private function insertLabelsData($labelsRecordsToInsert)
    {
        if (!empty($labelsRecordsToInsert)) {
            $this->getConnection()->insertMultiple($this->getTableName(), $labelsRecordsToInsert);
        }
        return $this;
    }

    /**
     * Retrieve table name
     *
     * @return string
     * @throws \Exception
     */
    private function getTableName()
    {
        return $this->metadataPool->getMetadata(StorefrontLabelsInterface::class)->getEntityTable();
    }

    /**
     * Get connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @throws \Exception
     */
    private function getConnection()
    {
        return $this->resourceConnection->getConnectionByName(
            $this->metadataPool->getMetadata(StorefrontLabelsInterface::class)->getEntityConnectionName()
        );
    }
}

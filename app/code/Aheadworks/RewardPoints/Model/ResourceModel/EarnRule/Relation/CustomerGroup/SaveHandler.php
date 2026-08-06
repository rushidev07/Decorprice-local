<?php
namespace Aheadworks\RewardPoints\Model\ResourceModel\EarnRule\Relation\CustomerGroup;

use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Model\ResourceModel\EarnRule as EarnRuleResource;
use Aheadworks\RewardPoints\Model\EarnRule;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class SaveHandler
 * @package Aheadworks\RewardPoints\Model\ResourceModel\EarnRule\Relation\CustomerGroup
 * @codeCoverageIgnore
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param Logger $logger
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        Logger $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $this->deleteOldCustomerGroupData($entity->getId());
        /** @var EarnRule $entity */
        $customerGroupDataToSave = $this->getCustomerGroupDataToSave($entity);
        $this->saveCustomerGroupData($customerGroupDataToSave);
        return $entity;
    }

    /**
     * Remove old customer group data
     *
     * @param int $id
     * @return int
     * @throws \Exception
     */
    private function deleteOldCustomerGroupData($id)
    {
        return $this->getConnection()->delete($this->getTableName(), ['rule_id = ?' => $id]);
    }

    /**
     * Retrieve customer group data to save in the corresponding table
     *
     * @param EarnRule $entity
     * @return array
     */
    private function getCustomerGroupDataToSave($entity)
    {
        $customerGroupData = [];
        $ruleId = $entity->getId();

        foreach ($entity->getCustomerGroupIds() as $customerGroupId) {
            $customerGroupData[] = [
                'rule_id' => $ruleId,
                'customer_group_id' => $customerGroupId
            ];
        }
        return $customerGroupData;
    }

    /**
     * Save customer group data in the corresponding table
     *
     * @param array $customerGroupDataToSave
     * @return $this
     */
    private function saveCustomerGroupData($customerGroupDataToSave)
    {
        if (!empty($customerGroupDataToSave)) {
            try {
                $connection = $this->getConnection();
                $tableName = $this->getTableName();
                $connection->insertMultiple(
                    $tableName,
                    $customerGroupDataToSave
                );
            } catch (\Exception $exception) {
                $this->logger->critical($exception->getMessage());
            }
        }
        return $this;
    }

    /**
     * Get connection
     *
     * @return AdapterInterface
     * @throws \Exception
     */
    private function getConnection()
    {
        return $this->resourceConnection->getConnectionByName(
            $this->metadataPool->getMetadata(EarnRuleInterface::class)->getEntityConnectionName()
        );
    }

    /**
     * Get table name
     *
     * @return string
     */
    private function getTableName()
    {
        return $this->resourceConnection->getTableName(EarnRuleResource::CUSTOMER_GROUP_TABLE_NAME);
    }
}

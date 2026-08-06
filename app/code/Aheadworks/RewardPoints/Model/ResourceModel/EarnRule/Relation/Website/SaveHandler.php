<?php
namespace Aheadworks\RewardPoints\Model\ResourceModel\EarnRule\Relation\Website;

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
 * @package Aheadworks\RewardPoints\Model\ResourceModel\EarnRule\Relation\Website
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
        $this->deleteOldWebsiteData($entity->getId());
        /** @var EarnRule $entity */
        $websiteDataToSave = $this->getWebsiteDataToSave($entity);
        $this->saveWebsiteData($websiteDataToSave);
        return $entity;
    }

    /**
     * Remove old website data
     *
     * @param int $id
     * @return int
     * @throws \Exception
     */
    private function deleteOldWebsiteData($id)
    {
        return $this->getConnection()->delete($this->getTableName(), ['rule_id = ?' => $id]);
    }

    /**
     * Retrieve website data to save in the corresponding table
     *
     * @param EarnRule $entity
     * @return array
     */
    private function getWebsiteDataToSave($entity)
    {
        $websiteData = [];
        $ruleId = $entity->getId();

        foreach ($entity->getWebsiteIds() as $websiteId) {
            $websiteData[] = [
                'rule_id' => $ruleId,
                'website_id' => $websiteId
            ];
        }
        return $websiteData;
    }

    /**
     * Save website data in the corresponding table
     *
     * @param array $websiteDataToSave
     * @return $this
     */
    private function saveWebsiteData($websiteDataToSave)
    {
        if (!empty($websiteDataToSave)) {
            try {
                $connection = $this->getConnection();
                $tableName = $this->getTableName();
                $connection->insertMultiple(
                    $tableName,
                    $websiteDataToSave
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
        return $this->resourceConnection->getTableName(EarnRuleResource::WEBSITE_TABLE_NAME);
    }
}

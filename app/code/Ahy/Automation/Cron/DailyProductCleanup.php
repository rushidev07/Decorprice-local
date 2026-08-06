<?php
declare(strict_types=1);

namespace Ahy\Automation\Cron;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Ahy\Automation\Observer\ProductDataCleanupObserver;
use Ahy\Automation\Logger\Logger;
use Magento\Framework\Stdlib\DateTime\DateTime;

class DailyProductCleanup
{
    private CollectionFactory $collectionFactory;
    private ProductDataCleanupObserver $cleanupObserver;
    private Logger $logger;
    private DateTime $dateTime;

    public function __construct(
        CollectionFactory $collectionFactory,
        ProductDataCleanupObserver $cleanupObserver,
        Logger $logger,
        DateTime $dateTime
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->cleanupObserver   = $cleanupObserver;
        $this->logger            = $logger;
        $this->dateTime          = $dateTime;
    }

    public function execute(): void
    {
        $today = $this->dateTime->date('Y-m-d');

        $this->logger->info('Hourly product data cleanup cron started', [
            'date' => $today
        ]);

        $collection = $this->collectionFactory->create();
        $connection = $collection->getConnection();

        // Get ETA attribute ID
        $etaAttr = $collection->getResource()->getAttribute('eta');
        $etaAttrId = $etaAttr ? (int)$etaAttr->getAttributeId() : 0;

        // Get adwords_grouping attribute ID
        $adwordsAttr = $collection->getResource()->getAttribute('adwords_grouping');
        $adwordsAttrId = $adwordsAttr ? (int)$adwordsAttr->getAttributeId() : 0;

        // Step 1: Fetch entity_ids via raw SQL with all three conditions
        $sql = "
            SELECT DISTINCT cpe.entity_id
            FROM {$collection->getTable('catalog_product_entity')} AS cpe
            LEFT JOIN {$collection->getTable('catalog_product_entity_datetime')} AS eta
                   ON eta.entity_id = cpe.entity_id
                  AND eta.attribute_id = :eta_attr
                  AND eta.store_id = 0
            LEFT JOIN {$collection->getTable('cataloginventory_stock_item')} AS stock
                   ON stock.product_id = cpe.entity_id
                  AND stock.stock_id = 1
            LEFT JOIN {$collection->getTable('catalog_product_entity_int')} AS cpei
                   ON cpe.entity_id = cpei.entity_id
                  AND cpei.attribute_id = :adwords_attr
                  AND cpei.store_id = 0
            LEFT JOIN {$collection->getTable('eav_attribute_option_value')} AS eaov
                   ON cpei.value = eaov.option_id
            WHERE (eta.value IS NOT NULL AND eta.value <= :today)
               OR (stock.qty IS NOT NULL AND stock.qty < 0)
               OR (stock.qty = 0 AND eaov.value = 'instock')
        ";

        $entityIds = $connection->fetchCol($sql, [
            'eta_attr'      => $etaAttrId,
            'adwords_attr'  => $adwordsAttrId,
            'today'         => $today
        ]);

        if (empty($entityIds)) {
            $this->logger->info('No products matched the cleanup criteria.');
            return;
        }

        // Step 2: Load products by entity_id IN (...) using ORM
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect(['sku', 'eta', 'adwords_grouping']);
        $collection->addFieldToFilter('entity_id', ['in' => $entityIds]);

        $collection->setPageSize(500);
        $page = 1;

        do {
            $collection->setCurPage($page);
            $collection->load();

            foreach ($collection as $product) {
                $sku = $product->getSku();

                try {
                    $this->logger->info('Cron cleanup processing SKU', [
                        'sku' => $sku
                    ]);

                    $this->cleanupObserver->processProduct(
                        $sku,
                        $product,
                        'hourly_cron'
                    );
                } catch (\Throwable $e) {
                    $this->logger->error('Error during cron cleanup', [
                        'sku'       => $sku,
                        'exception' => $e->getMessage()
                    ]);
                }
            }

            $collection->clear();
            $page++;
        } while ($page <= $collection->getLastPageNumber());

        $this->logger->info('Hourly product data cleanup cron completed');
    }
}

<?php
declare(strict_types=1);

namespace Ahy\Automation\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Ahy\Automation\Logger\Logger as ProductCleanupLogger;
use Magento\Catalog\Api\ProductRepositoryInterface;

class AfterRapidflowProductImportSave implements ObserverInterface
{
    private ProductCleanupLogger $logger;
    private ProductDataCleanupObserver $cleanupObserver;

    public function __construct(
        ProductCleanupLogger $logger,
        ProductDataCleanupObserver $cleanupObserver
    ) {
        $this->logger = $logger;
        $this->cleanupObserver = $cleanupObserver;
    }

    public function execute(Observer $observer): void
    {
        $vars = $observer->getEvent()->getVars();

        /**
         * RapidFlow gives SKUs as keys
         */
        $skus = array_unique(array_merge(
            array_keys($vars['change_attr'] ?? []),
            array_keys($vars['change_stock'] ?? []),
            array_keys($vars['insert_entity'] ?? [])
        ));

        if (empty($skus)) {
            return;
        }

        $this->logger->info('[RapidFlow] Triggering cleanup via existing logic', [
            'sku_count' => count($skus)
        ]);

        foreach ($skus as $sku) {
            try {
                /**
                 * Reuse existing observer logic
                 * SKU-based, already tested, already safe
                 */
                $this->cleanupObserver->execute(
                    new Observer(['product' => ['sku' => $sku]])
                );
            } catch (\Throwable $e) {
                $this->logger->error('[RapidFlow] Cleanup failed for SKU', [
                    'sku' => $sku,
                    'exception' => $e->getMessage()
                ]);
            }
        }
    }
}

<?php
declare(strict_types=1);

namespace Ahy\Automation\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Ahy\Automation\Logger\Logger as ProductCleanupLogger;
use Magento\Catalog\Model\ResourceModel\Product\Action as ProductAction;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;

class ProductDataCleanupObserver implements ObserverInterface
{
    private StockRegistryInterface $stockRegistry;
    private ProductAction $productAction;
    private ProductCleanupLogger $logger;
    private ProductRepositoryInterface $productRepository;

    public function __construct(
        StockRegistryInterface $stockRegistry,
        ProductAction $productAction,
        ProductCleanupLogger $logger,
        ProductRepositoryInterface $productRepository
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->productAction = $productAction;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
    }

    public function execute(Observer $observer): void
    {
        $products = [];
        $trigger = 'unknown';

        /**
         * 1. Single product save
         */
        if ($observer->hasData('product')) {
            $products[] = $observer->getData('product');
            $trigger = 'catalog_product_save_after';
        }

        /**
         * 2. Bulk import
         */
        if ($observer->hasData('bunch')) {
            foreach ($observer->getData('bunch') as $row) {
                if (!empty($row['sku'])) {
                    $products[] = ['sku' => $row['sku']];
                }
            }
            $trigger = 'catalog_product_import_bunch_save_after';
        }

        /**
         * 3. Stock item save (IMPORTANT FIX)
         */
        if ($observer->hasData('item')) {
            $stockItem = $observer->getData('item');
            $productId = (int)$stockItem->getProductId();

            if ($productId) {
                try {
                    $products[] = $this->productRepository->getById($productId);
                } catch (\Throwable $e) {
                    $this->logger->error('Failed to load product from stock item', [
                        'product_id' => $productId,
                        'exception' => $e->getMessage()
                    ]);
                }
            }

            $trigger = 'cataloginventory_stock_item_save_after';
        }

        if (empty($products)) {
            return;
        }

        // $this->logger->info('Product cleanup observer triggered', [
        //     'trigger' => $trigger,
        //     'product_count' => count($products)
        // ]);

        foreach ($products as $productData) {
            try {
                $sku = $this->resolveSku($productData);

                if (!$sku) {
                    $this->logger->warning('SKU could not be resolved, skipping cleanup', [
                        'trigger' => $trigger,
                        'data_type' => is_object($productData)
                            ? get_class($productData)
                            : gettype($productData)
                    ]);
                    continue;
                }

                $this->processProduct(
                    $sku,
                    $productData instanceof Product ? $productData : null,
                    $trigger
                );
            } catch (\Throwable $e) {
                $this->logger->error('Error during product cleanup', [
                    'sku' => $sku ?? 'unknown',
                    'trigger' => $trigger,
                    'exception' => $e->getMessage()
                ]);
            }
        }
    }

    public function processProduct(string $sku, ?Product $product = null, string $trigger = ''): void
    {
        // $this->logger->debug('Processing SKU for cleanup', [
        //     'sku' => $sku,
        //     'trigger' => $trigger
        // ]);

        /**
         * 1. Stock cleanup
         */
        $stockItem = $this->stockRegistry->getStockItemBySku($sku);
        $qty = (float)$stockItem->getQty();

        if ($qty < 0) {
            $this->logger->warning('Negative stock detected', [
                'sku' => $sku,
                'old_qty' => $qty
            ]);

            $stockItem->setQty(0);
            $this->stockRegistry->updateStockItemBySku($sku, $stockItem);

            $this->logger->info('Stock quantity normalized', [
                'sku' => $sku,
                'new_qty' => 0
            ]);
        }

        /**
         * 2. Load product if needed
         */
        if ($product === null) {
            $product = $this->productRepository->get($sku);
        }

        $updates = [];

        /**
         * 3. ETA cleanup
         */
        $eta = $product->getData('eta');
        if ($eta && strtotime($eta) <= strtotime('today')) {
            $updates['eta'] = null;

            $this->logger->info('ETA removed', [
                'sku' => $sku,
                'old_eta' => $eta
            ]);
        }

        /**
         * 4. Adwords grouping cleanup
         */
        if ((float)$stockItem->getQty() <= 0 && $product->getData('adwords_grouping')) {
            $updates['adwords_grouping'] = '';

            $this->logger->info('Adwords grouping cleared', [
                'sku' => $sku,
                'qty' => $stockItem->getQty()
            ]);
        }

        /**
         * 5. Apply updates
         */
        if (!empty($updates)) {
            $this->productAction->updateAttributes(
                [$product->getId()],
                $updates,
                0
            );

            $this->logger->debug('Product attributes updated', [
                'sku' => $sku,
                'updates' => array_keys($updates)
            ]);
        }
    }

    /**
     * Safely resolve SKU from different observer payloads
     */
    private function resolveSku($productData): ?string
    {
        if ($productData instanceof Product) {
            return $productData->getSku();
        }

        if (is_array($productData) && !empty($productData['sku'])) {
            return (string)$productData['sku'];
        }

        return null;
    }
}

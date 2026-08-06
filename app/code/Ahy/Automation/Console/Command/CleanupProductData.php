<?php
declare(strict_types=1);

namespace Ahy\Automation\Console\Command;

use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\Framework\Console\Cli;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Action as ProductAction;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ahy\Automation\Logger\Logger as ProductCleanupLogger;

class CleanupProductData extends Command
{
    private const OPTION_DRY_RUN = 'dry-run';
    private const OPTION_SKU = 'sku';
    private const PAGE_SIZE = 500;

    private State $appState;
    private CollectionFactory $productCollectionFactory;
    private StockRegistryInterface $stockRegistry;
    private ProductAction $productAction;
    private ProductCleanupLogger $logger;

    public function __construct(
        State $appState,
        CollectionFactory $productCollectionFactory,
        StockRegistryInterface $stockRegistry,
        ProductCleanupLogger $logger,
        ProductAction $productAction
    ) {
        $this->appState = $appState;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->stockRegistry = $stockRegistry;
        $this->productAction = $productAction;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('ahy:automation:cleanup')
            ->setDescription('Cleanup stock, ETA and adwords grouping data')
            ->addOption(
                self::OPTION_DRY_RUN,
                null,
                InputOption::VALUE_NONE,
                'Run without saving changes'
            )
            ->addOption(
                self::OPTION_SKU,
                null,
                InputOption::VALUE_OPTIONAL,
                'Cleanup specific SKU(s), comma-separated'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->appState->setAreaCode(Area::AREA_ADMINHTML);
        } catch (LocalizedException $e) {
            // Area code already set
        }

        $dryRun = (bool)$input->getOption(self::OPTION_DRY_RUN);
        $skuFilter = $input->getOption(self::OPTION_SKU);

        $this->logger->info('Product data cleanup command started', [
            'dry_run' => $dryRun,
            'sku_filter' => $skuFilter ?: 'all'
        ]);

        $output->writeln('<info>Starting product data cleanup</info>');
        if ($dryRun) {
            $output->writeln('<comment>Running in DRY-RUN mode (no DB writes)</comment>');
        }

        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect(['sku', 'eta', 'adwords_grouping']);

        if ($skuFilter) {
            $skus = array_map('trim', explode(',', $skuFilter));
            $collection->addAttributeToFilter('sku', ['in' => $skus]);
        }

        $currentPage = 1;
        $collection->setPageSize(self::PAGE_SIZE);

        $stats = [
            'products_scanned' => 0,
            'stock_fixed' => 0,
            'eta_fixed' => 0,
            'adwords_fixed' => 0,
        ];

        do {
            $collection->setCurPage($currentPage);
            $collection->load();

            foreach ($collection as $product) {
                $stats['products_scanned']++;

                $sku = $product->getSku();

                try {
                    $this->processStock($sku, $dryRun, $stats, $output);
                    $this->processProductAttributes($product, $dryRun, $stats, $output);
                } catch (\Throwable $e) {
                    $output->writeln(
                        sprintf('<error>Error processing SKU %s: %s</error>', $sku, $e->getMessage())
                    );

                    $this->logger->error('Error processing product during cleanup command', [
                        'sku' => $sku,
                        'exception' => $e
                    ]);
                }
            }

            $collection->clear();
            $currentPage++;
        } while ($currentPage <= $collection->getLastPageNumber());

        $output->writeln('<info>Cleanup completed</info>');
        $output->writeln(print_r($stats, true));

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Normalize stock quantities (legacy single-source Magento)
     */
    private function processStock(
        string $sku,
        bool $dryRun,
        array &$stats,
        OutputInterface $output
    ): void {
        try {
            $stockItem = $this->stockRegistry->getStockItemBySku($sku);
            $qty = (float)$stockItem->getQty();

            if ($qty < 0) {
                $stats['stock_fixed']++;

                $this->logger->warning('Negative stock detected during CLI cleanup', [
                    'sku' => $sku,
                    'old_qty' => $qty,
                    'dry_run' => $dryRun
                ]);

                $output->writeln(
                    sprintf('SKU %s: stock qty %s → 0', $sku, $qty)
                );

                if (!$dryRun) {
                    $stockItem->setQty(0);
                    $this->stockRegistry->updateStockItemBySku($sku, $stockItem);

                    $this->logger->info('Stock quantity normalized via CLI', [
                        'sku' => $sku,
                        'new_qty' => 0
                    ]);
                }
            }
        } catch (\Throwable $e) {
            $this->logger->error('Failed to process stock during CLI cleanup', [
                'sku' => $sku,
                'exception' => $e
            ]);

            $output->writeln(sprintf('<error>Failed to process stock for SKU %s: %s</error>', $sku, $e->getMessage()));
        }
    }

    /**
     * Cleanup ETA and Adwords grouping
     */
    private function processProductAttributes(
        $product,
        bool $dryRun,
        array &$stats,
        OutputInterface $output
    ): void {
        $productId = (int)$product->getId();
        $sku = $product->getSku();

        $updates = [];

        /** ETA cleanup */
        $eta = $product->getData('eta');
        if ($eta && strtotime($eta) <= strtotime('today')) {
            $updates['eta'] = null;
            $stats['eta_fixed']++;

            $this->logger->info('ETA removed via CLI cleanup', [
                'sku' => $sku,
                'old_eta' => $eta,
                'dry_run' => $dryRun
            ]);

            $output->writeln("SKU {$sku}: ETA removed");
        }

        /** Adwords grouping cleanup */
        $stockItem = $this->stockRegistry->getStockItemBySku($sku);
        $qty = (float)$stockItem->getQty();

        if ($qty <= 0 && $product->getData('adwords_grouping')) {
            $updates['adwords_grouping'] = '';
            $stats['adwords_fixed']++;

            $this->logger->info('Adwords grouping cleared via CLI cleanup', [
                'sku' => $sku,
                'qty' => $qty,
                'dry_run' => $dryRun
            ]);

            $output->writeln("SKU {$sku}: adwords_grouping cleared");
        }

        if (!$dryRun && !empty($updates)) {
            $this->productAction->updateAttributes(
                [$productId],
                $updates,
                0
            );

            $this->logger->debug('Product attributes updated via CLI', [
                'sku' => $sku,
                'attributes' => array_keys($updates)
            ]);
        }
    }
}

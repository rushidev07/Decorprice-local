<?php
declare(strict_types=1);

namespace Ahy\ShadowDomBenchmark\Model;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Single shared data source for all three benchmark variants (CSR / SSR-plain / SSR+DSD),
 * so the only thing that differs between them is WHERE the data becomes HTML, not what the data is.
 *
 * DecorPrice's real catalog is ~100,000 products across 9M+ row EAV tables — a fresh
 * collection query against it takes several seconds, which would swamp the much smaller
 * render-technique difference this benchmark exists to isolate. So the result is cached
 * to a file after the first (real) fetch, and reused on subsequent calls, the same way a
 * real search platform would serve from its own index rather than re-querying Magento's
 * catalog on every request.
 */
class ProductDataProvider
{
    private const CACHE_FILE = 'shadowdom_benchmark_products.json';
    private const CACHE_TTL_SECONDS = 3600;

    public function __construct(
        private readonly CollectionFactory $collectionFactory,
        private readonly StoreManagerInterface $storeManager,
        private readonly Filesystem $filesystem
    ) {
    }

    /**
     * @return array<int, array{id:int, name:string, price:float, image:string, url:string}>
     */
    public function getProducts(int $limit = 24): array
    {
        $cached = $this->readCache();
        if ($cached !== null) {
            return $cached;
        }

        $products = $this->fetchFromCatalog($limit);
        $this->writeCache($products);
        return $products;
    }

    private function cachePath(): string
    {
        return $this->filesystem
            ->getDirectoryWrite(DirectoryList::VAR_DIR)
            ->getAbsolutePath(self::CACHE_FILE);
    }

    private function readCache(): ?array
    {
        $path = $this->cachePath();
        if (!file_exists($path) || (time() - filemtime($path)) > self::CACHE_TTL_SECONDS) {
            return null;
        }
        $raw = @file_get_contents($path);
        $data = $raw ? json_decode($raw, true) : null;
        return is_array($data) ? $data : null;
    }

    private function writeCache(array $products): void
    {
        @file_put_contents($this->cachePath(), json_encode($products), LOCK_EX);
    }

    private function fetchFromCatalog(int $limit): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect(['name', 'price', 'small_image', 'url_key']);
        $collection->addAttributeToFilter('status', Status::STATUS_ENABLED);
        $collection->addAttributeToFilter('visibility', ['neq' => Visibility::VISIBILITY_NOT_VISIBLE]);
        $collection->setPageSize($limit);
        $collection->setCurPage(1);

        $mediaBase = '';
        try {
            $mediaBase = rtrim(
                $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA),
                '/'
            ) . '/catalog/product';
        } catch (\Throwable $e) {
            $mediaBase = '/media/catalog/product';
        }

        $products = [];
        foreach ($collection as $product) {
            $image = (string) $product->getData('small_image');
            $imageUrl = $image && $image !== 'no_selection'
                ? $mediaBase . (str_starts_with($image, '/') ? $image : '/' . $image)
                : '';

            $products[] = [
                'id' => (int) $product->getId(),
                'name' => (string) $product->getName(),
                'price' => (float) $product->getPrice(),
                'image' => $imageUrl,
                'url' => (string) $product->getProductUrl(),
            ];
        }

        return $products;
    }
}

<?php
declare(strict_types=1);

namespace Ahy\ShadowDomBenchmark\Controller\Index;

use Ahy\ShadowDomBenchmark\Model\ProductDataProvider;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * JSON endpoint the CSR variant's JS calls — stands in for FalcoSense's real
 * suggest/products API in this benchmark. Same data as the two SSR variants.
 */
class Data implements HttpGetActionInterface
{
    public function __construct(
        private readonly JsonFactory $jsonFactory,
        private readonly ProductDataProvider $dataProvider
    ) {
    }

    public function execute(): ResultInterface
    {
        $start = microtime(true);
        $products = $this->dataProvider->getProducts();
        $serverMs = round((microtime(true) - $start) * 1000, 2);

        $result = $this->jsonFactory->create();
        $result->setData([
            'products' => $products,
            'server_fetch_ms' => $serverMs,
        ]);
        return $result;
    }
}

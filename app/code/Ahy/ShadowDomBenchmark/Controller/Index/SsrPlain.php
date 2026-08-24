<?php
declare(strict_types=1);

namespace Ahy\ShadowDomBenchmark\Controller\Index;

use Ahy\ShadowDomBenchmark\Model\CardHtmlRenderer;
use Ahy\ShadowDomBenchmark\Model\ProductDataProvider;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Variant 2 — SSR-plain: the server builds the real product-card HTML directly
 * during this request. No shadow DOM involved. The "classic" SSR-shell idea.
 */
class SsrPlain implements HttpGetActionInterface
{
    public function __construct(
        private readonly RawFactory $rawFactory,
        private readonly ProductDataProvider $dataProvider,
        private readonly CardHtmlRenderer $cardRenderer
    ) {
    }

    public function execute(): ResultInterface
    {
        $start = microtime(true);
        $products = $this->dataProvider->getProducts();
        $cardsHtml = $this->cardRenderer->render($products);
        $serverMs = round((microtime(true) - $start) * 1000, 2);
        $css = $this->cardRenderer->sharedCss();

        $result = $this->rawFactory->create();
        $result->setHeader('Content-Type', 'text/html; charset=UTF-8');
        $result->setContents(<<<HTML
<!doctype html>
<html><head><meta charset="utf-8"><title>Variant 2: SSR-plain</title>
<style>body{font-family:system-ui,sans-serif;max-width:1000px;margin:20px auto;padding:0 16px}
.banner{position:sticky;top:0;background:#0a5;color:#fff;padding:10px 14px;border-radius:8px;margin-bottom:16px;font-family:monospace;font-size:13px}
{$css}
</style></head>
<body>
<div class="banner">SSR-plain — server built this HTML in {$serverMs}ms (view-source to confirm real markup, no JS needed)</div>
<h1>Variant 2: SSR-plain</h1>
<p>The cards below were already in the HTML the moment this response left the server.</p>
<div class="grid">{$cardsHtml}</div>
</body></html>
HTML
        );
        return $result;
    }
}

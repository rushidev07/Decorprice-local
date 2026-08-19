<?php
declare(strict_types=1);

namespace Ahy\ShadowDomBenchmark\Controller\Index;

use Ahy\ShadowDomBenchmark\Model\CardHtmlRenderer;
use Ahy\ShadowDomBenchmark\Model\ProductDataProvider;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;

/**
 * Variant 3 — SSR + Declarative Shadow DOM: the server outputs a
 * <template shadowrootmode="open"> already containing the real cards.
 * The browser's OWN HTML parser attaches the shadow root — no JS required
 * for the content to exist. A small JS file then ENHANCES that same shadow
 * root in place (never rebuilds it), which is the fix for the "two renderers
 * drift apart" risk: there is only ever one card markup, written once.
 */
class SsrDsd implements HttpGetActionInterface
{
    public function __construct(
        private readonly RawFactory $rawFactory,
        private readonly ProductDataProvider $dataProvider,
        private readonly CardHtmlRenderer $cardRenderer,
        private readonly AssetRepository $assetRepository
    ) {
    }

    public function execute(): ResultInterface
    {
        $start = microtime(true);
        $products = $this->dataProvider->getProducts();
        $cardsHtml = $this->cardRenderer->render($products);
        $serverMs = round((microtime(true) - $start) * 1000, 2);
        $css = $this->cardRenderer->sharedCss();
        $jsUrl = $this->assetRepository->getUrl('Ahy_ShadowDomBenchmark::js/dsd-enhance.js');

        $result = $this->rawFactory->create();
        $result->setHeader('Content-Type', 'text/html; charset=UTF-8');
        $result->setContents(<<<HTML
<!doctype html>
<html><head><meta charset="utf-8"><title>Variant 3: SSR + Declarative Shadow DOM</title>
<style>body{font-family:system-ui,sans-serif;max-width:1000px;margin:20px auto;padding:0 16px}
.banner{position:sticky;top:0;background:#409;color:#fff;padding:10px 14px;border-radius:8px;margin-bottom:16px;font-family:monospace;font-size:13px}
</style></head>
<body>
<div class="banner">SSR + Declarative Shadow DOM — server built this in {$serverMs}ms, sealed in a real shadow root (view-source to confirm: it's inside a &lt;template shadowrootmode&gt;, not injected by JS)</div>
<h1>Variant 3: SSR + Declarative Shadow DOM</h1>
<p>The host element below has a real, browser-parsed shadow root — try this with JS disabled, the cards are still there. With JS enabled, a small script enhances (doesn't rebuild) the same content.</p>

<div id="host">
  <template shadowrootmode="open">
    <style>{$css}
    .enhanced-tag{display:none;background:#409;color:#fff;font-size:10px;padding:2px 6px;border-radius:4px;margin-bottom:8px}
    </style>
    <div class="enhanced-tag" id="enh-tag">JS enhancement active</div>
    <div class="grid">{$cardsHtml}</div>
  </template>
</div>

<script src="{$jsUrl}"></script>
</body></html>
HTML
        );
        return $result;
    }
}

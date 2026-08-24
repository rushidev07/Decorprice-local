<?php
declare(strict_types=1);

namespace Ahy\ShadowDomBenchmark\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;

/**
 * Variant 1 — CSR (matches what FalcoSense does today):
 * server sends an EMPTY container; a client JS file fetches /shadowbench/index/data
 * and builds the cards in the browser, after the page has already loaded.
 */
class Csr implements HttpGetActionInterface
{
    public function __construct(
        private readonly RawFactory $rawFactory,
        private readonly AssetRepository $assetRepository
    ) {
    }

    public function execute(): ResultInterface
    {
        $jsUrl = $this->assetRepository->getUrl('Ahy_ShadowDomBenchmark::js/csr-render.js');

        $result = $this->rawFactory->create();
        $result->setHeader('Content-Type', 'text/html; charset=UTF-8');
        $result->setContents(<<<HTML
<!doctype html>
<html><head><meta charset="utf-8"><title>Variant 1: CSR</title>
<style>body{font-family:system-ui,sans-serif;max-width:1000px;margin:20px auto;padding:0 16px}
.banner{position:sticky;top:0;background:#111;color:#fff;padding:10px 14px;border-radius:8px;margin-bottom:16px;font-family:monospace;font-size:13px}
</style></head>
<body>
<div class="banner" id="timing-banner">CSR — waiting for client fetch + render&hellip;</div>
<h1>Variant 1: CSR (today's approach)</h1>
<p>Empty container below. JS fetches JSON, then builds the cards. Check Network tab for the real timeline.</p>
<div id="grid" class="grid"></div>
<script>window.__benchStart = performance.now();</script>
<script src="{$jsUrl}"></script>
</body></html>
HTML
        );
        return $result;
    }
}

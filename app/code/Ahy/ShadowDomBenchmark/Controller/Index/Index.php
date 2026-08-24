<?php
declare(strict_types=1);

namespace Ahy\ShadowDomBenchmark\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;

class Index implements HttpGetActionInterface
{
    public function __construct(private readonly RawFactory $rawFactory)
    {
    }

    public function execute(): ResultInterface
    {
        $result = $this->rawFactory->create();
        $result->setHeader('Content-Type', 'text/html; charset=UTF-8');
        $result->setContents(<<<HTML
<!doctype html>
<html><head><meta charset="utf-8"><title>Shadow DOM / SSR Benchmark</title>
<style>body{font-family:system-ui,sans-serif;max-width:640px;margin:60px auto;line-height:1.6}
a{display:block;padding:14px;margin:10px 0;background:#f0f0f0;border-radius:8px;text-decoration:none;color:#111}
a:hover{background:#e0e0e0}</style></head>
<body>
<h1>Shadow DOM / SSR-Shell Benchmark</h1>
<p>Same product data, three different ways of turning it into HTML. Open each, then check the on-page timing banner and your browser's Network/Performance tab.</p>
<a href="/shadowbench/index/csr">1. CSR (today's approach) &rarr;</a>
<a href="/shadowbench/index/ssrPlain">2. SSR-plain (server builds real HTML) &rarr;</a>
<a href="/shadowbench/index/ssrDsd">3. SSR + Declarative Shadow DOM &rarr;</a>
</body></html>
HTML
        );
        return $result;
    }
}

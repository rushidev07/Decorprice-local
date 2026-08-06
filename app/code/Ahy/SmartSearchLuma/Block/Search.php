<?php
declare(strict_types=1);

namespace Ahy\SmartSearchLuma\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Ahy\SmartSearchLuma\Helper\Data;
use Ahy\SmartSearchLuma\Service\SearchTokenService;

class Search extends Template
{
    public function __construct(
        Context           $context,
        private Data               $helper,
        private SearchTokenService $tokenService,
        array             $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function isEnabled(): bool
    {
        return $this->helper->isFrontendEnabled();
    }

    public function getProductsApiUrl(): string
    {
        $endpoint = $this->helper->getEndpointUrl();
        if (!$endpoint) {
            return 'http://localhost:8080/api/v1/products';
        }
        $parts = parse_url($endpoint);
        $base  = ($parts['scheme'] ?? 'http') . '://' . ($parts['host'] ?? 'localhost');
        if (!empty($parts['port'])) {
            $base .= ':' . $parts['port'];
        }
        return $base . '/api/v1/products';
    }

    public function getSearchToken(): string
    {
        return $this->tokenService->getToken(0);
    }

    public function getPlatformStoreId(): int
    {
        return $this->helper->getPlatformStoreId();
    }

    public function getMediaBaseUrl(): string
    {
        try {
            return rtrim($this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA), '/') . '/catalog/product';
        } catch (\Throwable $e) {
            return '/media/catalog/product';
        }
    }

    public function getSuggestUrl(): string
    {
        $endpoint = $this->helper->getEndpointUrl();
        if (!$endpoint) {
            return 'http://localhost:8080/api/v1/suggest';
        }
        $parts = parse_url($endpoint);
        $base  = ($parts['scheme'] ?? 'http') . '://' . ($parts['host'] ?? 'localhost');
        if (!empty($parts['port'])) {
            $base .= ':' . $parts['port'];
        }
        return $base . '/api/v1/suggest';
    }

    public function getSearchResultUrl(): string
    {
        return $this->getUrl('catalogsearch/result');
    }
}

<?php
declare(strict_types=1);

namespace Ahy\SmartSearchLuma\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Ahy\SmartSearchLuma\Helper\Data;

class NoResultsModal extends Template
{
    public function __construct(
        Context      $context,
        private Data $helper,
        array        $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function isEnabled(): bool
    {
        return $this->helper->isFrontendEnabled() && $this->helper->isNoResultsModalEnabled();
    }

    public function getModalConfig(): array
    {
        return [
            'title'          => $this->helper->getNoResultsModalTitle(),
            'subtitle'       => $this->helper->getNoResultsModalSubtitle(),
            'heading'        => $this->helper->getNoResultsModalHeading(),
            'productCount'   => $this->helper->getNoResultsProductCount(),
            'suggestUrl'     => $this->buildSuggestUrl(),
        ];
    }

    private function buildSuggestUrl(): string
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
}

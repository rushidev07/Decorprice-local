<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @version      v1.0.0
 * @created      2025-03-21
 */

namespace WeSupply\Toolbox\Block\NortonShoppingGuarantee\PackageProtection;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use WeSupply\Toolbox\Helper\NortonShoppingGuarantee\PackageProtection\NsgPpData as PackageProtectionHelper;
use WeSupply\Toolbox\Helper\NortonShoppingGuarantee\PackageProtection\NsgPpNonceData as NonceHelper;

/**
 * Class PackageProtection
 *
 * @package WeSupply\Toolbox\Block\PackageProtection
 */
class NsgPpTemplate extends Template
{
    /**
     * NSG Package Protection API URL
     */
    private const NSGPP_API_URL = 'https://guarantee-cdn.com/SealCore/api/gjs?t=%s&SN=%s';

    /**
     * NSG Package Protection provider identifier
     */
    private const PROVIDER_IDENT = 'Msp198';

    /**
     * @var NonceHelper
     */
    private NonceHelper $nonceHelper;

    /**
     * @var PackageProtectionHelper
     */
    private PackageProtectionHelper $helper;

    /**
     * NsgPpTemplate constructor.
     *
     * @param Context                 $context
     * @param NonceHelper             $nonceHelper
     * @param PackageProtectionHelper $helper
     * @param array                   $data
     */
    public function __construct(
        Context   $context,
        NonceHelper $nonceHelper,
        PackageProtectionHelper $helper,
        array     $data = []
    ) {
        $this->helper = $helper;
        $this->nonceHelper = $nonceHelper;

        parent::__construct($context, $data);
    }

    /**
     * Get NSG Package Protection API endpoint
     *
     * @return string
     */
    public function getNsgPpApiEndpoint(): string
    {
        $apiEndpoint = sprintf(
            self::NSGPP_API_URL,
            self::PROVIDER_IDENT,
            $this->helper->getStoreNumber()
        );

        if (!$this->hasNonce()) {
            return $apiEndpoint;
        }

        return sprintf(
            '%s&nonce=%s',
            $apiEndpoint,
            $this->nonceHelper->getNonce()
        );
    }

    /**
     * Check if nonce is set
     * @return bool
     */
    private function hasNonce(): bool
    {
        return !empty($this->nonceHelper->getNonce());
    }
}

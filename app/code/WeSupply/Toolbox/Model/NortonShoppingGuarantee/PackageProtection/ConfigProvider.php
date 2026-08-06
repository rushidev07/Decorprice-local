<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @version      v1.0.0
 * @created      2025-03-21
 */

namespace WeSupply\Toolbox\Model\NortonShoppingGuarantee\PackageProtection;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ConfigProvider
 *
 * @package WeSupply\Toolbox\Model
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * NsgConfigProvider constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if Norton Shopping Guarantee is enabled
     *
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'isNsgPpEnabled' => (bool) $this->scopeConfig->getValue(
                'norton_shopping_guarantee/package_protection/nsgpp_enabled',
                ScopeInterface::SCOPE_STORE
            )
        ];
    }
}

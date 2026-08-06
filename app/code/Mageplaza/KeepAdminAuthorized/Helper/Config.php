<?php
/**
 * Copyright © Mageplaza. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageplaza\KeepAdminAuthorized\Helper;

/**
 * Class Config
 */
class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Get module settings
     *
     * @param $key
     * @return mixed
     */
    public function getConfigModule($key)
    {
        return $this->scopeConfig
            ->getValue(
                'mageplaza_keep_admin_authorized/general/' . $key,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        if ($this->getConfigModule('enabled')
            && $this->isModuleOutputEnabled('Mageplaza_KeepAdminAuthorized')
        ) {
            return true;
        }
        return false;
    }
}

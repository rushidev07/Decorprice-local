<?php

namespace Unirgy\Dropship\Plugin;

class ProductFlatState
{
    public function aroundIsFlatEnabled(
        \Magento\Catalog\Model\Indexer\Product\Flat\State $productFlatState,
        \Closure $proceed
    ) {
    
        $result = null;
        if ($this->isInstalled() && $this->getProductFlatDisabled()) {
            $result = false;
        }
        return $result===false ? false : $proceed();
    }

    private function isInstalled()
    {
        /** @var \Magento\Framework\App\DeploymentConfig $deploymentConfig */
        $deploymentConfig = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\App\DeploymentConfig::class);
        return $deploymentConfig->isAvailable();
    }

    protected $_productFlatDisabled;
    public function getProductFlatDisabled()
    {
        return $this->_productFlatDisabled;
    }
    public function setProductFlatDisabled($flag)
    {
        return $this->_productFlatDisabled = $flag;
    }
}

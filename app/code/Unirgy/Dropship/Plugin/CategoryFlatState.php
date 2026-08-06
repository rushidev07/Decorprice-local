<?php

namespace Unirgy\Dropship\Plugin;

class CategoryFlatState
{
    public function aroundIsFlatEnabled(
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
        \Closure $proceed
    ) {
    
        $result = null;
        if ($this->isInstalled() && $this->getCategoryFlatDisabled()) {
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

    protected $_categoryFlatDisabled;
    public function getCategoryFlatDisabled()
    {
        return $this->_categoryFlatDisabled;
    }
    public function setCategoryFlatDisabled($flag)
    {
        return $this->_categoryFlatDisabled = $flag;
    }
}

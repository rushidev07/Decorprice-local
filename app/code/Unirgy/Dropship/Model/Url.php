<?php

namespace Unirgy\Dropship\Model;

class Url extends \Magento\Framework\Url
{
    public function getStore()
    {
        $registry = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Framework\Registry');
        if (!$this->hasData('store')) {
            $this->setStore(null);
        }
        return $registry->registry('url_store')
            ? $registry->registry('url_store')
            : $this->_getData('store');
    }
}

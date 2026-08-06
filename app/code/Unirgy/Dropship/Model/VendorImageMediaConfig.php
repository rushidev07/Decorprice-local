<?php

namespace Unirgy\Dropship\Model;

use \Magento\Catalog\Model\Product\Media\Config as BaseMediaConfig;

class VendorImageMediaConfig extends BaseMediaConfig
{
    public function getBaseMediaPathAddition()
    {
        return '';
    }

    public function getBaseMediaUrlAddition()
    {
        return '';
    }

    public function getBaseMediaPath()
    {
        return '';
    }

    public function getBaseMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . '';
    }
}

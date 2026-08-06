<?php

namespace Unirgy\Dropship\Helper\Wysiwyg;

use \Magento\Backend\Helper\Data as HelperData;
use \Magento\Cms\Helper\Wysiwyg\Images as WysiwygImages;
use \Magento\Cms\Model\Wysiwyg\Config;
use \Magento\Framework\App\Helper\Context;
use \Magento\Framework\Filesystem;
use \Magento\Store\Model\StoreManagerInterface;
use \Unirgy\Dropship\Model\Session;

class Images extends WysiwygImages
{
    /**
     * @return \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected function _dirList()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()
            ->get('\Magento\Framework\App\Filesystem\DirectoryList');
    }

    /**
     * @return \Unirgy\Dropship\Helper\Data
     */
    protected function _hlp()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()
            ->get('\Unirgy\Dropship\Helper\Data');
    }

    public function getStorageRoot()
    {
        return $this->_dirList()->getPath('media') . '/' . Config::IMAGE_DIRECTORY
            . '/' . 'udvendor-'.$this->_hlp()->session()->getVendorId();
    }
    public function isUsingStaticUrlsAllowed()
    {
        return true;
    }
}

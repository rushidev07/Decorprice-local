<?php

namespace Unirgy\Dropship\Model;

use \Magento\Catalog\Model\Product\Image;
use Magento\Framework\App\ObjectManager;
use \Magento\Framework\Filesystem\Io\File;
use Unirgy\Dropship\Helper\Data as DropshipHelper;

class ProductImage extends Image
{
    public function clearCache($vId = null)
    {
        $hasImageUpload = true;
        $subDir = '';
        if ($vId instanceof Vendor) {
            $hasImageUpload = $vId->hasImageUpload();
            $vId = $vId->getId();
            $subDir = 'vendor/'.$vId;
        }
        if (!$hasImageUpload) {
            return;
        }
        $baseMediaPath = $this->_catalogProductMediaConfig->getBaseMediaPath();
        if (!empty($baseMediaPath)) {
            $baseMediaPath .= '/';
        }
        foreach ($this->_storeManager->getStores() as $store) {
            $directory = sprintf(
                '%s%s/%s/%s',
                $baseMediaPath,
                'cache',
                $store->getId(),
                $subDir
            );

            $this->_mediaDirectory->delete($directory);

            $this->_coreFileStorageDatabase->deleteFolder($this->_mediaDirectory->getAbsolutePath($directory));
        }
    }
    /**
     * @return DropshipHelper
     */
    protected function _hlp()
    {
        return ObjectManager::getInstance()->get(DropshipHelper::class);
    }
    public function uIsImageExists()
    {
        $imageAsset = $this->_hlp()->getObjectPrivateProperty($this, 'imageAsset', parent::class);
        if ($imageAsset instanceof \Magento\Framework\View\Asset\LocalInterface) {
            return (bool)$imageAsset->getPath();
        } else {
            return true;
        }
    }
}

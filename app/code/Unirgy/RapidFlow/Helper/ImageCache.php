<?php

namespace Unirgy\RapidFlow\Helper;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Unirgy\RapidFlow\Model\Profile;

class ImageCache
{
    protected $_productsToUpdate = [];
    /**
     * @var \Unirgy\RapidFlow\Model\Product\ImageCacheFactory
     */
    protected $_imageCacheFactory;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;
    protected $_rfHlp;
    public function __construct(
        \Unirgy\RapidFlow\Model\Product\ImageCacheFactory $imageCacheFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Unirgy\RapidFlow\Helper\Data $rapidflowHelper
    ) {
        $this->_imageCacheFactory = $imageCacheFactory;
        $this->_productRepository = $productRepository;
        $this->_rfHlp = $rapidflowHelper;
    }

    public function addProductIdForFlushCache($productId, $productData = [])
    {
        $this->_productsToUpdate[$productId] = $productData;
    }
    public function flushProductsImageCache(Profile $profile)
    {
        try {
            $reflImage = new \ReflectionClass('\Magento\Catalog\Model\Product\Image');
            foreach ($reflImage->getProperties() as $__rip) {
                if ($__rip->getName()=='imageAsset') {
                    $__rip->setAccessible(true);
                    \Unirgy\RapidFlow\Model\Product\Image::$myImageAsset = $__rip;
                }
            }
            foreach ($this->_productsToUpdate as $productId => $productData) {
                try {
                    /** @var Product $product */
                    $product = $this->_productRepository->getById($productId);
                } catch (NoSuchEntityException $e) {
                    continue;
                }

                /** @var \Unirgy\RapidFlow\Model\Product\ImageCache $imageCache */
                $imageCache = $this->_imageCacheFactory->create();
                $imageCache->flushProduct($product);

                if ($profile->getData('options/import/import_image_generate')) {
                    if ($this->_rfHlp->isModuleActive('Magento_MediaStorage')) {
                        $imageCache->resizeProduct23($product);
                    } elseif ($this->_rfHlp->compareMageVer('2.2.8')) {
                        $imageCache->resizeProduct($product);
                    }
                }
            }
        } catch (\Exception $e) {
            $profile->getLogger()->warning($e->getMessage());
        }
    }
}

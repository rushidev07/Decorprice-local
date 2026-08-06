<?php

namespace Unirgy\RapidFlow\Helper;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Unirgy\RapidFlow\Model\Profile;
use Magento\Framework\Indexer\CacheContextFactory;

class ProductCache
{
    protected $_productsToUpdate = [];
    protected $_rfHlp;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;
    /**
     * @var CacheContextFactory
     */
    protected $cacheContextFactory;

    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        CacheContextFactory $cacheContextFactory,
        \Unirgy\RapidFlow\Helper\Data $rapidflowHelper
    ) {
        $this->_rfHlp = $rapidflowHelper;
        $this->eventManager = $eventManager;
        $this->cacheContextFactory = $cacheContextFactory;
    }

    public function addProductIdForFlushCache($productId, $productData = [])
    {
        $this->_productsToUpdate[$productId] = $productData;
    }
    public function flushProductsCache(Profile $profile)
    {
        try {
            $cacheContext = $this->cacheContextFactory->create();
            $cacheContext->registerEntities(Product::CACHE_TAG, array_keys($this->_productsToUpdate));
            $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $cacheContext]);
        } catch (\Exception $e) {
            $profile->getLogger()->warning($e->getMessage());
        }
    }
}

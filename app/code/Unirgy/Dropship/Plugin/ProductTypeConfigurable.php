<?php

namespace Unirgy\Dropship\Plugin;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurable;

class ProductTypeConfigurable
{
    protected $_hlp;
    /**
     * @var \Unirgy\Dropship\Model\ResourceModel\ConfigurableProductCollectionFactory
     */
    protected $configurableProductCollectionFactory;

    public function __construct(
        \Unirgy\Dropship\Helper\Data $udropshipHelper,
        \Unirgy\Dropship\Model\ResourceModel\ConfigurableProductCollectionFactory $configurableProductCollectionFactory
    ) {
        $this->_hlp = $udropshipHelper;
        $this->configurableProductCollectionFactory = $configurableProductCollectionFactory;
    }
    public function aroundGetUsedProductCollection(
        TypeConfigurable $subject,
        \Closure $next,
        $product
    ) {
        if ($this->_hlp->isVendorPortalAction()) {
            $this->_hlp->setObjectPrivateProperty($subject, '_productCollectionFactory', $this->configurableProductCollectionFactory);
        }
        return $next($product);
    }
    public function aroundGetUsedProducts(
        TypeConfigurable $subject,
        \Closure $next,
        $product,
        $requiredAttributeIds = null
    ) {
        if ($this->_hlp->isVendorPortalAction()) {
            $this->_hlp->setObjectPrivateProperty($subject, '_productCollectionFactory', $this->configurableProductCollectionFactory);
            $reflector = new \ReflectionObject($subject);
            try {
                $loadUsedProducts = $reflector->getMethod('loadUsedProducts');
                $getCfgUsedProdCol = $reflector->getMethod('getConfiguredUsedProductCollection');
                if ($loadUsedProducts) {
                    $getCfgUsedProdCol->setAccessible(true);
                    $collection = $getCfgUsedProdCol->invoke($subject, $product, false);
                    $usedProducts = array_values($collection->getItems());
                    $dataFieldName = $this->_hlp->getObjectPrivateProperty($subject, '_usedProducts', TypeConfigurable::class);
                    $product->setData($dataFieldName, $usedProducts);
                    return $product->getData($dataFieldName);
                }
            } catch (\ReflectionException $e) {}
        }
        return $next($product, $requiredAttributeIds);
    }
}

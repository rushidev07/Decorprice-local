<?php

namespace Unirgy\Dropship\Plugin;

class ProductRepository
{
    /**
     * @var \Unirgy\Dropship\Helper\Data
     */
    private $_hlp;

    public function __construct(
        \Unirgy\Dropship\Helper\Data $dropshipHelper
    )
    {
        $this->_hlp = $dropshipHelper;
    }

    /**
     * @return \Magento\NegotiableQuoteSharedCatalog\Plugin\Catalog\Api\ProductRepositoryApplyFilter
     */
    protected function b2bNqPlugin()
    {
        return $this->_hlp->getObj('Magento\NegotiableQuoteSharedCatalog\Plugin\Catalog\Api\ProductRepositoryApplyFilter');
    }

    public function afterGetById(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Magento\Catalog\Api\Data\ProductInterface $product
    ) {
        if (!$this->_hlp->isVendorPortalAction() && $this->_hlp->isB2B()) {
            $this->b2bNqPlugin()->afterGetById($subject, $product);
        }
        return $product;
    }

    public function afterGet(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Magento\Catalog\Api\Data\ProductInterface $product
    ) {
        if (!$this->_hlp->isVendorPortalAction() && $this->_hlp->isB2B()) {
            $this->b2bNqPlugin()->afterGet($subject, $product);
        }
        return $product;
    }

}

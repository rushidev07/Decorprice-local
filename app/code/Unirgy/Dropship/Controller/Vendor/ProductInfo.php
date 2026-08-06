<?php

namespace Unirgy\Dropship\Controller\Vendor;

class ProductInfo extends AbstractVendor
{
    protected function resultJsonFactory()
    {
        return $this->_hlp->getObj('Magento\Framework\Controller\Result\JsonFactory');
    }
    public function _productVisibility()
    {
        return $this->_hlp->getObj('Magento\Catalog\Model\Product\Visibility');
    }
    public function execute()
    {
        $v = $this->_hlp->session()->getVendor();
        $resultJson = $this->resultJsonFactory()->create();
        /** @var \Unirgy\Dropship\Model\ResourceModel\ProductCollection $collection */
        $collection = $collection = $this->_hlp->createObj('Unirgy\Dropship\Model\ResourceModel\ProductCollection')
            ->setFlag('udskip_price_index',1)
            ->setFlag('udskip_shared_catalog',1)
            ->setFlag('has_group_entity', 1)
            ->setFlag('has_stock_status_filter', 1);
        $collection->addAttributeToSelect(['sku', 'name', 'status', 'price']);
        $collection->addAttributeToFilter('udropship_vendor', $v->getId());
        $collection->addAttributeToFilter('type_id', 'simple');
        $collection->addAttributeToFilter('visibility', ['in'=>$this->_productVisibility()->getVisibleInSiteIds()]);
        $collection->addIdFilter($this->getRequest()->getParam('id'));
        $data = $collection->getFirstItem()->getData();
        $data['id'] = $data['entity_id'];
        $data['price'] = $this->_hlp->formatPrice($data['price'], false);
        $resultJson->setData($data);
        return $resultJson;
    }
}

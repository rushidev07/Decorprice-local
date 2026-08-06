<?php

namespace Unirgy\Dropship\Controller\Vendor;

class ProductSuggest extends AbstractVendor
{
    protected function _resourceHelper()
    {
        return $this->_hlp->getObj('Magento\Framework\DB\Helper');
    }
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
        $escapedLabelPart = $this->_resourceHelper()->addLikeEscape(
            $this->getRequest()->getParam('q'),
            ['position' => 'any']
        );
        /** @var \Unirgy\Dropship\Model\ResourceModel\ProductCollection $collection */
        $collection = $collection = $this->_hlp->createObj('Unirgy\Dropship\Model\ResourceModel\ProductCollection')
            ->setFlag('udskip_price_index',1)
            ->setFlag('udskip_shared_catalog',1)
            ->setFlag('has_group_entity', 1)
            ->setFlag('has_stock_status_filter', 1);
        $collection->addAttributeToSelect(['sku', 'name', 'status', 'price']);
        $collection->addAttributeToFilter('udropship_vendor', $v->getId());
        $collection->addAttributeToFilter('type_id', 'simple');
        $collection->addAttributeToFilter([
            ['attribute'=>'name','like' => $escapedLabelPart],
            ['attribute'=>'sku','like' => $escapedLabelPart],
        ]);
        $collection->addAttributeToFilter('visibility', ['in'=>$this->_productVisibility()->getVisibleInSiteIds()]);

        $page = $this->getRequest()->getParam('page', 1);
        $collection->setPage($page, 20);

        $result = [
            'items' => [],
            'total_count' => $collection->getSize()
        ];
        foreach ($collection->getItems() as $prod) {
            $result['items'][] = [
                'id' => $prod->getId(),
                'label' => sprintf('%s: %s, %s', $prod->getSku(), $prod->getName(), $this->_hlp->formatPrice($prod->getPrice(), false)),
                'code' => $prod->getId(),
            ];
        }
        $resultJson->setData($result);
        return $resultJson;
    }
}

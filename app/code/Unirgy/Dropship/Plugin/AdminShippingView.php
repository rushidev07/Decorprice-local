<?php
namespace Unirgy\Dropship\Plugin;

use Magento\Catalog\Model\Product\Attribute\Repository;

class AdminShippingView
{
    public function beforeSetLayout(\Magento\Shipping\Block\Adminhtml\View $viewBlock, $route = '', $params = [])
    {
        $viewBlock->addButton('mark_as_shipped',
            [
                'label' => __('Mark As Shipped'),
                'class' => 'save',
                'onclick' => 'setLocation(\'' . $this->getMarkShippedUrl($viewBlock) . '\')'
            ]);
    }
    public function getMarkShippedUrl($viewBlock)
    {
        return $viewBlock->getUrl('udropship/shipment/ship', ['id' => $viewBlock->getShipment()->getId()]);
    }
}

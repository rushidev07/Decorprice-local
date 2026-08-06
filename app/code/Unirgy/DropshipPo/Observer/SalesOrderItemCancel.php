<?php

namespace Unirgy\DropshipPo\Observer;

use Magento\CatalogInventory\Model\StockFactory;
use Magento\Catalog\Model\ProductFactory as ModelProductFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Config;
use Psr\Log\LoggerInterface;
use Unirgy\DropshipPo\Helper\Data as DropshipPoHelperData;
use Unirgy\DropshipPo\Model\Source as ModelSource;
use Unirgy\Dropship\Helper\Data as HelperData;
use Unirgy\Dropship\Model\Source;
use Unirgy\Dropship\Model\Vendor\ProductFactory;

class SalesOrderItemCancel extends AbstractObserver implements ObserverInterface
{

    public function execute(Observer $observer)
    {
        $item = $observer->getItem();
        $order = $item->getOrder();
        $poHlp = $this->_poHlp;
        $poHlp->initOrderUdposCollection($order);
        foreach ($order->getShipmentsCollection() as $shipment) {
            $canCancel = !in_array($shipment->getUdropshipStatus(), [
                Source::SHIPMENT_STATUS_SHIPPED,
                Source::SHIPMENT_STATUS_DELIVERED,
                Source::SHIPMENT_STATUS_CANCELED,
            ]);
            if ($canCancel) {
                $canCancel = false;
                foreach ($shipment->getAllItems() as $sItem) {
                    if ($sItem->getOrderItemId()==$item->getId() && $item->getQtyToInvoice()) {
                        $canCancel = true;
                        break;
                    }
                }
                $shipment->setUdCanCancel($shipment->getUdCanCancel()||$canCancel);
            }
        }
        foreach ($order->getUdposCollection() as $udpo) {
            $canCancel = !in_array($udpo->getUdropshipStatus(), [
                ModelSource::UDPO_STATUS_SHIPPED,
                ModelSource::UDPO_STATUS_CANCELED,
                ModelSource::UDPO_STATUS_DELIVERED,
            ]);
            if ($canCancel) {
                $canCancel = false;
                foreach ($udpo->getAllItems() as $poItem) {
                    if ($poItem->getOrderItemId()==$item->getId() && $item->getQtyToInvoice()) {
                        $canCancel = true;
                        break;
                    }
                }
                $udpo->setUdCanCancel($udpo->getUdCanCancel()||$canCancel);
            }
        }
    }
}

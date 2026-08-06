<?php

namespace Unirgy\DropshipPo\Observer;

use Magento\CatalogInventory\Model\StockFactory;
use Magento\Catalog\Model\ProductFactory as ModelProductFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Config;
use Magento\Sales\Model\Order\Shipment\CommentFactory;
use Psr\Log\LoggerInterface;
use Unirgy\DropshipPo\Helper\Data as DropshipPoHelperData;
use Unirgy\DropshipPo\Model\Source as ModelSource;
use Unirgy\Dropship\Helper\Data as HelperData;
use Unirgy\Dropship\Model\Source;
use Unirgy\Dropship\Model\Vendor\ProductFactory;

class OrderCancelAfter extends AbstractObserver implements ObserverInterface
{
    /**
     * @var CommentFactory
     */
    protected $_shipmentCommentFactory;

    public function __construct(
        CommentFactory $shipmentCommentFactory,
        \Unirgy\Dropship\Helper\Data $udropshipHelper,
        \Unirgy\DropshipPo\Helper\Data $udpoHelper,
        \Psr\Log\LoggerInterface $logger,
        \Unirgy\Dropship\Model\Vendor\ProductFactory $vendorProductFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Quote\Model\Quote\Config $quoteConfig
    )
    {
        $this->_shipmentCommentFactory = $shipmentCommentFactory;

        parent::__construct($udropshipHelper, $udpoHelper, $logger, $vendorProductFactory, $productFactory, $quoteConfig);
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        $hlp = $this->_hlp;
        $poHlp = $this->_poHlp;
        $poHlp->initOrderUdposCollection($order);
        foreach ($order->getShipmentsCollection() as $shipment) {
            if ($shipment->getUdCanCancel() && (!($sPO = $this->_getShipmentPo($shipment, $order)) || !$sPO->getUdCanCancel())) {
                $poHlp->cancelShipment($shipment, true);
                $statusCanceled  = Source::SHIPMENT_STATUS_CANCELED;
                $statuses = $this->_hlp->src()->setPath('shipment_statuses')->toOptionHash();
                $hlp->processShipmentStatusSave($shipment, $statusCanceled);
                $commentText = __("ORDER WAS CANCELED: shipment status was changed to %1", $statuses[$statusCanceled]);
                $comment = $this->_shipmentCommentFactory->create()
                    ->setComment($commentText)
                    ->setIsCustomerNotified(false)
                    ->setIsVendorNotified(true)
                    ->setIsVisibleToVendor(true)
                    ->setUdropshipStatus($statuses[$statusCanceled]);
                $shipment->addComment($comment);
                $this->_hlp->sendShipmentCommentNotificationEmail($shipment, $commentText);
            }
        }
        foreach ($order->getUdposCollection() as $udpo) {
            if ($udpo->getUdCanCancel()) {
                $poHlp->cancelPo($udpo, true);
                $poHlp->processPoStatusSave($udpo, ModelSource::UDPO_STATUS_CANCELED, true, false, __('ORDER WAS CANCELED'), true, true);
            }
        }
    }
}

<?php

namespace Unirgy\DropshipPo\Controller\Vendor;

use Magento\Store\Model\StoreManagerInterface;
use Unirgy\Dropship\Model\Source;

class CreateShipment extends AbstractVendor
{
    public function execute()
    {
        $result = [];
        try {
            $udpoHlp = $this->_poHlp;
            $udpos = $this->getVendorPoCollection();
            if (!$udpos->getSize()) {
                throw new \Exception('No purchase orders found for these criteria');
            }
            $createdShipments = 0;
            $shipments = [];
            foreach ($udpos as $udpo) {
                $udpoHlp->createReturnAllShipments=true;
                if (($_shipments = $udpoHlp->createShipmentFromPo($udpo, [], true, true, true))) {
                    foreach ($_shipments as $_shipment) {
                        $createdShipments++;
                        $_shipment->setNewShipmentFlag(true);
                        $_shipment->setDeleteOnFailedLabelRequestFlag(true);
                        $_shipment->setCreatedByVendorFlag(true);
                        $shipments[] = $_shipment;
                    }
                } elseif ($this->getRequest()->getParam('send_customer_notification')) {
                    foreach ($udpo->getShipmentsCollection() as $_s) {
                        if ($_s->getUdropshipStatus()==Source::SHIPMENT_STATUS_CANCELED) {
                            continue;
                        }
                        $shipments[] = $_s;
                        break;
                    }
                }
                $udpoHlp->createReturnAllShipments=false;
            }
            if (empty($shipments)) {
                throw new \Exception('Cannot create shipments (maybe nothing to create)');
            }

            $this->messageManager->addSuccess(__('Created %1 shipments', $createdShipments));

            $notificationsSent = 0;
            if ($this->getRequest()->getParam('send_customer_notification')) {
                /** @var \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender */
                $shipmentSender = $this->_hlp->getObj('\Magento\Sales\Model\Order\Email\Sender\ShipmentSender');
                foreach ($shipments as $shipment) {
                    $shipmentSender->send($shipment);
                    $shipment->setEmailSent(true);
                    $notificationsSent++;
                }
                $this->messageManager->addSuccess(__('Sent %1 shipment customer notifications', $notificationsSent));
            }

        } catch (\Exception $e) {
            $this->_hlp->getObj('\Psr\Log\LoggerInterface')->error($e);
            if ($this->getRequest()->getParam('use_json_response')) {
                $result = [
                    'error'=>true,
                    'message'=>$e->getMessage()
                ];
            } else {
                $this->messageManager->addError(__($e->getMessage()));
            }
        }
        if ($this->getRequest()->getParam('use_json_response')) {
            return $this->_resultRawFactory->create()->setContents(
                $this->_hlp->jsonEncode($result)
            );
        } else {
            return $this->_redirect('udpo/vendor/', ['_current'=>true, '_query'=>['submit_action'=>'']]);
        }
    }
}

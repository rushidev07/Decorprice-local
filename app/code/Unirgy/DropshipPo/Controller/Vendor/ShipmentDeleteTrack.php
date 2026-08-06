<?php

namespace Unirgy\DropshipPo\Controller\Vendor;

use Magento\Store\Model\StoreManagerInterface;

class ShipmentDeleteTrack extends AbstractVendor
{
    public function execute()
    {
        $hlp = $this->_hlp;
        $r = $this->getRequest();
        $id = $r->getParam('id');
        $shipment = $this->_hlp->createObj('Magento\Sales\Model\Order\Shipment')->load($id);
        $vendor = $hlp->getVendor($shipment->getUdropshipVendor());
        $session = $this->_getSession();

        if (!$shipment->getId()) {
            return;
        }
        $deleteTrack = $r->getParam('delete_track');
        if ($deleteTrack) {
            $track = $this->_trackFactory->create()->load($deleteTrack);
            if ($track->getId()) {

                try {
                    $labelModel = $this->_hlp->getLabelCarrierInstance($track->getCarrierCode())->setVendor($vendor);
                    try {
                        $labelModel->voidLabel($track);
                        $this->_hlp->addShipmentComment(
                            $shipment,
                            __('%1 voided tracking ID %2', $vendor->getVendorName(), $track->getNumber())
                        );
                        $this->messageManager->addSuccess(__('Track %1 was voided', $track->getNumber()));
                    } catch (\Exception $e) {
                        $this->_hlp->addShipmentComment(
                            $shipment,
                            __('%1 attempted to void tracking ID %2: %3', $vendor->getVendorName(), $track->getNumber(), $e->getMessage())
                        );
                        $this->messageManager->addSuccess(__('Problem voiding track %1: %2', $track->getNumber(), $e->getMessage()));
                    }
                } catch (\Exception $e) {
                    // doesn't support voiding
                }

                $track->delete();
                if ($track->getPackageCount()>1) {
                    foreach ($this->_trackFactory->create()->getCollection()
                        ->addAttributeToFilter('master_tracking_id', $track->getMasterTrackingId())
                        as $_track
                    ) {
                        $_track->delete();
                    }
                }
                $this->_hlp->addShipmentComment(
                    $shipment,
                    __('%1 deleted tracking ID %2', $vendor->getVendorName(), $track->getNumber())
                );
                $shipment->save();
                #$save = true;
                $highlight['tracking'] = true;
                $this->messageManager->addSuccess(__('Track %1 was deleted', $track->getNumber()));
            } else {
                $this->messageManager->addError(__('Track %1 was not found', $track->getNumber()));
            }
        }
        $this->_forward('shipmentInfo');
    }
}

<?php

namespace Unirgy\DropshipPo\Controller\Vendor;

use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Unirgy\DropshipPo\Model\Source;
use Unirgy\Dropship\Model\Source as ModelSource;

class UdpoPost extends AbstractVendor
{
    public function getPo($id)
    {
        return $this->_poFactory->create()->load($id);
    }
    public function execute()
    {
        $hlp = $this->_hlp;
        $udpoHlp = $this->_poHlp;
        $r = $this->getRequest();
        $id = $r->getParam('id');
        $udpo = $this->getPo($id);
        $vendor = $hlp->getVendor($udpo->getUdropshipVendor());
        $session = $this->_getSession();

        if (!$udpo->getId()) {
            return;
        }

        try {
            $store = $udpo->getOrder()->getStore();

            $track = null;
            $highlight = [];

            $partial = $r->getParam('partial_availability');
            $partialQty = $r->getParam('partial_qty');

            $printLabel = $r->getParam('print_label');
            $number = $r->getParam('tracking_id');

            $carrier = $r->getParam('carrier');
            $carrierTitle = $r->getParam('carrier_title');

            $_explicitMethods = $this->_hlp->src()->setPath('explicit_system_methods')->toOptionHash();

            $notifyOn = $this->_hlp->getScopeConfig('udropship/customer/notify_on', $store);
            $pollTracking = $this->_hlp->getScopeConfig('udropship/customer/poll_tracking', $store);
            $poAutoComplete = $this->_hlp->getScopeConfig('udropship/vendor/auto_complete_po', $store);
            $autoComplete = $this->_hlp->getScopeConfig('udropship/vendor/auto_shipment_complete', $store);

            $poStatusShipped = Source::UDPO_STATUS_SHIPPED;
            $poStatusDelivered = Source::UDPO_STATUS_DELIVERED;
            $poStatusCanceled = Source::UDPO_STATUS_CANCELED;
            $poStatuses = $this->_poHlp->src()->setPath('po_statuses')->toOptionHash();
            // if label was printed
            if ($printLabel) {
                $poStatus = $r->getParam('is_shipped') ? $poStatusShipped : Source::UDPO_STATUS_PARTIAL;
                $isShipped = $r->getParam('is_shipped') ? true : false;
            } else { // if status was set manually
                $poStatus = $r->getParam('status');
                $isShipped = $poStatus == $poStatusShipped || $poStatus==$poStatusDelivered || $autoComplete && ($poStatus==='' || is_null($poStatus));
            }

            //if ($printLabel || $number || ($partial=='ship' && $partialQty)) {
            $partialQty = $partialQty ? $partialQty : [];
            if ($r->getParam('use_label_shipping_amount')) {
                $udpo->setUseLabelShippingAmount(true);
            } elseif ($r->getParam('shipping_amount')) {
                $udpo->setShipmentShippingAmount($r->getParam('shipping_amount'));
            }
            $udpo->setUdpoNoSplitPoFlag(true);
            $udpoHlp->createReturnAllShipments=true;
            $shipment = $__shipments = $udpoHlp->createShipmentFromPo($udpo, $partialQty, true, true, true);
            $udpoHlp->createReturnAllShipments=false;

            if ($__shipments) {

                if (!is_array($__shipments)) {
                    $__shipments = [$__shipments];
                }

                foreach ($__shipments as $shipment) {
                    $shipment->setNewShipmentFlag(true);
                    $shipment->setDeleteOnFailedLabelRequestFlag(true);
                    $shipment->setCreatedByVendorFlag(true);
                }
            }
            //}

            // if label to be printed
            if ($printLabel) {
                $data = [
                    'weight' => $r->getParam('weight'),
                    'value' => $r->getParam('value'),
                    'length' => $r->getParam('length'),
                    'width' => $r->getParam('width'),
                    'height' => $r->getParam('height'),
                    'reference' => $r->getParam('reference'),
                    'package_count' => $r->getParam('package_count'),
                ];

                $oldUdropshipMethod = $udpo->getUdropshipMethod();
                $oldUdropshipMethodDesc = $udpo->getUdropshipMethodDescription();

                $extraLblInfo = $r->getParam('extra_label_info');
                $extraLblInfo = is_array($extraLblInfo) ? $extraLblInfo : [];
                $data = array_merge($data, $extraLblInfo);
                foreach ($__shipments as $shipment) {
                    $oldUdropshipMethod = $shipment->getUdropshipMethod();
                    $oldUdropshipMethodDesc = $shipment->getUdropshipMethodDescription();
                    if ($r->getParam('use_method_code')) {
                        list($useCarrier, $useMethod) = explode('_', $r->getParam('use_method_code'), 2);
                        if (!empty($useCarrier) && !empty($useMethod)) {
                            $shipment->setUdropshipMethod($r->getParam('use_method_code'));
                            $carrierMethods = $this->_hlp->getCarrierMethods($useCarrier);
                            foreach ($_explicitMethods as $_emc=>$_emt) {
                                if (0===strpos($_emc, $useCarrier.'_')) {
                                    $carrierMethods[$_emc] = $_emt;
                                }
                            }
                            $shipment->setUdropshipMethodDescription(
                                $this->_hlp->getScopeConfig('carriers/' . $useCarrier . '/title', $shipment->getOrder()->getStoreId())
                                . ' - ' . $carrierMethods[$useMethod]
                            );
                        }
                    }
                }
                // generate label
                try {
	                $batch = $this->_labelBatchFactory->create()
	                    ->setVendor(ObjectManager::getInstance()->get('Unirgy\Dropship\Model\Session')->getVendor())
	                    ->processShipments($__shipments, $data, ['mark_shipped'=>$isShipped]);
                } catch (\Exception $e) {
                    if ($r->getParam('use_method_code')) {
                        $this->setShipmentUdropshipMethod($__shipments, $oldUdropshipMethod, $oldUdropshipMethodDesc);
                    }
            		throw $e;
                }

                // if batch of 1 label is successfull
                if ($batch->getShipmentCnt()) {
                    $url = $this->_url->getUrl('udropship/vendor/reprintLabelBatch', ['batch_id'=>$batch->getId()]);
                    $this->_registry->register('udropship_download_url', $url);

                    $newTracks = $batch->getNewTracks();
                    foreach ($newTracks as $__newTrack) {
                        foreach ($__shipments as $shipment) {
                            if ($__newTrack->getShipment()==$shipment) {
                                $this->_hlp->addShipmentComment(
                                    $shipment,
                                    __('%1 printed label ID %2', $vendor->getVendorName(), $__newTrack->getNumber())
                                );
                                $shipment->save();
                            }
                        }
                    }
                    if (($track = $batch->getLastTrack())) {
                        $this->messageManager->addSuccess('Label was succesfully created');
                        $highlight['tracking'] = true;
                    }
                } else {
                    if ($batch->getErrors()) {
                    	$batchError = '';
                        foreach ($batch->getErrors() as $error=>$cnt) {
                        	$batchError .= __($error, $cnt)." \n";
                        }
                        if ($r->getParam('use_method_code')) {
                            $this->setShipmentUdropshipMethod($__shipments, $oldUdropshipMethod, $oldUdropshipMethodDesc);
                        }
	            		throw new \Exception($batchError);
                    } else {
                        if ($r->getParam('use_method_code')) {
                            $this->setShipmentUdropshipMethod($__shipments, $oldUdropshipMethod, $oldUdropshipMethodDesc);
                        }
	                    $batchError = 'No items are available for shipment';
	            		throw new \Exception($batchError);
                    }
                }

            } elseif ($number) { // if tracking id was added manually
                $method = explode('_', $shipment->getUdropshipMethod(), 2);
                $title = $this->_hlp->getScopeConfig('carriers/'.$method[0].'/title', $store);
                $_carrier = $method[0];
                if (!empty($carrier) && !empty($carrierTitle)) {
                    $_carrier = $carrier;
                    $title = $carrierTitle;
                }
                $track = $this->_trackFactory->create()
                    ->setTrackNumber($number)
                    ->setCarrierCode($_carrier)
                    ->setTitle($title);

                $shipment->addTrack($track);

                $this->_hlp->processTrackStatus($track, true, $isShipped);

                $this->_hlp->addShipmentComment(
                    $shipment,
                    __('%1 added tracking ID %2', $vendor->getVendorName(), $number)
                );
                $shipment->save();
                $this->messageManager->addSuccess(__('Tracking ID has been added'));

                $highlight['tracking'] = true;
            }

            $udpoStatuses = false;
            if ($this->_hlp->getScopeConfig('udropship/vendor/is_restrict_udpo_status')) {
                $udpoStatuses = $this->_hlp->getScopeConfig('udropship/vendor/restrict_udpo_status');
                if (!is_array($udpoStatuses)) {
                    $udpoStatuses = explode(',', $udpoStatuses);
                }
            }

            if (!$printLabel && !is_null($poStatus) && $poStatus!=='' && $poStatus!=$udpo->getUdropshipStatus()
                && (!$udpoStatuses || (in_array($udpo->getUdropshipStatus(), $udpoStatuses) && in_array($poStatus, $udpoStatuses)))
            ) {
                $oldStatus = $udpo->getUdropshipStatus();
                $poStatusChanged = false;
                if ($r->getParam('force_status_change_flag')) {
                    $udpo->setForceStatusChangeFlag(true);
                }
                if ($oldStatus==$poStatusCanceled && !$udpo->getForceStatusChangeFlag()) {
                    throw new \Exception(__('Canceled purchase order cannot be reverted'));
                }
                if ($poStatus==$poStatusShipped || $poStatus==$poStatusDelivered) {
                    foreach ($udpo->getShipmentsCollection() as $_s) {
                        if ($_s->getUdropshipStatus()==ModelSource::SHIPMENT_STATUS_CANCELED) {
                            continue;
                        }
                        $hlp->completeShipment($_s, true, $poStatus==$poStatusDelivered);
                    }
                    if (isset($_s)) {
                        $hlp->completeOrderIfShipped($_s, true);
                    }
                    $poStatusChanged = $udpoHlp->processPoStatusSave($udpo, $poStatus, true, $vendor);
                } elseif ($poStatus == $poStatusCanceled) {
                    $udpo->setFullCancelFlag($r->getParam('full_cancel'));
                    $udpo->setNonshippedCancelFlag($r->getParam('nonshipped_cancel'));
                    $this->_poHlp->cancelPo($udpo, true, $vendor);
                    $poStatusChanged = $udpoHlp->processPoStatusSave($udpo, $poStatus, true, $vendor);
                } else {
                    $poStatusChanged = $udpoHlp->processPoStatusSave($udpo, $poStatus, true, $vendor);
                }
                $udpo->getCommentsCollection()->save();
                if ($poStatusChanged) {
                    $this->messageManager->addSuccess(__('Purchase order status has been changed'));
                } else {
                    $this->messageManager->addError(__('Cannot change purchase order status'));
                }
            }

        	if (!empty($shipment) && $shipment->getNewShipmentFlag() && !$shipment->isDeleted()) {
        		$shipment->setNoInvoiceFlag(false);
            	$udpoHlp->invoiceShipment($shipment);
            }

            $comment = $r->getParam('comment');
            if ($comment || $partial=='inform' && $partialQty) {
                if ($partialQty) {
                    $comment .= "\n\nPartial Availability:\n";
                    foreach ($udpo->getAllItems() as $item) {
                        if (!array_key_exists($item->getId(), $partialQty) || '' === $partialQty[$item->getId()]) {
                            continue;
                        }
                        $comment .= __('%1 x [%2] %3', $partialQty[$item->getId()], $item->getName(), $item->getSku())."\n";
                    }
                }

                //$udpo->addComment($comment, false, true)->getCommentsCollection()->save();
                $this->_poHlp->sendVendorComment($udpo, $comment);
                $this->messageManager->addSuccess(__('Your comment has been sent to store administrator'));

                $highlight['comment'] = true;
            }

            $session->setHighlight($highlight);
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        $this->_forward('udpoInfo');
    }
    public function setShipmentUdropshipMethod($shipments, $udropshipMethod, $udropshipMethodDesc)
    {
        if (!is_array($shipments)) {
            $shipments = [$shipments];
        }
        foreach ($shipments as $shipment) {
            $shipment->setUdropshipMethod($udropshipMethod);
            $shipment->setUdropshipMethodDescription($udropshipMethodDesc);
            $shipment->getResource()->saveAttribute($shipment, 'udropship_method');
            $shipment->getResource()->saveAttribute($shipment, 'udropship_method_description');
        }
    }
}

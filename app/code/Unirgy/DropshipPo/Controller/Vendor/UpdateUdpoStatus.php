<?php

namespace Unirgy\DropshipPo\Controller\Vendor;

use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Unirgy\DropshipPo\Model\Source;
use Unirgy\Dropship\Model\Source as ModelSource;

class UpdateUdpoStatus extends AbstractVendor
{
    public function execute()
    {
        try {
            $udpos = $this->getVendorPoCollection();
            $r = $this->getRequest();
            $poStatus = $this->getRequest()->getParam('update_status');

            $poStatusShipped = Source::UDPO_STATUS_SHIPPED;
            $poStatusDelivered = Source::UDPO_STATUS_DELIVERED;
            $poStatusCanceled = Source::UDPO_STATUS_CANCELED;
            $poStatuses = $this->_poHlp->src()->setPath('po_statuses')->toOptionHash();

            if (!$udpos->getSize()) {
                throw new \Exception(__('No purchase orders found for these criteria'));
            }
            if (is_null($poStatus) || $poStatus==='') {
                throw new \Exception(__('No status selected'));
            }

            $vendorId = $this->_getSession()->getId();
            $vendor = $this->_hlp->getVendor($vendorId);

            $hlp = $this->_hlp;
            $udpoHlp = $this->_poHlp;

            $udpoStatuses = false;
            if ($this->_hlp->getScopeConfig('udropship/vendor/is_restrict_udpo_status')) {
                $udpoStatuses = $this->_hlp->getScopeConfig('udropship/vendor/restrict_udpo_status');
                if (!is_array($udpoStatuses)) {
                    $udpoStatuses = explode(',', $udpoStatuses);
                }
            }

            foreach ($udpos as $udpo) {
                if (!is_null($poStatus) && $poStatus!=='' && $poStatus!=$udpo->getUdropshipStatus()
                    && (!$udpoStatuses || (in_array($udpo->getUdropshipStatus(), $udpoStatuses) && in_array($poStatus, $udpoStatuses)))
                ) {
                    $oldStatus = $udpo->getUdropshipStatus();
                    if ($oldStatus==$poStatusCanceled) {
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
                        $udpoHlp->processPoStatusSave($udpo, $poStatus, true, $vendor);
                    } elseif ($poStatus == $poStatusCanceled) {
                        $udpo->setFullCancelFlag($r->getParam('full_cancel'));
                    	$udpo->setNonshippedCancelFlag($r->getParam('nonshipped_cancel'));
                        $this->_poHlp->cancelPo($udpo, true, $vendor);
                        $udpoHlp->processPoStatusSave($udpo, $poStatus, true, $vendor);
                    } else {
                        $udpoHlp->processPoStatusSave($udpo, $poStatus, true, $vendor);
                    }
                }
            }
            $this->messageManager->addSuccess(__('Purchase Order status has been updated for the selected orders'));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $this->_redirect('udpo/vendor/', ['_current'=>true, '_query'=>['submit_action'=>'']]);
    }
}

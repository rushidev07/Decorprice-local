<?php

namespace Unirgy\DropshipPo\Controller\Vendor;

use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Unirgy\DropshipPo\Model\Source;
use Unirgy\Dropship\Model\Source as ModelSource;

class AddUdpoComment extends AbstractVendor
{
	public function execute()
    {
        $hlp = $this->_hlp;
        $udpoHlp = $this->_poHlp;
        $r = $this->getRequest();
        $id = $r->getParam('id');
        $udpo = $this->_poFactory->create()->load($id);
        $vendor = $hlp->getVendor($udpo->getUdropshipVendor());
        $session = $this->_getSession();

        if (!$udpo->getId()) {
            return;
        }

        try {
            $store = $udpo->getOrder()->getStore();

            $highlight = [];

            $partial = $r->getParam('partial_availability');
            $partialQty = $r->getParam('partial_qty');

            $notifyOn = $this->_hlp->getScopeConfig('udropship/customer/notify_on', $store);
            $pollTracking = $this->_hlp->getScopeConfig('udropship/customer/poll_tracking', $store);
            $poAutoComplete = $this->_hlp->getScopeConfig('udropship/vendor/auto_complete_po', $store);
            $autoComplete = $this->_hlp->getScopeConfig('udropship/vendor/auto_shipment_complete', $store);

            $poStatusShipped = Source::UDPO_STATUS_SHIPPED;
            $poStatusDelivered = Source::UDPO_STATUS_DELIVERED;
            $poStatusCanceled = Source::UDPO_STATUS_CANCELED;
            $poStatuses = $this->_poHlp->src()->setPath('po_statuses')->toOptionHash();
            // if label was printed
            $poStatus = $r->getParam('status');
            $isShipped = $poStatus == $poStatusShipped || $poStatus==$poStatusDelivered || $autoComplete && ($poStatus==='' || is_null($poStatus));

            $udpoStatuses = false;
            if ($this->_hlp->getScopeConfig('udropship/vendor/is_restrict_udpo_status')) {
                $udpoStatuses = $this->_hlp->getScopeConfig('udropship/vendor/restrict_udpo_status');
                if (!is_array($udpoStatuses)) {
                    $udpoStatuses = explode(',', $udpoStatuses);
                }
            }

            if (!is_null($poStatus) && $poStatus!=='' && $poStatus!=$udpo->getUdropshipStatus()
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

            $comment = $r->getParam('comment');
            if ($comment || $partial=='inform' && $partialQty) {
                if ($partialQty) {
                    $comment .= "\n\nPartial Availability:\n";
                    foreach ($udpo->getAllItems() as $item) {
                        if (empty(@$partialQty[$item->getId()])) {
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
}

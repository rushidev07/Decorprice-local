<?php

namespace Unirgy\DropshipPo\Controller\Adminhtml\Order\Po;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Psr\Log\LoggerInterface;
use Unirgy\DropshipPo\Helper\Data as HelperData;
use Unirgy\DropshipPo\Model\PoFactory;
use Unirgy\DropshipPo\Model\Source;
use Unirgy\Dropship\Helper\Data as DropshipHelperData;
use Unirgy\Dropship\Helper\ProtectedCode;
use Zend\Json\Json;
use Magento\Framework\Exception\LocalizedException;

class AddComment extends AbstractPo
{
    public function execute()
    {
        try {
            $this->getRequest()->setParam(
                'udpo_id',
                $this->getRequest()->getParam('id')
            );
            $data = $this->getRequest()->getPost('comment');
            $udpo = $this->_initPo();
            if (empty($data['comment']) && $data['status']==$udpo->getUdropshipStatus()) {
                throw new LocalizedException(__('Comment text field cannot be empty.'));
            }
            /** @var \Magento\Backend\Model\Auth $auth */
            $auth = $this->_hlp->getObj('\Magento\Backend\Model\Auth');
            $adminUser = $auth->getUser();
            $isVendorNotified  = isset($data['is_vendor_notified']);
            $isVisibleToVendor = isset($data['is_vendor_notified']) || isset($data['is_visible_to_vendor']);
            
            $udpo->setUseCommentUsername($adminUser->getUsername());
            
            $hlp = $this->_hlp;
            $udpoHlp = $this->_poHlp;
            $poStatus = $data['status'];
            
            $poStatusShipped = Source::UDPO_STATUS_SHIPPED;
            $poStatusDelivered = Source::UDPO_STATUS_DELIVERED;
            $poStatusCanceled = Source::UDPO_STATUS_CANCELED;
            
            $poStatusSaveRes = true;
            if ($this->getRequest()->getParam('force_status_change_flag')) {
                $udpo->setForceStatusChangeFlag(true);
            }
            if ($poStatus!=$udpo->getUdropshipStatus()) {
                $oldStatus = $udpo->getUdropshipStatus();
                if ($oldStatus==$poStatusCanceled && !$udpo->getForceStatusChangeFlag()) {
                    throw new LocalizedException(__('Canceled purchase order cannot be reverted'));
                }
                if ($poStatus==$poStatusShipped || $poStatus==$poStatusDelivered) {
                    foreach ($udpo->getShipmentsCollection() as $_s) {
                        $hlp->completeShipment($_s, true, $poStatus==$poStatusDelivered);
                    }
                    if (isset($_s)) {
                        $hlp->completeOrderIfShipped($_s, true);
                    }
                    $poStatusSaveRes = $udpoHlp->processPoStatusSave($udpo, $poStatus, true, null, $data['comment'], $isVendorNotified, $isVisibleToVendor);
                } elseif ($poStatus == $poStatusCanceled) {
                    $udpo->setFullCancelFlag(isset($data['full_cancel']));
                    $udpo->setNonshippedCancelFlag(isset($data['nonshipped_cancel']));
                    $this->_poHlp->cancelPo($udpo, true);
                    $poStatusSaveRes = $udpoHlp->processPoStatusSave($udpo, $poStatus, true, null, $data['comment'], $isVendorNotified, $isVisibleToVendor);
                } else {
                    $poStatusSaveRes = $udpoHlp->processPoStatusSave($udpo, $poStatus, true, null, $data['comment'], $isVendorNotified, $isVisibleToVendor);
                }
            } else {
                $udpo->addComment($data['comment'], $isVendorNotified, $isVisibleToVendor);
                if (isset($data['is_vendor_notified'])) {
                    $this->_poHlp->sendPoCommentNotificationEmail($udpo, $data['comment']);
                }
                $udpo->saveComments();
            }

            if ($poStatus == $poStatusCanceled) {
            	if ($udpo->getCurrentlyCanceledQty()>0) {
                    $this->messageManager->addSuccess(__('%1 items were canceled', $udpo->getCurrentlyCanceledQty()));
            	} else {
                    $this->messageManager->addNotice(__('There were no items to cancel that match requested condition'));
            	}
            	if (!$poStatusSaveRes) {
                    $this->messageManager->addNotice(__('Status cannot be changed to canceled because po still have processing items'));
            	} else {
                    $this->messageManager->addNotice(__('Po Status changed to canceled'));
            	}
                $response = [
                    'ajaxExpired'  => true,
                    'ajaxRedirect' => $this->getUrl('*/*/view', ['udpo_id' => $this->getRequest()->getParam('udpo_id')])
                ];
            } elseif ($poStatusSaveRes) {
                /** @var \Magento\Framework\App\ViewInterface $view */
                $view = $this->_hlp->createObj('\Magento\Framework\App\ViewInterface');
                $view->loadLayout();
                $response = $view->getLayout()->getBlock('udpo_comments')->toHtml();
            } else {
                $response = [
                    'error'     => true,
                    'message'   => __('Cannot change status from %1 to %2', $this->_poHlp->getPoStatusName($udpo->getUdropshipStatus()), $this->_poHlp->getPoStatusName($poStatus))
                ];
            }
        } catch (LocalizedException $e) {
            $response = [
                'error'     => true,
                'message'   => $e->getMessage()
            ];
        } catch (\Exception $e) {
            $this->_logger->error($e);
            $response = [
                'error'     => true,
                'message'   => __('Cannot add new comment.')
            ];
        }
        if (is_array($response)) {
            $response = $this->_hlp->jsonEncode($response);
        }
        return $this->_resultRawFactory->create()->setContents($response);
    }
}

<?php

namespace Unirgy\DropshipPo\Controller\Adminhtml\Po;

class ResendUdpos extends AbstractPo
{
    public function execute(){
        $udpoIds = $this->getRequest()->getPost('udpo_ids');
        if (!empty($udpoIds)) {
            try {
                $udpos = $this->_hlp->createObj('\Unirgy\DropshipPo\Model\ResourceModel\Po\Collection')
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('entity_id', ['in' => $udpoIds])
                    ->load();

                foreach ($udpos as $udpo) {
                    $udpo->afterLoad();
                    $udpo->setResendNotificationFlag(true);
                    $vendor = $udpo->getVendor();
                    $method = $vendor->getNewOrderNotifications();
                    if ($method==-1 && $udpo->getData('is_vendor_notified')) {
                        $udpo->setResendNotificationFlag(1);
                        $this->_poHlp->sendNewPoNotificationEmail($udpo);
                    } else {
                        $this->_poHlp->sendVendorNotification($udpo);
                    }
                }

                $this->messageManager->addSuccess(__('%1 notifications sent.', count($udpoIds)));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_logger->error($e);
                $this->messageManager->addError(__('Cannot save shipment.'));
            }
        }
        return $this->_redirect('*/*/');
    }
}

<?php

namespace Unirgy\DropshipPo\Controller\Adminhtml\Po;

use Magento\Framework\Exception;
use Unirgy\DropshipPo\Model\Source;

class MassDelete extends AbstractPo
{
    public function execute(){
        $udpoHlp = $this->_poHlp;
        $udpoIds = $this->getRequest()->getPost('udpo_ids');
        if (!empty($udpoIds)) {
            try {
                $udpos = $this->_hlp->createObj('\Unirgy\DropshipPo\Model\ResourceModel\Po\Collection')
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('entity_id', ['in' => $udpoIds])
                    ->load();

                foreach ($udpos as $udpo) {
                    $udpo->afterLoad();
                    $udpo->setFullCancelFlag(1);
                    $udpo->setNonshippedCancelFlag(1);
                    $udpo->setForceStatusChangeFlag(1);
                    $udpo->setResendNotificationFlag(1);
                    $this->_poHlp->cancelPo($udpo, true);
                    $udpoHlp->processPoStatusSave($udpo, Source::UDPO_STATUS_CANCELED, true, null, null, false, false);
                    $this->_poHlp->sendPoDeleteVendorNotification($udpo);
                }

                foreach ($udpos as $udpo) {
                    foreach ($udpo->getShipmentsCollection() as $_shipment) {
                        $_shipment->delete();
                    }
                    $udpo->delete();
                }

                $this->messageManager->addSuccess(__('%1 POs deleted.', count($udpoIds)));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_logger->error($e);
                $this->messageManager->addError(__('Cannot save delete POs.'));
            }
        }
        return $this->_redirect('*/*/');
    }
}

<?php

namespace Unirgy\DropshipPo\Controller\Adminhtml\Order\Po;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception;
use Magento\Framework\Registry;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Psr\Log\LoggerInterface;
use Unirgy\DropshipPo\Helper\Data as HelperData;
use Unirgy\DropshipPo\Model\PoFactory;
use Unirgy\DropshipPo\Model\Source;
use Unirgy\Dropship\Helper\Data as DropshipHelperData;
use Unirgy\Dropship\Helper\ProtectedCode;

class Delete extends AbstractPo
{
    public function execute()
    {
        $hlp = $this->_hlp;
        $udpoHlp = $this->_poHlp;
        $data = $this->getRequest()->getPost('udpo');

        if ($udpo = $this->_initPo(false)) {
            try {
                $udpo->afterLoad();
                $udpo->setFullCancelFlag(1);
                $udpo->setNonshippedCancelFlag(1);
                $udpo->setForceStatusChangeFlag(1);
                $udpo->setResendNotificationFlag(1);
                $this->_poHlp->cancelPo($udpo, true);
                $udpoHlp->processPoStatusSave($udpo, Source::UDPO_STATUS_CANCELED, true, null, null, false, false);
                $this->_poHlp->sendPoDeleteVendorNotification($udpo);
                foreach ($udpo->getShipmentsCollection() as $_shipment) {
                    $_shipment->delete();
                }
                $udpo->delete();

                $this->messageManager->addSuccess(__('PO was successfully deleted'));

            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_logger->error($e);
                $this->messageManager->addError(__('Cannot delete PO.'));
            }
            return $this->_redirect('*/po');
        } else {
            return $this->_forward('noRoute');
        }
    }
}

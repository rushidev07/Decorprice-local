<?php

namespace Unirgy\DropshipPo\Controller\Adminhtml\Order\Po;

class UpdateQty extends AbstractPo
{
    public function execute()
    {
        try {
            $this->_initOrder();
            $this->_view->loadLayout();
            $response = $this->_view->getLayout()->getBlock('order_items')->toHtml();
            if ($this->_hlp->isUdmultiActive()) {
                $response .= $this->_view->getLayout()->getBlock('udmulti_po_js')->toHtml();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
        	$this->_logger->error($e);
            $response = [
                'error'     => true,
                'message'   => $e->getMessage()
            ];
            $response = $this->_hlp->jsonEncode($response);
        } catch (\Exception $e) {
        	$this->_logger->error($e);
            $response = [
                'error'     => true,
                'message'   => __('Cannot update item quantity.')
            ];
            $response = $this->_hlp->jsonEncode($response);
        }
        return $this->_resultRawFactory->create()->setContents($response);
    }
}

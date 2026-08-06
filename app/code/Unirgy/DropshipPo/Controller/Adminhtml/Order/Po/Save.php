<?php

namespace Unirgy\DropshipPo\Controller\Adminhtml\Order\Po;

use Magento\Framework\Exception;

class Save extends AbstractPo
{
    public function execute()
    {
        $data = $this->getRequest()->getPost('udpo');
        if (!empty($data['comment_text'])) {
            $this->_session->setCommentText($data['comment_text']);
        }

        try {
            if ($order = $this->_initOrder()) {
                /** @var \Magento\Backend\Model\Auth $auth */
                $auth = $this->_hlp->getObj('\Magento\Backend\Model\Auth');
                $adminUser = $auth->getUser();
                $order->setUdpoNoSplitPoFlag(true);
                $order->setSkipLockedCheckFlag(true);
                $order->setIsManualPoFlag(true);
                $posCreated = $this->_poHlp->splitOrderToPos($order, $this->_getItemQtys(), isset($data['comment_vendor_notify']) ? $data['comment_text'] : '');
                $this->messageManager->addSuccess(__('Created %1 Purchase Orders', $posCreated));
                if (!empty($data['comment_text'])) {
                    $this->_poHlp->initOrderUdposCollection($order, true);
                    $commentVisibleToVendor = isset($data['comment_vendor_notify']) || isset($data['comment_visible_to_vendor']);
                    foreach ($order->getLastCreatedUdpos() as $_po) {
                        $_po->setUseCommentUsername($adminUser->getUsername());
                        $_po->addComment(
                            $data['comment_text'],
                            isset($data['comment_vendor_notify']),
                            $commentVisibleToVendor
                        );
                        $_po->saveComments();
                    }
                }
                $this->_session->getCommentText(true);
                return $this->_redirect('sales/order/view', ['order_id' => $order->getId()]);
            } else {
                return $this->_forward('noRoute');
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_logger->error($e);
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_logger->error($e);
            $this->messageManager->addError(__('Cannot save po.'));
        }
        return $this->_redirect('*/*/new', ['order_id' => $this->getRequest()->getParam('order_id')]);
    }
}

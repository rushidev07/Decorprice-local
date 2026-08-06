<?php

namespace Unirgy\DropshipPo\Controller\Adminhtml\Order\Po;

class SaveCosts extends AbstractPo
{
    public function execute()
    {
        $data = $this->getRequest()->getPost('udpo');

        if ($po = $this->_initPo(false)) {
            /** @var \Magento\Backend\Model\Auth $auth */
            $auth = $this->_hlp->getObj('\Magento\Backend\Model\Auth');
            $adminUser = $auth->getUser();
            try {
                $order = $po->getOrder();
                $_orderRate = $order->getBaseToOrderRate() > 0 ? $order->getBaseToOrderRate() : 1;

                if (isset($data['shipping_amount'])) {
                    $po->setData('base_shipping_amount', $data['shipping_amount']);
                    $po->setData('shipping_amount', $data['shipping_amount']*$_orderRate);
                }

                if (is_array($data['costs'])) {
                    $costsDiff = 0;
                    foreach ($data['costs'] as $itemId => $itemCost) {
                        if (($item = $po->getItemById($itemId))) {
                            $costsDiff += ($itemCost-$item->getBaseCost())*$item->getQty();
                            $item->setBaseCost($itemCost);
                        }
                    }
                    $po->setTotalCost($po->getTotalCost()+$costsDiff);
                }

                $po->save();

                foreach ($po->getShipmentsCollection() as $shipment) {
                    if (is_array($data['costs'])) {
                        $costsDiff = 0;
                        foreach ($data['costs'] as $itemId => $itemCost) {
                            if (($item = $this->getItemByColumnValue($shipment->getItemsCollection(), 'udpo_item_id', $itemId))) {
                                $costsDiff += ($itemCost-$item->getBaseCost())*$item->getQty();
                                $item->setBaseCost($itemCost);
                            }
                        }
                        $shipment->setTotalCost($shipment->getTotalCost()+$costsDiff);
                        $shipment->save();
                    }
                }

                $commentVisibleToVendor = isset($data['comment_vendor_notify']) || isset($data['comment_visible_to_vendor']);
                $po->setUseCommentUsername($adminUser->getUsername());
                $po->addComment(
                    $data['comment_text'],
                    isset($data['comment_vendor_notify']),
                    $commentVisibleToVendor
                );
                $po->saveComments();
                if (isset($data['comment_vendor_notify'])) {
                    $this->_poHlp->sendPoCommentNotificationEmail($po, $data['comment_text']);
                }

                $this->messageManager->addSuccess(__('Costs were successfully updated'));

            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_logger->error($e);
                $this->messageManager->addError(__('Cannot save shipment.'));
            }
            return $this->_redirect('*/*/view', ['udpo_id' => $po->getId()]);
        } else {
            return $this->_forward('noRoute');
        }
    }
    protected function getItemByColumnValue($items, $column, $value)
    {
        foreach ($items as $item) {
            if ($item->getData($column) == $value) {
                return $item;
            }
        }
        return null;
    }
}

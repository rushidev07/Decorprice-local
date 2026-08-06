<?php

namespace Unirgy\DropshipPo\Controller\Adminhtml\Order\Po;

use Unirgy\DropshipPo\Model\Source;
use Unirgy\Dropship\Model\Label\BatchFactory;

class SaveShipment extends AbstractPo
{
    /**
     * @var BatchFactory
     */
    protected $_labelBatchFactory;

    public function __construct(
        BatchFactory $labelBatchFactory,
        \Magento\Backend\App\Action\Context $context,
        \Unirgy\DropshipPo\Model\PoFactory $poFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Unirgy\DropshipPo\Helper\Data $udpoHelper,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $shipmentTrackFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Unirgy\Dropship\Helper\Data $udropshipHelper,
        \Unirgy\Dropship\Helper\ProtectedCode $udropshipHelperProtected,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_labelBatchFactory = $labelBatchFactory;

        parent::__construct($context, $poFactory, $coreRegistry, $udpoHelper, $shipmentTrackFactory, $orderFactory, $udropshipHelper, $udropshipHelperProtected, $resultForwardFactory, $resultPageFactory, $resultRedirectFactory, $resultRawFactory, $logger);
    }

	public function execute()
    {
        $data = $this->getRequest()->getPost('shipment');
        if (!empty($data['comment_text'])) {
            $this->_session->setCommentText($data['comment_text']);
        }

        $hlp = $this->_hlp;
        $udpoHlp = $this->_poHlp;
        
        try {
        	$session = $this->_getSession();
        	$udpo = $this->_initPo();
        	$order = $udpo->getOrder();
        	if (isset($data['use_label_shipping_amount'])) {
                $udpo->setUseLabelShippingAmount(true);
            } elseif ($data['shipping_amount']) {
                $udpo->setShipmentShippingAmount($data['shipping_amount']);
            }
            if ($shipment = $this->_initShipment($udpo, true)) {

                $comment = '';
                if (!empty($data['comment_text'])) {
                    $this->_hlp->addShipmentComment(
                        $shipment,
                        $data['comment_text'], true, false, isset($data['comment_customer_notify'])
                    );
                    if (!empty($data['comment_customer_notify'])) {
                        $comment = $data['comment_text'];
                    }
                }

                if (!empty($data['send_email'])) {
                    $shipment->setEmailSent(true);
                }

                $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                $this->_eventManager->dispatch('udpo_po_shipment_save_before', ['order'=>$order, 'udpo'=>$udpo, 'shipments'=>[$shipment]]);

		        $transaction = $this->_hlp->transactionFactory()->create();
		        $order->getShipmentsCollection()->addItem($shipment);
		        $udpo->getShipmentsCollection()->addItem($shipment);
		        $transaction->addObject($shipment);
		        $shipment->setNoInvoiceFlag(true);
                /** @var \Magento\Sales\Model\Order\Item $__oi */
                foreach ($order->getAllItems() as $__oi) {
                    $__oi->setProductOptions($__oi->getProductOptions());
                }
		        $transaction->addObject($order->setIsInProcess(true))->addObject($udpo->setData('___dummy',1))->save();
		        
		        $this->_eventManager->dispatch('udpo_po_shipment_save_after', ['order'=>$order, 'udpo'=>$udpo, 'shipments'=>[$shipment]]);
        
                if (!empty($data['send_email'])) {
                    /** @var \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender */
                    $shipmentSender = $this->_hlp->getObj('\Magento\Sales\Model\Order\Email\Sender\ShipmentSender');
                    $shipment->setCustomerNoteNotify(true);
                    $shipment->setCustomerNote($comment);
                    $shipmentSender->send($shipment);
                }
                $this->messageManager->addSuccess(__('The shipment has been created.'));
                $this->_session->getCommentText(true);
                
             	if (!empty($data['generate_label'])) {
                    $labelData = [];
                    foreach (['weight','value','length','width','height','reference','package_count'] as $_glKey) {
                        if (isset($data['label_info'][$_glKey])) {
                            $labelData[$_glKey] = $data['label_info'][$_glKey];
                        }
                    }

                    $oldUdropshipMethod = $shipment->getUdropshipMethod();
                    $oldUdropshipMethodDesc = $shipment->getUdropshipMethodDescription();
                    if (isset($data['label_info']['use_method_code'])) {
                        list($useCarrier, $useMethod) = explode('_', $data['label_info']['use_method_code'], 2);
                        if (!empty($useCarrier) && !empty($useMethod)) {
                            $shipment->setUdropshipMethod($data['label_info']['use_method_code']);
                            $carrierMethods = $this->_hlp->getCarrierMethods($useCarrier);
                            $shipment->setUdropshipMethodDescription(
                                $this->_hlp->getScopeConfig('carriers/'.$useCarrier.'/title', $shipment->getOrder()->getStoreId())
                                .' - '.$carrierMethods[$useMethod]
                            );
                        }
                    }
	
	                // generate label
                    try {
                        $batch = $this->_labelBatchFactory->create()
                            ->setVendor($udpo->getVendor())
                            ->processShipments([$shipment], $labelData, ['mark_shipped'=>!empty($data['mark_as_shipped'])]);
                    } catch (\Exception $e) {
                        if (isset($data['label_info']['use_method_code'])) {
                            $shipment->setUdropshipMethod($oldUdropshipMethod);
                            $shipment->setUdropshipMethodDescription($oldUdropshipMethodDesc);
                            $shipment->getResource()->saveAttribute($shipment, 'udropship_method');
                            $shipment->getResource()->saveAttribute($shipment, 'udropship_method_description');
                        }
                        throw $e;
                    }
	
	                if (empty($data['mark_as_shipped'])) {
	                	$udpoHlp->processPoStatusSave($udpo, Source::UDPO_STATUS_READY, true);
	                }
	                    
	                // if batch of 1 label is successfull
	                if ($batch->getShipmentCnt() && $batch->getLastTrack()) {
                        $this->messageManager->addSuccess('Label was succesfully created');
	                } else {
	                    if ($batch->getErrors()) {
	                        foreach ($batch->getErrors() as $error=>$cnt) {
                                $this->messageManager->addError(__($error, $cnt));
	                        }
	                    }
	                }
             	} elseif (!empty($data['mark_as_shipped'])) {
             		$hlp->completeShipment($shipment, true);
             		$hlp->completeUdpoIfShipped($shipment, true);
            		$hlp->completeOrderIfShipped($shipment, true);
             	} else {
             		$udpoHlp->processPoStatusSave($udpo, Source::UDPO_STATUS_READY, true);
             	}
             	
            	if (!empty($data['do_invoice'])) {
            		$shipment->setNoInvoiceFlag(false);
		        	$shipment->setDoInvoiceFlag(true);
		        	if ($this->_poHlp->invoiceShipment($shipment)) {
                        $this->messageManager->addSuccess(__('Shipment was succesfully invoiced'));
		        	} else {
                        $this->messageManager->addError(__('Shipment was not invoiced'));
		        	}
		        }
                
                return $this->_redirect('*/*/view', ['udpo_id' => $shipment->getUdpoId()]);
            } else {
                $this->messageManager->addError(__('Cannot initialize shipment.'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
        	$this->_logger->error($e);
            $this->messageManager->addError(__('Cannot save shipment.'));
        }
        return $this->_redirect('*/*/newShipment', ['udpo_id' => $this->getRequest()->getParam('udpo_id')]);
    }
}

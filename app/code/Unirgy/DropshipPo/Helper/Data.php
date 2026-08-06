<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_DropshipPo
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\DropshipPo\Helper;

use Magento\Backend\Model\UrlFactory;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Model\Locale;
use Magento\Framework\Registry;
use Magento\Sales\Model\Convert\OrderFactory;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Unirgy\DropshipPo\Model\Pdf\PoFactory as PdfPoFactory;
use Unirgy\DropshipPo\Model\PoFactory;
use Unirgy\DropshipPo\Model\Po\ItemFactory;
use Unirgy\DropshipPo\Model\ResourceModel\Po\Collection;
use Unirgy\DropshipPo\Model\Source;
use Unirgy\Dropship\Helper\Data as HelperData;
use Unirgy\Dropship\Helper\Error;
use Unirgy\Dropship\Model\Label\BatchFactory;
use Unirgy\Dropship\Model\Source as UdropshipSource;

class Data extends AbstractHelper
{
    /**
     * @var HelperData
     */
    protected $_hlp;

    /**
     * @var OrderFactory
     */
    protected $_convertOrderFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var PoFactory
     */
    protected $_poFactory;

    /**
     * @var ItemFactory
     */
    protected $_poItemFactory;

    /**
     * @var Collection
     */
    protected $_poCollection;

    /**
     * @var PdfPoFactory
     */
    protected $_pdfPoFactory;

    /**
     * @var BatchFactory
     */
    protected $_labelBatchFactory;

    /**
     * @var Error
     */
    protected $_helperError;

    /**
     * @var UrlFactory
     */
    protected $_backendUrlFactory;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    protected $inlineTranslation;
    protected $_transportBuilder;

    public function __construct(
        \Unirgy\Dropship\Model\EmailTransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        Context $context,
        HelperData $helperData,
        OrderFactory $convertOrderFactory,
        StoreManagerInterface $modelStoreManagerInterface,
        PoFactory $modelPoFactory,
        ItemFactory $poItemFactory,
        Collection $poCollection,
        PdfPoFactory $pdfPoFactory,
        BatchFactory $labelBatchFactory,
        Error $helperError,
        UrlFactory $modelUrlFactory,
        Registry $frameworkRegistry,
        \Magento\Framework\DataObject\Copy $objectCopyService
    )
    {
        $this->_transportBuilder = $transportBuilder;
        $this->_transportBuilder->udReset();
        $this->inlineTranslation = $inlineTranslation;
        $this->_hlp = $helperData;
        $this->_convertOrderFactory = $convertOrderFactory;
        $this->_storeManager = $modelStoreManagerInterface;
        $this->_poFactory = $modelPoFactory;
        $this->_poItemFactory = $poItemFactory;
        $this->_poCollection = $poCollection;
        $this->_pdfPoFactory = $pdfPoFactory;
        $this->_labelBatchFactory = $labelBatchFactory;
        $this->_helperError = $helperError;
        $this->_backendUrlFactory = $modelUrlFactory;
        $this->_coreRegistry = $frameworkRegistry;
        $this->_objectCopyService = $objectCopyService;

        parent::__construct($context);
    }

    public function isActive()
    {
        return true;
    }

    protected function _processObjectSave($save, $object)
    {
        if ($object instanceof \Magento\Sales\Model\Order\Item) {
            $object->setProductOptions($object->getProductOptions());
        }
        if ($save===true) {
            $object->save();
        } elseif ($save instanceof \Magento\Framework\DB\Transaction) {
            $save->addObject($object);
        }
    }

    public function registerShipmentItem($item, $save)
    {
        $item->register();
        $this->_processObjectSave($save, $item);
        $poItem = $this->getShipmentPoItem($item);
        if ($poItem->getId()) {
            $poItem->setQtyShipped(
                $poItem->getQtyShipped()+$item->getQty()
            );
            $this->_processObjectSave($save, $poItem);
        }
    }

    public function revertCompleteShipment($shipment, $save)
    {
        foreach ($shipment->getAllItems() as $sItem) {
            $sItem->setQtyShipped(0);
            $this->_processObjectSave($save, $sItem);
        }
    }

    public function completeShipmentItem($item, $save)
    {
        $item->setQtyShipped(
            $item->getQtyShipped()+$this->getShipmentItemQtyToShip($item)
        );
        $this->_processObjectSave($save, $item);
    }

    public function completeUdpoIfShipped($shipment, $save=false, $force=true)
    {
        if (($po = $this->getShipmentPo($shipment))) {
            return $this->processPoStatusSave($po, Source::UDPO_STATUS_DELIVERED, $save)
                || $this->processPoStatusSave($po, Source::UDPO_STATUS_SHIPPED, $save)
                || $this->processPoStatusSave($po, Source::UDPO_STATUS_PARTIAL, $save);
        }
        return false;
    }

    public function splitOrderToPos($order, $qtys=[], $comment='')
    {
        return $this->_hlp->getObj('\Unirgy\DropshipPo\Helper\ProtectedCode')->splitOrderToPos($order, $qtys, $comment);
    }

    public function sendVendorNotification($po, $comment='')
    {
        try {
            $this->_sendVendorNotification($po, $comment);
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
    }
    protected function _sendVendorNotification($po, $comment='')
    {
        $vendor = $po->getVendor();
        $method = $vendor->getNewOrderNotifications();

        if (!$method || $method=='0') {
            return $this;
        }

        $data = compact('vendor', 'po', 'method');
        if ($method=='1') {
            $this->sendNewPoNotificationEmail($po, $comment);
        } else {
            $config = $this->_hlp->config()->getNotificationMethod($method);
            if ($config) {
                $cb = explode('::', (string)$config['callback']);
                $obj = ObjectManager::getInstance()->get($cb[0]);
                $method = $cb[1];
                $obj->$method($data);
            }
        }
        $this->_eventManager->dispatch('udpo_send_vendor_notification', $data);

        return $this;
    }

    public $createReturnAllShipments=false;
    public function createShipmentFromPo($udpo, $qtys=[], $save=true, $setQtyShippedFlag=true, $noInvoiceFlag=false)
    {
        if (!$this->_hlp->isActive($udpo->getOrder()->getStore())) {
            return false;
        }

        $order = $udpo->getOrder();
        $hlp = $this->_hlp;
        $convertor = $this->_convertOrderFactory->create();

        $store = $this->_storeManager->getStore($order->getStoreId());

        $items = $udpo->getAllItems();

        $orderToPoItemMap = [];
        foreach ($items as $poItem) {
            $orderToPoItemMap[$poItem->getOrderItemId()] = $poItem;
        }

        $shipmentIncrement = $this->scopeConfig->getValue('udropship/purchase_order/shipment_increment_type', ScopeInterface::SCOPE_STORE, $order->getStoreId());

        $orderToShipItemMap = [];

        $shipments = [];
        $canShipItemFlags = [];
        foreach ($items as $poItem) {
            $orderItem = $poItem->getOrderItem();
            $canShipItemFlags[$poItem->getId()] = $this->_canShipItem($orderItem, $poItem, $orderToPoItemMap, $qtys);
        }
        foreach ($items as $poItem) {
            $orderItem = $poItem->getOrderItem();

            if (!$canShipItemFlags[$poItem->getId()]) {
                continue;
            }

            $vId = $udpo->getUdropshipVendor();
            $vendor = $hlp->getVendor($vId);

            $vIds = [];
            if ($orderItem->getHasChildren()) {
                $children = $orderItem->getChildrenItems() ? $orderItem->getChildrenItems() : $orderItem->getChildren();
                foreach ($children as $child) {
                    if (!isset($orderToPoItemMap[$child->getId()]) || !$canShipItemFlags[$orderToPoItemMap[$child->getId()]->getId()]) continue;
                    $udpoKey = $vId;
                    if (!$udpo->getUdpoNoSplitPoFlag()) {
                    if ($this->_hlp->isSeparateShipment($child, $vId) && $orderItem->isShipSeparately()) {
                        $udpoKey .= '-'.($child->getUdpoSeqNumber() ? $child->getUdpoSeqNumber() : $child->getId());
                    } elseif ($this->_hlp->isSeparateShipment($orderItem, $vId)) {
                        $udpoKey .= '-'.($orderItem->getUdpoSeqNumber() ? $orderItem->getUdpoSeqNumber() : $orderItem->getId());
                    }}
                    $vIds[$udpoKey] = $vId;
                }
                if (empty($vIds)) {
                    $udpoKey = $vId;
                    $vIds[$udpoKey] = $vId;
                }
            } else {
                $udpoKey = $vId;
                $oiParent = $orderItem->getParentItem();
                if (!$udpo->getUdpoNoSplitPoFlag()) {
                if ($this->_hlp->isSeparateShipment($orderItem, $vId)
                    && (!$oiParent || $oiParent->isShipSeparately())
                ) {
                    $udpoKey .= '-'.($orderItem->getUdpoSeqNumber() ? $orderItem->getUdpoSeqNumber() : $orderItem->getId());
                } elseif ($oiParent && $this->_hlp->isSeparateShipment($oiParent, $vId)) {
                    $udpoKey .= '-'.($oiParent->getUdpoSeqNumber() ? $oiParent->getUdpoSeqNumber() : $oiParent->getId());
                }}
                $vIds[$udpoKey] = $vId;
            }

            foreach ($vIds as $udpoKey=>$vId) {
            $vendor = $hlp->getVendor($vId);

            if (empty($shipments[$udpoKey])) {
                $shipmentStatus = (int)$this->scopeConfig->getValue('udropship/vendor/default_shipment_status', ScopeInterface::SCOPE_STORE, $order->getStoreId());
                if ('999' != $vendor->getData('initial_shipment_status')) {
                    $shipmentStatus = $vendor->getData('initial_shipment_status');
                }
                $shipments[$udpoKey] = $convertor->toShipment($order)
                    ->setUdpo($udpo)
                    ->setUdpoId($udpo->getId())
                    ->setUdpoIncrementId($udpo->getIncrementId())
                    ->setUdropshipVendor($vId)
                    ->setUdropshipStatus($shipmentStatus)
                    ->setTotalQty(0)
                    ->setShippingAmount(0)
                    ->setBaseShippingAmount(0)
                    ->setShippingAmountIncl(0)
                    ->setBaseShippingAmountIncl(0)
                    ->setShippingTax(0)
                    ->setBaseShippingTax(0);

                if ($udpo->getSourceCode()) {
                    $shipments[$udpoKey]->getExtensionAttributes()->setSourceCode(
                        $udpo->getSourceCode()
                    );
                }

                if ($shipmentIncrement == Source::SHIPMENT_INCREMENT_ORDER_BASED
                    || $shipmentIncrement == Source::SHIPMENT_INCREMENT_PO_BASED
                ) {
                    $shipments[$udpoKey]->setIncrementId(
                        $this->_getNextShipmentIncrementId($order, $udpo, $shipments, $udpoKey)
                    );
                }

                $_orderRate = $udpo->getOrder()->getBaseToOrderRate() > 0 ? $udpo->getOrder()->getBaseToOrderRate() : 1;
                $_baseSa = $udpo->hasShipmentShippingAmount() ? $udpo->getShipmentShippingAmount() : $udpo->getBaseShippingAmountLeft();
                $_sa = $this->_hlp->roundPrice($_orderRate*$_baseSa);
                $shipments[$udpoKey]
                    ->setShippingAmount($_sa)
                    ->setBaseShippingAmount($_baseSa)
                    ->setShippingAmountIncl($udpo->getShippingAmountIncl())
                    ->setBaseShippingAmountIncl($udpo->getBaseShippingAmountIncl())
                    ->setShippingTax($udpo->getShippingTax())
                    ->setBaseShippingTax($udpo->getBaseShippingTax())
                    ->setUdropshipMethod($udpo->getUdropshipMethod())
                    ->setUdropshipMethodDescription($udpo->getUdropshipMethodDescription())
                ;
            }
            if ($orderItem->isDummy(true)) {
                if ($orderItem->getParentItem()) {
                    $qty = $orderItem->getQtyOrdered()/$orderItem->getParentItem()->getQtyOrdered();
                } else {
                    $qty = 1;
                }
            } else {
                if (isset($qtys[$poItem->getId()])) {
                    $qty = $qtys[$poItem->getId()];
                } else {
                    $qty = $poItem->getQtyToShip();
                }
            }

            $item = $convertor->itemToShipmentItem($orderItem)->setUdpoItem($poItem)->setUdpoItemId($poItem->getId());

            $orderToShipItemMap[$orderItem->getId().'-'.$vId] = $item;

            $_totQty = $qty;
            if (($_parentItem = $orderItem->getParentItem())
                && isset($orderToShipItemMap[$_parentItem->getId().'-'.$vId])
            ) {
                $_totQty *= $orderToShipItemMap[$_parentItem->getId().'-'.$vId]->getQty();
            }

            $this->setShipmentItemQty($item, $poItem, $_totQty);

            if (!$orderItem->getHasChildren()
                || $orderItem->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
            ) {
                if (abs($poItem->getBaseCost())<0.001) {
                    $item->setBaseCost($orderItem->getBasePrice());
                } else {
                    $item->setBaseCost($poItem->getBaseCost());
                }
            }

            //$item->register();
            if ($setQtyShippedFlag) {
                $poItem->setQtyShipped(
                    $poItem->getQtyShipped()+$item->getQty()
                );
                $orderItem->setQtyShipped(
                    $orderItem->getQtyShipped()+$item->getQty()
                );
            }

            $shipments[$udpoKey]->addItem($item);
            if (!$orderItem->isDummy(true)) {
                $qtyOrdered = $orderItem->getQtyOrdered();
                $_rowDivider = $_totQty/($qtyOrdered>0 ? $qtyOrdered : 1);
                $_rowDivider = $_rowDivider>0 ? $_rowDivider : 1;
                $iHiddenTax = $this->_hlp->roundPrice($orderItem->getBaseHiddenTaxAmount()*$_rowDivider);
                $iTax = $this->_hlp->roundPrice($orderItem->getBaseTaxAmount()*$_rowDivider);
                $iDiscount = $this->_hlp->roundPrice($orderItem->getBaseDiscountAmount()*$_rowDivider);
                $iBaseTotal = $this->_hlp->roundPrice($orderItem->getBaseRowTotal()*$_rowDivider);
                $iTotal = $this->_hlp->roundPrice($orderItem->getRowTotal()*$_rowDivider);
                if ($order->getData('ud_amount_fields') && $qtyOrdered==$orderItem->getQtyShipped()) {
                    if ($orderItem->getBaseHiddenTaxAmount()>$orderItem->getUdBaseHiddenTaxAmount()) {
                        $iHiddenTax = $orderItem->getBaseHiddenTaxAmount()-$orderItem->getUdBaseHiddenTaxAmount();
                    }
                    if ($orderItem->getBaseTaxAmount()>$orderItem->getUdBaseTaxAmount()) {
                        $iTax = $orderItem->getBaseTaxAmount()-$orderItem->getUdBaseTaxAmount();
                    }
                    if ($orderItem->getBaseDiscountAmount()>$orderItem->getUdBaseDiscountAmount()) {
                        $iDiscount = $orderItem->getBaseDiscountAmount()-$orderItem->getUdBaseDiscountAmount();
                    }
                    if ($orderItem->getBaseRowTotal()>$orderItem->getUdBaseRowTotal()) {
                        $iBaseTotal = $orderItem->getBaseRowTotal()-$orderItem->getUdBaseRowTotal();
                    }
                    if ($orderItem->getRowTotal()>$orderItem->getUdRowTotal()) {
                        $iTotal = $orderItem->getRowTotal()-$orderItem->getUdRowTotal();
                    }
                }

                $item->setBaseHiddenTaxAmount($iHiddenTax);
                $item->setBaseTaxAmount($iTax);
                $item->setBaseDiscountAmount($iDiscount);
                $item->setBaseRowTotal($iBaseTotal);
                $item->setRowTotal($iTotal);

                $orderItem->setUdBaseHiddenTaxAmount($orderItem->getUdBaseHiddenTaxAmount()+$iHiddenTax);
                $orderItem->setUdBaseTaxAmount($orderItem->getUdBaseTaxAmount()+$iTax);
                $orderItem->setUdBaseDiscountAmount($orderItem->getUdBaseDiscountAmount()+$iDiscount);
                $orderItem->setUdBaseRowTotal($orderItem->getUdBaseRowTotal()+$iBaseTotal);
                $orderItem->setUdRowTotal($orderItem->getUdRowTotal()+$iTotal);

                $shipments[$udpoKey]
                    ->setBaseHiddenTaxAmount($shipments[$udpoKey]->getBaseHiddenTaxAmount()+$iHiddenTax)
                    ->setBaseTaxAmount($shipments[$udpoKey]->getBaseTaxAmount()+$iTax)
                    ->setBaseDiscountAmount($shipments[$udpoKey]->getBaseDiscountAmount()+$iDiscount)
                    ->setBaseTotalValue($shipments[$udpoKey]->getBaseTotalValue()+$iBaseTotal)
                    ->setTotalValue($shipments[$udpoKey]->getTotalValue()+$iTotal)
                    ->setTotalQty($shipments[$udpoKey]->getTotalQty()+$qty)
                ;
            }
            if ($orderItem->getParentItem()) {
                $weightType = $orderItem->getParentItem()->getProductOptionByCode('weight_type');
                if (null !== $weightType && !$weightType) {
                    $shipments[$udpoKey]->setTotalWeight($shipments[$udpoKey]->getTotalWeight()+$orderItem->getWeight()*$_totQty);
                }
            } else {
                $weightType = $orderItem->getProductOptionByCode('weight_type');
                if (null === $weightType || $weightType) {
                    $shipments[$udpoKey]->setTotalWeight($shipments[$udpoKey]->getTotalWeight()+$orderItem->getWeight()*$_totQty);
                }
            }
            if (!$orderItem->getHasChildren()) {
                $shipments[$udpoKey]->setTotalCost(
                    $shipments[$udpoKey]->getTotalCost()+$item->getBaseCost()*$_totQty
                );
            }
            $shipments[$udpoKey]->setCommissionPercent($vendor->getCommissionPercent());
            $shipments[$udpoKey]->setTransactionFee($vendor->getTransactionFee());
            }
        }

        if (!$save) {
            reset($shipments);
            return count($shipments)>0 ? ($this->createReturnAllShipments ? $shipments : current($shipments)) : false;
        }

        if (empty($shipments)) return false;

        $this->_eventManager->dispatch('udpo_po_shipment_save_before', ['order'=>$order, 'udpo'=>$udpo, 'shipments'=>$shipments]);

        $udpoSplitWeights = [];
        foreach ($shipments as $_vUdpoKey => $_vUdpo) {
            if (empty($udpoSplitWeights[$_vUdpo->getUdropshipVendor().'-'])) {
                $udpoSplitWeights[$_vUdpo->getUdropshipVendor().'-']['weights'] = [];
                $udpoSplitWeights[$_vUdpo->getUdropshipVendor().'-']['total_weight'] = 0;
            }
            $weight = $_vUdpo->getTotalWeight()>0 ? $_vUdpo->getTotalWeight() : .001;
            $udpoSplitWeights[$_vUdpo->getUdropshipVendor().'-']['weights'][$_vUdpoKey] = $weight;
            $udpoSplitWeights[$_vUdpo->getUdropshipVendor().'-']['total_weight'] += $weight;
        }

        $transaction = $this->_hlp->transactionFactory()->create();
        foreach ($shipments as $shipment) {
            $this->_hlp->addVendorSkus($shipment);
            $shipment->setNoInvoiceFlag($noInvoiceFlag);
            if (empty($udpoNoSplitWeights[$shipment->getUdropshipVendor().'-'])
                && !empty($udpoSplitWeights[$shipment->getUdropshipVendor().'-']['weights'][$udpoKey])
                && count($udpoSplitWeights[$shipment->getUdropshipVendor().'-']['weights'])>1
            ) {
                $_splitWeight = $udpoSplitWeights[$shipment->getUdropshipVendor().'-']['weights'][$udpoKey];
                $_totalWeight = $udpoSplitWeights[$shipment->getUdropshipVendor().'-']['total_weight'];
                $shipment->setShippingAmount($shipment->getShippingAmount()*$_splitWeight/$_totalWeight);
                $shipment->setBaseShippingAmount($shipment->getBaseShippingAmount()*$_splitWeight/$_totalWeight);
                $shipment->setShippingAmountIncl($shipment->getShippingAmountIncl()*$_splitWeight/$_totalWeight);
                $shipment->setBaseShippingAmountIncl($shipment->getBaseShippingAmountIncl()*$_splitWeight/$_totalWeight);
                $shipment->setShippingTax($shipment->getShippingTax()*$_splitWeight/$_totalWeight);
                $shipment->setBaseShippingTax($shipment->getBaseShippingTax()*$_splitWeight/$_totalWeight);
            }
            $order->getShipmentsCollection()->addItem($shipment);
            $udpo->getShipmentsCollection()->addItem($shipment);
            $transaction->addObject($shipment);
        }
        /** @var \Magento\Sales\Model\Order\Item $__oi */
        foreach ($order->getAllItems() as $__oi) {
            $__oi->setProductOptions($__oi->getProductOptions());
        }
        $transaction->addObject($order->setIsInProcess(true))->addObject($udpo->setData('___dummy',1))->save();

        $shipped = count($shipments);
        foreach ($shipments as $shipment) {
            $shipment->setOrigData('entity_id', $shipment->getData('entity_id'));
            /** @var \Magento\Sales\Model\Order\Shipment $shipment */
            $shipment->unsetData(\Magento\Sales\Api\Data\ShipmentInterface::TRACKS);
            if (!in_array($shipment->getUdropshipStatus(), [UdropshipSource::SHIPMENT_STATUS_SHIPPED,UdropshipSource::SHIPMENT_STATUS_DELIVERED])) {
                $shipped = false;
                break;
            }
        }
        if ($shipped) {
            foreach ($shipments as $shipment) {
                $this->completeUdpoIfShipped($shipment, true);
                break;
            }
        } else {
            $this->processPoStatusSave($udpo, Source::UDPO_STATUS_READY, true);
        }

        $this->_eventManager->dispatch('udpo_po_shipment_save_after', ['order'=>$order, 'udpo'=>$udpo, 'shipments'=>$shipments]);

        /* no need to send notification because shipments created by vendor
        // send vendor notifications
        foreach ($shipments as $shipment) {
            $hlp->sendVendorNotification($shipment);
        }

        $hlp->processQueue();
        */

        reset($shipments);

        return count($shipments)>0 ? ($this->createReturnAllShipments ? $shipments : current($shipments)) : false;
    }

    protected function _getNextShipmentIncrementId($order, $udpo, $extraShipments, $skipKey)
    {
        $shipmentIncrement = $this->scopeConfig->getValue('udropship/purchase_order/shipment_increment_type', ScopeInterface::SCOPE_STORE, $order->getStoreId());

        if ($shipmentIncrement == Source::SHIPMENT_INCREMENT_ORDER_BASED) {
            $shipmentIncrementBase = $order->getIncrementId();
            $shipments = $order->getShipmentsCollection();
            $shipmentIndex = $order->getShipmentsCollection()->count();
        } elseif ($shipmentIncrement == Source::SHIPMENT_INCREMENT_PO_BASED) {
            $shipmentIncrementBase = $udpo->getIncrementId();
            $shipments = $udpo->getShipmentsCollection();
            $shipmentIndex = $udpo->getShipmentsCollection()->count();
        }
        do {
            $incIdExists = false;
            $checkIncrementId = sprintf('%s-%s', $shipmentIncrementBase, $shipmentIndex);
            foreach ($shipments as $__s) {
                if ($checkIncrementId == $__s->getIncrementId()) {
                    $incIdExists = true;
                }
            }
            foreach ($extraShipments as $__k => $__s) {
                if ($__k !== $skipKey && $checkIncrementId == $__s->getIncrementId()) {
                    $incIdExists = true;
                }
            }
            $shipmentIndex++;
        } while ($incIdExists);
        return $checkIncrementId;
    }

    public function completeShipment($shipment) {}

    public function invoiceShipment($shipment)
    {
    	if ($shipment->getNoInvoiceFlag()) return false;
        if (!($udpo = $this->getShipmentPo($shipment))) return false;
        $autoInvoiceFlag = $this->scopeConfig->getValue('udropship/purchase_order/autoinvoice_shipment', ScopeInterface::SCOPE_STORE, $udpo->getStoreId());
        if (!$shipment->getDoInvoiceFlag()) {
	        if (!$autoInvoiceFlag) return false;
	        $autoInvoiceStatuses = $this->scopeConfig->getValue('udropship/purchase_order/autoinvoice_shipment_statuses', ScopeInterface::SCOPE_STORE, $udpo->getStoreId());
	        if (!is_array($autoInvoiceStatuses)) {
	            $autoInvoiceStatuses = explode(',', $autoInvoiceStatuses);
	        }
	        if (!in_array($shipment->getUdropshipStatus(), $autoInvoiceStatuses)) return false;
        }
        if (!$udpo->canInvoiceShipment($shipment)) {
            if (!$udpo->getOrder()->getInvoiceCollection()->getItemByColumnValue('shipment_id', $shipment->getId())) {
                $udpo->addComment(__('Cannot autoinvoice shipment # %1', $shipment->getIncrementId()), false, false);
                $udpo->saveComments();
            }
            return false;
        }
        if (Source::AUTOINVOICE_SHIPMENT_YES == $autoInvoiceFlag
            && !$shipment->getOrder()->getPayment()->canCapturePartial()
        ) {
            $udpo->addComment(__('Cannot autoinvoice shipment # %1: order payment method does not allow partial capture', $shipment->getIncrementId()), false, false);
            $udpo->saveComments();
            return false;
        } elseif (Source::AUTOINVOICE_SHIPMENT_ORDER == $autoInvoiceFlag
            && !$shipment->getOrder()->getPayment()->canCapture()
        ) {
            $udpo->addComment(__('Cannot autoinvoice shipment # %1: order payment method does not allow online capture', $shipment->getIncrementId()), false, false);
            $udpo->saveComments();
            return false;
        }

        $isItemsRegistered = $isFullRegistered = false;
        $udpo->getResource()->beginTransaction();
        try {

            if (Source::AUTOINVOICE_SHIPMENT_ORDER == $autoInvoiceFlag) {

                /** @var \Magento\Sales\Model\Service\InvoiceService $invoice */
                $invoice = $this->_hlp->getObj('\Magento\Sales\Model\Service\InvoiceService')->prepareInvoice($shipment->getOrder());

                $invoice->getOrder()->getPayment()->unsParentTransactionId();
                $invoice->getOrder()->getPayment()->unsTransactionId();

                if ($invoice->getBaseGrandTotal()>0) {
                    $invoice->setRequestedCaptureCase(Invoice::CAPTURE_ONLINE);
                } else {
                    $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
                }

                $isItemsRegistered = true;

                $invoice->register();
                $invoice->getOrder()->setIsInProcess(true);

                $isFullRegistered = true;

                $this->_hlp->transactionFactory()->create()
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder())
                    ->save();

            } else {

                $qtys = [];
                foreach ($shipment->getAllItems() as $sItem) {
                    $qtys[$sItem->getUdpoItemId()] = $sItem->getQty();
                }

                $order = $udpo->getOrder();
                $convertor = $this->_convertOrderFactory->create();

                $poItems = $udpo->getAllItems();

                $orderToPoItemMap = [];
                foreach ($poItems as $poItem) {
                    $orderToPoItemMap[$poItem->getOrderItemId()] = $poItem;
                }

                $invoice = $convertor->toInvoice($order)->setUdpo($udpo)->setUdpoId($udpo->getId())->setShipmentId($shipment->getId());
                $totalQty = 0;

                $hasItemToInvoice = false;

                foreach ($qtys as $poItemId => $qty) {
                    $poItem    = $udpo->getItemById($poItemId);
                    $orderItem = $poItem->getOrderItem();

                    if (!$this->_canInvoiceItem($orderItem, $poItem, $orderToPoItemMap, $qtys)) {
                        continue;
                    }

                    $hasItemToInvoice = true;

                    $item = $convertor->itemToInvoiceItem($orderItem)->setUdpoItem($poItem)->setUdpoItemId($poItemId);

                    if ($orderItem->isDummy()) {
                        $qty = 1;
                    } else {
                        $totalQty += $qty;
                    }
                    $item->setQty($qty);
                    $invoice->addItem($item);

                    $poItem->setQtyInvoiced(
                        $poItem->getQtyInvoiced()+$item->getQty()
                    );
                }

                $invoice->setBaseShippingAmount($shipment->getBaseShippingAmount());

                $invoice->setTotalQty($totalQty);
                $invoice->collectTotals();
                $order->getInvoiceCollection()->addItem($invoice);

                $order->getPayment()->unsParentTransactionId();
                $order->getPayment()->unsTransactionId();

                if ($invoice->getBaseGrandTotal()>0) {
                    $invoice->setRequestedCaptureCase(Invoice::CAPTURE_ONLINE);
                } else {
                    $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
                }

                $isItemsRegistered = true;

                $invoice->register();

                $isFullRegistered = true;

                /** @var \Magento\Sales\Model\Order\Item $__oi */
                foreach ($order->getAllItems() as $__oi) {
                    $__oi->setProductOptions($__oi->getProductOptions());
                }

                $this->_hlp->transactionFactory()->create()
                    ->addObject($invoice)
                    ->addObject($order->setData('___dummy',1))
                    ->addObject($udpo->setData('___dummy',1))
                    ->save();

                $udpo->addComment(__('created invoice # %1 for shipment # %2', $invoice->getIncrementId(), $shipment->getIncrementId()), false, false)->saveComments();
            }

            $udpo->getResource()->commit();

        } catch (\Exception $e) {
            if (isset($invoice)) {
                if ($isFullRegistered) {
                    $invoice->cancel();
                } elseif ($isItemsRegistered) {
                    foreach ($invoice->getAllItems() as $item) {
                        $item->cancel();
                    }
                }
            }
            $udpo->getResource()->rollBack();
            $udpo->addComment(__('Autoinvoice Error for shipment # %1: %2', $shipment->getIncrementId(), $e->getMessage()), false, false);
            $udpo->saveComments();
            $this->_logger->error($e);
        }
        return true;
    }

    public function canCreatePo($order)
    {
        if ($order->canUnhold()) {
            return false;
        }
        foreach ($order->getAllItems() as $item) {
            if ($this->getOrderItemQtyToUdpo($item)>0 && (!$item->getLockedDoUdpo() || $order->getSkipLockedCheckFlag())) {
                return true;
            }
        }
        return false;
    }

    public function checkCreatePoQtys($order, $qtys)
    {
        $result = true;
        foreach ($qtys as $itemId => $qty) {
            if (($oItem = $this->_hlp->getOrderItemById($order, $itemId))) {
                $result = $result && ($qty <= $this->getOrderItemQtyToUdpo($oItem) || $oItem->isDummy(true));
            }
        }
        return $result;
    }

    public function canPoItem($item, $qtys=[])
    {
        return $this->_canPoItem($item, $qtys);
    }
    protected function _canPoItem($item, $qtys=[])
    {
        if ($item->getLockedDoUdpo() && !$item->getOrder()->getSkipLockedCheckFlag()) {
            return false;
        }
        if ($item->isDummy(true)) {
            if ($item->getHasChildren()) {
                $children = $item->getChildrenItems() ? $item->getChildrenItems() : $item->getChildren();
                foreach ($children as $child) {
                    if ($this->_canPoItem($child, $qtys)) {
                        return true;
                    }
                }
                return false;
            } else if($item->getParentItem()) {
                return $this->_canPoItem($item->getParentItem(), $qtys);
            }
        } else {
            if (empty($qtys)) {
                return $this->getOrderItemQtyToUdpo($item)>0;
            } else {
                return isset($qtys[$item->getId()]) && $qtys[$item->getId()] > 0;
            }
        }
    }

    protected function _canShipItem($orderItem, $poItem, $orderToPoItemMap, $qtys=[])
    {
        $sId = $orderItem->getOrder() ? $orderItem->getOrder()->getStoreId() : null;
        $enableVirtual = $this->scopeConfig->getValue('udropship/misc/enable_virtual', ScopeInterface::SCOPE_STORE, $sId);
        if ($orderItem->getIsVirtual() && !$enableVirtual || $orderItem->getLockedDoShip()) {
            return false;
        }
        if ($orderItem->isDummy(true)) {
            if ($orderItem->getHasChildren()) {
                foreach ($orderItem->getChildrenItems() as $child) {
                    if ($child->getIsVirtual() && !$enableVirtual) {
                        continue;
                    }
                    if (isset($orderToPoItemMap[$child->getId()])
                        && ($poChild = $orderToPoItemMap[$child->getId()])
                    ) {
                        if (empty($qtys)) {
                            if ($poChild->getQtyToShip() > 0 && !$child->getLockedDoShip()) {
                                return true;
                            }
                        } else {
                            if (isset($qtys[$poChild->getId()]) && $qtys[$poChild->getId()] > 0 && !$child->getLockedDoShip()) {
                                return true;
                            }
                        }
                    }
                }
                return false;
            } else if (($parent = $orderItem->getParentItem())
                && isset($orderToPoItemMap[$parent->getId()])
                && ($poParent = $orderToPoItemMap[$parent->getId()])
                && !$parent->getLockedDoShip()
            ) {
                if (empty($qtys)) {
                    return $poParent->getQtyToShip() > 0;
                } else {
                    return isset($qtys[$poParent->getId()]) && $qtys[$poParent->getId()] > 0;
                }
            }
        } else {
            if (empty($qtys)) {
                return $poItem->getQtyToShip() > 0;
            } else {
                return isset($qtys[$poItem->getId()]) && $qtys[$poItem->getId()] > 0;
            }
        }
        return false;
    }

    protected function _canInvoiceItem($orderItem, $poItem, $orderToPoItemMap, $qtys=[])
    {
        if ($orderItem->getLockedDoInvoice()) {
            return false;
        }
        if ($orderItem->isDummy()) {
            if ($orderItem->getHasChildren()) {
                foreach ($orderItem->getChildrenItems() as $child) {
                    if (isset($orderToPoItemMap[$child->getId()])
                        && ($poChild = $orderToPoItemMap[$child->getId()])
                    ) {
                        if (empty($qtys)) {
                            if ($poChild->getQtyToInvoice() > 0 && !$child->getLockedDoInvoice()) {
                                return true;
                            }
                        } else {
                            if (isset($qtys[$poChild->getId()]) && $qtys[$poChild->getId()] > 0 && !$child->getLockedDoInvoice()) {
                                return true;
                            }
                        }
                    }
                }
                return false;
            } else if (($parent = $orderItem->getParentItem())
                && isset($orderToPoItemMap[$parent->getId()])
                && ($poParent = $orderToPoItemMap[$parent->getId()])
                && !$parent->getLockedDoInvoice()
            ) {
                if (empty($qtys)) {
                    return $poParent->getQtyToInvoice() > 0;
                } else {
                    return isset($qtys[$poParent->getId()]) && $qtys[$poParent->getId()] > 0;
                }
            }
        } else {
            if (empty($qtys)) {
                return $poItem->getQtyToInvoice() > 0;
            } else {
                return isset($qtys[$poItem->getId()]) && $qtys[$poItem->getId()] > 0;
            }
        }
        return false;
    }

    public function setShipmentItemQty($shipmentItem, $poItem, $qty)
    {
        if ($qty <= $poItem->getQtyToShip() || $shipmentItem->getOrderItem()->isDummy(true)) {
            return $shipmentItem->setQty($qty);
        }
        else {
            throw new \Exception(
                __('Invalid qty to ship for item "%1"', $shipmentItem->getName())
            );
        }
    }

    public function setInvoiceItemQty($iItem, $poItem, $qty)
    {
        if ($qty <= $poItem->getQtyToInvoice() || $iItem->getOrderItem()->isDummy()) {
            return $iItem->setQty($qty);
        }
        else {
            throw new \Exception(
                __('Invalid qty to invoice for item "%1"', $iItem->getName())
            );
        }
    }


    public function getOrderItemQtyToUdpo($item, $skipDummy=false)
    {
        if ($item->isDummy(true) && !$skipDummy) {
            return 0;
        }
        $qty = $item->getQtyOrdered()
            - $item->getQtyUdpo()
            - $item->getQtyRefunded()
            - $item->getQtyCanceled();
        return max($qty, 0);
    }

    public function toUdpo($order)
    {
        $udpo = $this->_poFactory->create();
        $udpo->setOrder($order)
            ->setStoreId($order->getStoreId())
            ->setCustomerId($order->getCustomerId())
            ->setBillingAddressId($order->getBillingAddressId())
            ->setShippingAddressId($order->getShippingAddressId());
        $this->_objectCopyService->copyFieldsetToTarget('sales_convert_order', 'to_udpo', $order, $udpo);
        return $udpo;
    }

    public function itemToUdpoItem($orderItem)
    {
        $udpoItem = $this->_poItemFactory->create();
        $udpoItem->setOrderItem($orderItem)
            ->setProductId($orderItem->getProductId());
        $this->_objectCopyService->copyFieldsetToTarget('sales_convert_order_item', 'to_udpo_item', $orderItem, $udpoItem);
        return $udpoItem;
    }

    public function initOrderUdposCollection($order, $forceReload=false)
    {
        if (!$order->hasUdposCollection() || $forceReload) {
            $udposCollection = $this->_poCollection
                ->setOrderFilter($order);
            $order->setUdposCollection($udposCollection);

            if ($order->getId()) {
                foreach ($udposCollection as $udpo) {
                    $udpo->setOrder($order);
                }
            }
        }
        return $this;
    }

    public function getUdpoStatusName($po)
    {
        $statuses = $this->src()->setPath('po_statuses')->toOptionHash();
        $id = $po->getUdropshipStatus();
        return isset($statuses[$id]) ? $statuses[$id] : 'Unknown';
    }

    /**
     * @return \Unirgy\DropshipPo\Model\Source
     */
    public function src()
    {
        return $this->_hlp->getObj('Unirgy\DropshipPo\Model\Source');
    }

    protected $_vendorShipmentCollection;

    public function getVendorShipmentCollection()
    {
        if (!$this->_vendorShipmentCollection) {
            $collection = $this->_hlp->createObj('Magento\Sales\Model\Order\Shipment')->getCollection();
            $poIds = [];
            foreach ($this->getVendorPoCollection() as $po) {
                $poIds[] = $po->getId();
            }
            if (!empty($poIds)) {
                $collection->getSelect()->where('udpo_id in (?)', $poIds);
            } else {
                $collection->getSelect()->where('false');
            }
            $collection->getSelect()->where('udropship_vendor=?', ObjectManager::getInstance()->get('Unirgy\Dropship\Model\Session')->getVendorId());
            $this->_vendorShipmentCollection = $collection;
        }
        return $this->_vendorShipmentCollection;
    }

    /**
     * @var \Unirgy\DropshipPo\Model\ResourceModel\Po\Collection
     */
    protected $_vendorPoCollection;

    public function getVendorPoCollection()
    {
        if (!$this->_vendorPoCollection) {
            /** @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate */
            $localeDate = $this->_hlp->getObj('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
            $datetimeFormatInt = \Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT;
            $dateFormat = $localeDate->getDateFormat(\IntlDateFormatter::SHORT);
            $vendorId = $this->_hlp->session()->getVendorId();
            $vendor = $this->_hlp->getVendor($vendorId);
            $collection = $this->_poFactory->create()->getCollection();

            $collection->join('sales_order', "sales_order.entity_id=main_table.order_id", [
                'order_increment_id' => 'increment_id',
                'order_created_at' => 'created_at',
                'shipping_method',
            ]);

            $collection->addAttributeToFilter('main_table.udropship_vendor', $vendorId);

            $r = $this->_request;

            if (($v = $r->getParam('filter_order_id_from'))) {
                $collection->addAttributeToFilter("sales_order.increment_id", ['gteq'=>$v]);
            }
            if (($v = $r->getParam('filter_order_id_to'))) {
                $collection->addAttributeToFilter("sales_order.increment_id", ['lteq'=>$v]);
            }

            if (($v = $r->getParam('filter_order_date_from'))) {
                $_filterDate = $this->_hlp->dateLocaleToInternal($v, $dateFormat, true);
                $collection->addAttributeToFilter("sales_order.created_at", ['gteq'=>$_filterDate]);
            }
            if (($v = $r->getParam('filter_order_date_to'))) {
                $_filterDate = $this->_hlp->dateLocaleToInternal($v, $dateFormat, true);
                $_filterDate = $this->_hlp->utcDateObj($_filterDate);
                $_filterDate->add(new \DateInterval('P1D'));
                $_filterDate->sub(new \DateInterval('PT1S'));
                $_filterDate = datefmt_format_object($_filterDate, $datetimeFormatInt);
                $collection->addAttributeToFilter("sales_order.created_at", ['lteq'=>$_filterDate]);
            }

            if (($v = $r->getParam('filter_po_id_from'))) {
                $collection->addAttributeToFilter('main_table.increment_id', ['gteq'=>$v]);
            }
            if (($v = $r->getParam('filter_po_id_to'))) {
                $collection->addAttributeToFilter('main_table.increment_id', ['lteq'=>$v]);
            }

            if (($v = $r->getParam('filter_po_date_from'))) {
                $_filterDate = $this->_hlp->dateLocaleToInternal($v, $dateFormat, true);
                $collection->addAttributeToFilter('main_table.created_at', ['gteq'=>$_filterDate]);
            }
            if (($v = $r->getParam('filter_po_date_to'))) {
                $_filterDate = $this->_hlp->dateLocaleToInternal($v, $dateFormat, true);
                $_filterDate = $this->_hlp->utcDateObj($_filterDate);
                $_filterDate->add(new \DateInterval('P1D'));
                $_filterDate->sub(new \DateInterval('PT1S'));
                $_filterDate = datefmt_format_object($_filterDate, $datetimeFormatInt);
                $collection->addAttributeToFilter('main_table.created_at', ['lteq'=>$_filterDate]);
            }

            if (($v = $r->getParam('filter_method'))) {
                if (array_key_exists('VIRTUAL_PO', $v)) {
                    $collection->addFieldToFilter(
                        ['main_table.udropship_method', 'main_table.is_virtual'],
                        [['in'=>array_keys($v)], '1']
                    );
                } else {
                    $collection->addAttributeToFilter('main_table.udropship_method', ['in'=>array_keys($v)]);
                }
            }

            if (!$r->getParam('apply_filter') && $vendor->getData('vendor_po_grid_status_filter')) {
                $filterStatuses = $vendor->getData('vendor_po_grid_status_filter');
                if (!$this->isVpStatusFilterAsValue()) {
                    $filterStatuses = array_combine($filterStatuses, array_fill(0, count($filterStatuses), 1));
                }
                $r->setParam('filter_status', $filterStatuses);
            }

            if (($v = $r->getParam('filter_status'))) {
                $fStatus = array_keys($v);
                if ($this->isVpStatusFilterAsValue()) {
                    $fStatus = $v;
                }
                $collection->addAttributeToFilter('main_table.udropship_status', ['in'=>$fStatus]);
            }

            if (!$r->getParam('sort_by') && $vendor->getData('vendor_po_grid_sortby')) {
                $r->setParam('sort_by', $vendor->getData('vendor_po_grid_sortby'));
                $r->setParam('sort_dir', $vendor->getData('vendor_po_grid_sortdir'));
            }
            if (($v = $r->getParam('sort_by'))) {
                $map = ['order_date'=>'order_created_at', 'po_date'=>'created_at'];
                if (isset($map[$v])) {
                    $v = $map[$v];
                }
                $collection->setOrder($v, $r->getParam('sort_dir'));
            }
            $this->_vendorPoCollection = $collection;
        }
        return $this->_vendorPoCollection;
    }

    public function isVpStatusFilterAsValue()
    {
        return $this->scopeConfig->getValue('udropship/vendor/interface_theme', ScopeInterface::SCOPE_STORE)=='default/udropship_new';
    }

    public function getOrderItemVendorName($orderItem)
    {
        if ($orderItem->getHasChildren() && $orderItem->isDummy(true)) {
            foreach ($orderItem->getChildrenItems() as $child) {
                $vendor = $this->_hlp->getVendor($child->getUdropshipVendor());
                break;
            }
        } else {
            $vendor = $this->_hlp->getVendor($orderItem->getUdropshipVendor());
        }
        return $vendor && $vendor->getId() ? $vendor->getVendorName() : '';
    }

    public function getVendorPoMultiPdf($udpos)
    {
        foreach ($udpos as $udpo) {
            $this->_hlp->assignVendorSkus($udpo);
            $tracks = $udpo->getOrder()->getTracksCollection();
            $tracks->load();
            foreach ($tracks as $id=>$track) {
                $tracks->removeItemByKey($id);
            }
            if ($udpo->getUdropshipMethodDescription()) {
                $udpo->getOrder()->setData('__orig_shipping_description', $udpo->getOrder()->getShippingDescription());
                $udpo->getOrder()->setShippingDescription($udpo->getUdropshipMethodDescription());
            }
        }
        $pdf = $this->_pdfPoFactory->create()
            ->setUseFont($this->scopeConfig->getValue('udropship/vendor/pdf_use_font', ScopeInterface::SCOPE_STORE))
            ->getPdf($udpos);
        foreach ($udpos as $udpo) {
            $this->_hlp->unassignVendorSkus($udpo);
            if ($udpo->getOrder()->hasData('__orig_shipping_description')) {
                $udpo->getOrder()->setShippingDescription($udpo->getOrder()->getData('__orig_shipping_description'));
                $udpo->getOrder()->unsetData('__orig_shipping_description');
            }
        }
        return $pdf;
    }

    public function sendNewPoNotificationEmail($po, $comment='')
    {
        $order = $po->getOrder();
        $store = $order->getStore();

        $vendor = $po->getVendor();

        $hlp = $this->_hlp;
        $udpoHlp = $this;
        $data = [];

        if (!$po->getResendNotificationFlag()
            && ($this->_hlp->getScopeConfig('udropship/vendor/attach_packingslip', $store) && $vendor->getAttachPackingslip()
            || $this->_hlp->getScopeConfig('udropship/vendor/attach_shippinglabel', $store) && $vendor->getAttachShippinglabel() && $vendor->getLabelType())
        ) {
            $udpoHlp->createReturnAllShipments=true;
            if ($shipments = $udpoHlp->createShipmentFromPo($po, [], true, true, true)) {
                foreach ($shipments as $shipment) {
                    $shipment->setNewShipmentFlag(true);
                    $shipment->setDeleteOnFailedLabelRequestFlag(true);
                }
            }
            $udpoHlp->createReturnAllShipments=false;
        }

        if ($po->getResendNotificationFlag()) {
            foreach ($po->getShipmentsCollection() as $_shipment) {
                if ($_shipment->getUdropshipStatus()!=UdropshipSource::SHIPMENT_STATUS_CANCELED) {
                    $shipments[] = $_shipment;
                    break;
                }
            }
        }

        $adminTheme = $this->scopeConfig->getValue('udropship/admin/interface_theme', ScopeInterface::SCOPE_STORE, 0);

        if ($this->_hlp->getScopeConfig('udropship/purchase_order/attach_po_pdf', $store) && $vendor->getAttachPoPdf()) {
            $hlp->setDesignStore(0, 'adminhtml', $adminTheme);

            $orderShippingAmount = $order->getShippingAmount();
            $order->setShippingAmount($po->getShippingAmount());
            $order->setShippingInclTax($po->getShippingAmountIncl());
            $order->setBaseShippingInclTax($po->getBaseShippingAmountIncl());
            $order->setBaseShippingAmount($po->getBaseShippingAmount());

            $pdf = $this->getVendorPoMultiPdf([$po]);

            $order->setShippingAmount($orderShippingAmount);

            $data['_ATTACHMENTS'][] = [
                'content'=>$pdf->render(),
                'filename'=>'purchase_order-'.$po->getIncrementId().'-'.$vendor->getId().'.pdf',
                'type'=>'application/x-pdf',
            ];
            $hlp->setDesignStore();
        }

        if ($this->_hlp->getScopeConfig('udropship/vendor/attach_packingslip', $store) && $vendor->getAttachPackingslip() && !empty($shipments)) {
            $hlp->setDesignStore(0, 'adminhtml', $adminTheme);

            foreach ($shipments as $shipment) {
            $orderShippingAmount = $order->getShippingAmount();
            $order->setShippingAmount($shipment->getShippingAmount());

            $pdf = $this->_hlp->getVendorShipmentsPdf([$shipment]);

            $order->setShippingAmount($orderShippingAmount);
            $shipment->setDeleteOnFailedLabelRequestFlag(false);

            $data['_ATTACHMENTS'][] = [
                'content'=>$pdf->render(),
                'filename'=>'packingslip-'.$po->getIncrementId().'-'.$vendor->getId().'.pdf',
                'type'=>'application/x-pdf',
            ];
            }
            $hlp->setDesignStore();
        }

        if ($this->_hlp->getScopeConfig('udropship/vendor/attach_shippinglabel', $store) && $vendor->getAttachShippinglabel()
            && $vendor->getLabelType() && !empty($shipments)
        ) {
            foreach ($shipments as $shipment) {
            try {
                $hlp->unassignVendorSkus($shipment);
                $hlp->unassignVendorSkus($po);
                foreach ($shipment->getAllItems() as $sItem) {
                    $firstOrderItem = $sItem->getOrderItem();
                    break;
                }
                if (!isset($firstOrderItem) || !$firstOrderItem->getUdpompsManual()) {
                    if (!$po->getResendNotificationFlag()) {
                        $batch = $this->_labelBatchFactory->create()->setVendor($vendor)->processShipments([$shipment]);
                        if ($batch->getErrors()) {
                            if ($this->_request->getRouteName()=='udropship') {
                                throw new \Exception($batch->getErrorMessages());
                            } else {
                                $this->_helperError->sendLabelRequestFailedNotification($shipment, $batch->getErrorMessages());
                            }
                        } else {
                            if ($batch->getShipmentCnt()>1) {
                                $labelModel = $this->_hlp->getLabelTypeInstance($batch->getLabelType());
                                $data['_ATTACHMENTS'][] = $labelModel->renderBatchContent($batch);
                            } else {
                                $labelModel = $hlp->getLabelTypeInstance($batch->getLabelType());
                                foreach ($shipment->getAllTracks() as $track) {
                                    $data['_ATTACHMENTS'][] = $labelModel->renderTrackContent($track);
                                }
                            }
                        }
                    } else {
                        $batchIds = [];
                        foreach ($shipment->getAllTracks() as $track) {
                            $batchIds[$track->getBatchId()][] = $track;
                        }
                        foreach ($batchIds as $batchId => $tracks) {
                            $batch = $this->_labelBatchFactory->create()->load($batchId);
                            if (!$batch->getId()) continue;
                            if (count($tracks)>1) {
                                $labelModel = $this->_hlp->getLabelTypeInstance($batch->getLabelType());
                                $data['_ATTACHMENTS'][] = $labelModel->renderBatchContent($batch);
                            } else {
                                reset($tracks);
                                $labelModel = $this->_hlp->getLabelTypeInstance($batch->getLabelType());
                                $data['_ATTACHMENTS'][] = $labelModel->renderTrackContent(current($tracks));
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // ignore if failed
            }
            }
        }

    	if (!empty($shipments)) {
            foreach ($shipments as $shipment) {
                if ($shipment->getNewShipmentFlag() && !$shipment->isDeleted()) {
                    $shipment->setNoInvoiceFlag(false);
                    $hlp->unassignVendorSkus($shipment);
                    $hlp->unassignVendorSkus($po);
                    $udpoHlp->invoiceShipment($shipment);
                }
            }
        }

        $this->inlineTranslation->suspend();

        $shippingAddress = $order->getShippingAddress();
        if (!$shippingAddress) {
            $shippingAddress = $order->getBillingAddress();
        }
        $hlp->assignVendorSkus($po);
        $data += [
            'po'              => $po,
            'order'           => $order,
            'vendor'          => $vendor,
            'comment'         => $comment,
            'store_name'      => $store->getName(),
            'vendor_name'     => $vendor->getVendorName(),
            'is_virtual'      => $po->getIsVirtual()?'1':'',
            'po_id'           => $po->getIncrementId(),
            'order_id'        => $order->getIncrementId(),
            'customer_info'   => $this->_hlp->formatCustomerAddress($shippingAddress, 'html', $vendor),
            'shipping_method' => $po->getUdropshipMethodDescription() ? $po->getUdropshipMethodDescription() : $vendor->getShippingMethodName($order->getShippingMethod(), true),
            'po_url'          => $this->_urlBuilder->getUrl('udpo/vendor/', ['_query'=>'filter_po_id_from='.$po->getIncrementId().'&filter_po_id_to='.$po->getIncrementId()]),
            'po_pdf_url'      => $this->_urlBuilder->getUrl('udpo/vendor/udpoPdf', ['udpo_id'=>$po->getId()]),
        ];

        $template = $vendor->getEmailTemplate();
        if (!$template) {
            $template = $this->_hlp->getScopeConfig('udropship/purchase_order/new_po_vendor_email_template', $store);
        }
        $identity = $this->_hlp->getScopeConfig('udropship/vendor/vendor_email_identity', $store);

        $data['_BCC'] = $vendor->getNewOrderCcEmails();
        if (($emailField = $this->_hlp->getScopeConfig('udropship/vendor/vendor_notification_field', $store))) {
            $email = $vendor->getData($emailField) ? $vendor->getData($emailField) : $vendor->getEmail();
        } else {
            $email = $vendor->getEmail();
        }

        /** @var \Magento\Framework\Mail\Template\SenderResolverInterface $senderResolver */
        $senderResolver = $this->_hlp->getObj('Magento\Framework\Mail\Template\SenderResolverInterface');
        $senderInfo = $senderResolver->resolve(
            $identity,
            $store
        );

        $this->_transportBuilder->setTemplateIdentifier(
            $template
        )->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $store->getId(),
            ]
        )->setTemplateVars(
            $data
        )->setFrom(
            $senderInfo
        )->addTo(
            $email,
            $vendor->getVendorName()
        );

        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();

        $hlp->unassignVendorSkus($po);

        $this->inlineTranslation->resume();

    }

    public function sendPoDeleteVendorNotification($po, $comment='')
    {
        $order = $po->getOrder();
        $store = $order->getStore();

        $vendor = $po->getVendor();

        $hlp = $this->_hlp;
        $udpoHlp = $this;
        $data = [];

        $adminTheme = explode('/', $this->scopeConfig->getValue('udropship/admin/interface_theme', ScopeInterface::SCOPE_STORE, 0));

        if ($this->_hlp->getScopeConfig('udropship/purchase_order/attach_po_pdf', $store) && $vendor->getAttachPoPdf()) {
            $hlp->setDesignStore(0, 'adminhtml', $adminTheme);

            $orderShippingAmount = $order->getShippingAmount();
            $order->setShippingAmount($po->getShippingAmount());
            $order->setShippingInclTax($po->getShippingAmountIncl());
            $order->setBaseShippingInclTax($po->getBaseShippingAmountIncl());
            $order->setBaseShippingAmount($po->getBaseShippingAmount());

            $pdf = $this->getVendorPoMultiPdf([$po]);

            $order->setShippingAmount($orderShippingAmount);

            $data['_ATTACHMENTS'][] = [
                'content'=>$pdf->render(),
                'filename'=>'purchase_order-'.$po->getIncrementId().'-'.$vendor->getId().'.pdf',
                'type'=>'application/x-pdf',
            ];
            $hlp->setDesignStore();
        }

        $this->inlineTranslation->suspend();

        $shippingAddress = $order->getShippingAddress();
        if (!$shippingAddress) {
            $shippingAddress = $order->getBillingAddress();
        }
        $hlp->assignVendorSkus($po);
        $data += [
            'po'              => $po,
            'order'           => $order,
            'vendor'          => $vendor,
            'comment'         => $comment,
            'store_name'      => $store->getName(),
            'vendor_name'     => $vendor->getVendorName(),
            'po_id'           => $po->getIncrementId(),
            'order_id'        => $order->getIncrementId(),
            'customer_info'   => $this->_hlp->formatCustomerAddress($shippingAddress, 'html', $vendor),
            'shipping_method' => $po->getUdropshipMethodDescription() ? $po->getUdropshipMethodDescription() : $vendor->getShippingMethodName($order->getShippingMethod(), true),
        ];

        $template = $this->_hlp->getScopeConfig('udropship/purchase_order/delete_po_vendor_email_template', $store);
        $identity = $this->_hlp->getScopeConfig('udropship/vendor/vendor_email_identity', $store);

        $data['_BCC'] = $vendor->getNewOrderCcEmails();
        if (($emailField = $this->_hlp->getScopeConfig('udropship/vendor/vendor_notification_field', $store))) {
            $email = $vendor->getData($emailField) ? $vendor->getData($emailField) : $vendor->getEmail();
        } else {
            $email = $vendor->getEmail();
        }

        /** @var \Magento\Framework\Mail\Template\SenderResolverInterface $senderResolver */
        $senderResolver = $this->_hlp->getObj('Magento\Framework\Mail\Template\SenderResolverInterface');
        $senderInfo = $senderResolver->resolve(
            $identity,
            $store
        );

        $this->_transportBuilder->setTemplateIdentifier(
            $template
        )->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $store->getId(),
            ]
        )->setTemplateVars(
            $data
        )->setFrom(
            $senderInfo
        )->addTo(
            $email,
            $vendor->getVendorName()
        );

        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();

        $hlp->unassignVendorSkus($po);

        $this->inlineTranslation->resume();
    }

    public function sendPoCommentNotificationEmail($po, $comment)
    {
        $order = $po->getOrder();
        $store = $order->getStore();

        $vendor = $po->getVendor();

        $hlp = $this->_hlp;
        $udpoHlp = $this;
        $data = [];

        $this->inlineTranslation->suspend();

        $data += [
            'po'              => $po,
            'order'           => $order,
            'vendor'          => $vendor,
            'comment'         => $comment,
            'store_name'      => $store->getName(),
            'vendor_name'     => $vendor->getVendorName(),
            'po_id'           => $po->getIncrementId(),
            'po_status'       => $po->getUdropshipStatusName(),
            'order_id'        => $order->getIncrementId(),
            'po_url'          => $this->_urlBuilder->getUrl('udpo/vendor/', ['_query'=>'filter_po_id_from='.$po->getIncrementId().'&filter_po_id_to='.$po->getIncrementId()]),
            'po_pdf_url'      => $this->_urlBuilder->getUrl('udpo/vendor/udpoPdf', ['udpo_id'=>$po->getId()]),
        ];

        $template = $this->_hlp->getScopeConfig('udropship/purchase_order/po_comment_vendor_email_template', $store);
        $identity = $this->_hlp->getScopeConfig('udropship/vendor/vendor_email_identity', $store);

        $data['_BCC'] = $vendor->getNewOrderCcEmails();
        if (($emailField = $this->_hlp->getScopeConfig('udropship/vendor/vendor_notification_field', $store))) {
            $email = $vendor->getData($emailField) ? $vendor->getData($emailField) : $vendor->getEmail();
        } else {
            $email = $vendor->getEmail();
        }

        /** @var \Magento\Framework\Mail\Template\SenderResolverInterface $senderResolver */
        $senderResolver = $this->_hlp->getObj('Magento\Framework\Mail\Template\SenderResolverInterface');
        $senderInfo = $senderResolver->resolve(
            $identity,
            $store
        );

        $this->_transportBuilder->setTemplateIdentifier(
            $template
        )->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $store->getId(),
            ]
        )->setTemplateVars(
            $data
        )->setFrom(
            $senderInfo
        )->addTo(
            $email,
            $vendor->getVendorName()
        );

        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();

        $this->inlineTranslation->resume();
    }

    public function sendVendorComment($udpo, $comment)
    {
        $order = $udpo->getOrder();
        $store = $order->getStore();
        $to = $this->_hlp->getScopeConfig('udropship/admin/vendor_comments_receiver', $store);
        $subject = $this->_hlp->getScopeConfig('udropship/admin/vendor_po_comments_subject', $store);
        $template = $this->_hlp->getScopeConfig('udropship/admin/vendor_po_comments_template', $store);
        $vendor = $this->_hlp->getVendor($udpo->getUdropshipVendor());
        $ahlp = $this->_backendUrlFactory->create();

        if ($subject && $template && $vendor->getId()) {
            $toEmail = $this->_hlp->getScopeConfig('trans_email/ident_'.$to.'/email', $store);
            $toName = $this->_hlp->getScopeConfig('trans_email/ident_'.$to.'/name', $store);
            $data = [
                'vendor_name'   => $vendor->getVendorName(),
                'order_id'      => $order->getIncrementId(),
                'po_id'         => $udpo->getIncrementId(),
                'vendor_url'    => $ahlp->getUrl('udropship/vendor/edit', [
                    'id'        => $vendor->getId(),
                    '_scope'    => 0
                ]),
                'order_url'     => $ahlp->getUrl('sales/order/view', [
                    'order_id'  => $order->getId(),
                    '_scope'    => 0
                ]),
                'po_url'  => $ahlp->getUrl('udpo/order_po/view', [
                    'udpo_id'  => $udpo->getId(),
                    'order_id' => $order->getId(),
                    '_scope'    => 0
                ]),
                'comment'      => $comment,
            ];
            foreach ($data as $k=>$v) {
                $subject = str_replace('{{'.$k.'}}', $v, $subject);
                $template = str_replace('{{'.$k.'}}', $v, $template);
            }

            $message = $this->_hlp->createTextMailMessage([
                'name' => $toName,
                'email' => $toEmail,
                'from_name' => $vendor->getVendorName(),
                'from_email' => $vendor->getEmail(),
                'subject' => $subject,
                'body' => $template
            ]);

            $transport = $this->_hlp->createObj('Magento\Framework\Mail\TransportInterface', ['message' => $message]);
            $transport->sendMessage();
        }

        $udpo->addComment(__($vendor->getVendorName().': '.$comment), false, true)->saveComments();

        return $this;
    }

    public function getShipmentPo($shipment)
    {
        if (!$shipment->hasUdpo() && $shipment->getUdpoId()
            && ($po = $this->_poFactory->create()->load($shipment->getUdpoId())) && $po->getId()
        ) {
            $shipment->setUdpo($po->setOrder($shipment->getOrder()));
        } elseif (!$shipment->hasUdpo()) {
            $shipment->setUdpo(false);
        }
        return $shipment->getUdpo();
    }

    public function getShipmentPoItem($sItem)
    {
        if (!$sItem->hasUdpoItem() && $sItem->getUdpoItemId()) {
            if (($shipment = $sItem->getShipment())
                && ($po = $this->getShipmentPo($shipment))
                && ($poItem = $po->getItemById($sItem->getUdpoItemId()))
            ) {
                $sItem->setUdpoItem($poItem);
            } elseif (($poItem = $this->_poItemFactory->create()->load($sItem->getUdpoItemId())) && $poItem->getId()) {
                $sItem->setUdpoItem($poItem);
            }
        } elseif (!$sItem->hasUdpoItem()) {
            $sItem->setUdpoItem(false);
        }
        return $sItem->getUdpoItem();
    }

    public function getInvoicePo($invoice)
    {
        if (!$invoice->hasUdpo() && $invoice->getUdpoId()
            && ($po = $this->_poFactory->create()->load($invoice->getUdpoId())) && $po->getId()
        ) {
            $invoice->setUdpo($po);
        } elseif (!$invoice->hasUdpo()) {
            $invoice->setUdpo(false);
        }
        return $invoice->getUdpo();
    }

    public function getInvoicePoItem($iItem)
    {
        if (!$iItem->hasUdpoItem() && $iItem->getUdpoItemId()) {
            if (($invoice = $iItem->getInvoice())
                && ($po = $this->getInvoicePo($invoice))
                && ($poItem = $po->getItemById($iItem->getUdpoItemId()))
            ) {
                $iItem->setUdpoItem($poItem);
            } elseif (($poItem = $this->_poItemFactory->create()->load($iItem->getUdpoItemId())) && $poItem->getId()) {
                $iItem->setUdpoItem($poItem);
            }
        } elseif (!$iItem->hasUdpoItem()) {
            $iItem->setUdpoItem(false);
        }
        return $iItem->getUdpoItem();
    }

    public function getShipmentItemQtyToCancel($shipmentItem)
    {
        return $this->getShipmentItemQtyToShip($shipmentItem);
    }

    public function getShipmentItemQtyToShip($sItem)
    {
        $oItem = $sItem->getOrderItem();
        if ($oItem->isDummy(true)) {
            return 0;
        }
        $qty = $sItem->getQty() - $sItem->getQtyShipped() - $sItem->getQtyCanceled();
        return max($qty, 0);
    }

    public function cancelShipmentItem($sItem, $save)
    {
        if (($poItem = $this->getShipmentPoItem($sItem))) {
            $poItem->setQtyShipped(
                $poItem->getQtyShipped()-$this->getShipmentItemQtyToCancel($sItem)
            );
            $this->_processObjectSave($save, $poItem);
        }
        $oItem = $sItem->getOrderItem();
        $oItem->setQtyShipped(
            $oItem->getQtyShipped()-$this->getShipmentItemQtyToCancel($sItem)
        );
        $this->_processObjectSave($save, $oItem);
        $sItem->setQtyCanceled(
            $sItem->getQtyCanceled()+$this->getShipmentItemQtyToCancel($sItem)
        );
        $this->_processObjectSave($save, $sItem);
    }

    public function cancelShipment($shipment, $save)
    {
        $fullCancel = true;
        foreach ($shipment->getAllItems() as $item) {
            $this->cancelShipmentItem($item, $save);
            $fullCancel = $fullCancel && ($item->getOrderItem()->isDummy(true) || $item->getQtyShipped()<=0);
        }
        if ($fullCancel) {
            $shipment->setUdropshipStatus(UdropshipSource::SHIPMENT_STATUS_CANCELED);
        }
        $this->_processObjectSave($save, $shipment);
        return $fullCancel;
    }

    public function cancelPo($po, $save, $vendor=false)
    {
        $po->getResource()->beginTransaction();
        try {
            foreach ($po->getShipmentsCollection() as $shipment) {
            	if ($po->getFullCancelFlag()) {
            		$this->revertCompleteShipment($shipment, true);
            		$this->cancelShipment($shipment, $save);
            	} elseif ($po->getNonshippedCancelFlag()) {
            		$this->cancelShipment($shipment, $save);
            	}
            }
            $po->cancel()->save();
            $po->getOrder()->setData('___dummy',1)->save();
            $po->getResource()->commit();
            return true;
        } catch (\Exception $e) {
            $po->getResource()->rollBack();
            return false;
        }
    }

    public function processLabelRequestError($shipment, $error)
    {
    	if ($shipment->getCancelOnFailedLabelRequestFlag()
    		|| $shipment->getDeleteOnFailedLabelRequestFlag()
    	) {
    		$this->revertCompleteShipment($shipment, true);
        	$this->cancelShipment($shipment, true);
    	}
        if ($shipment->getDeleteOnFailedLabelRequestFlag()) {
        	$shipment->isDeleted(true);
        	$odlSA = $this->_coreRegistry->registry('isSecureArea');
        	$this->_coreRegistry->unregister('isSecureArea');
        	$this->_coreRegistry->register('isSecureArea', true, true);
			$shipment->delete();
			if (!is_null($odlSA)) {
			    $this->_coreRegistry->unregister('isSecureArea');
			    $this->_coreRegistry->register('isSecureArea', $odlSA, true);
            }
			else {
			    $this->_coreRegistry->unregister('isSecureArea');
            }
        }
		if (($udpo = $this->getShipmentPo($shipment))
			&& ($shipment->getCancelOnFailedLabelRequestFlag() || $shipment->getDeleteOnFailedLabelRequestFlag())
		) {
			if ($shipment->getDeleteOnFailedLabelRequestFlag()) {
				if ($shipment->getNewShipmentFlag()) {
					$comment = __('Shipment was not created due to label request error: %1', $error);
				} else {
					$comment = __('Shipment was deleted due to label request error: %1', $error);
				}
			} else {
				$comment = __('Shipment was canceled due to label request error: %1', $error);
			}
			$udpo->addComment($comment, false, $shipment->getCreatedByVendorFlag())->getCommentsCollection()->save();
		}
		return $this;
    }

    public function getAllowedPoStatusesHash($po)
    {
        $confSrc = $this->src();
        $poStatuses = $this->getAllowedPoStatuses($po);
        $poStatusesHash = [];
        foreach ($confSrc->setPath('po_statuses')->toOptionHash() as $_k => $_v) {
            if ($_k=='' || in_array($_k, $poStatuses)) {
                $poStatusesHash[$_k] = $_v;
            }
        }
        return $poStatusesHash;
    }
    public function getAllowedPoStatuses($po, $auto=false)
    {
        $confSrc = $this->src();
        if ($po->getIsVirtual()) {
            $allowedStatuses = array_keys($confSrc->getAllPoStatuses());
        } else {
            $allowedStatuses = $confSrc->getNonSecurePoStatuses();
            if ($po->hasShippedItem() && !$po->hasItemToShip() && !$po->hasCanceledItem() && $po->isShipmentsDelivered()) {
                $allowedStatuses = $confSrc->getAllowedPoStatusesForDelivered();
            } elseif ($po->hasShippedItem() && !$po->hasItemToShip() && !$po->hasCanceledItem() && $po->isShipmentsShipped()) {
                $allowedStatuses = $confSrc->getAllowedPoStatusesForShipped($auto);
            } elseif ($po->hasCanceledItem() && !$po->hasItemToShip() && !$po->hasShippedItem()) {
                $allowedStatuses = $confSrc->getAllowedPoStatusesForCanceled();
            } elseif ($po->hasShippedItem() && ($po->hasItemToShip() || $po->hasCanceledItem()) && $po->isShipmentsShipped(false)) {
                $allowedStatuses = $confSrc->getAllowedPoStatusesForPartialShipped();
            } else {
                $allowedStatuses[] = Source::UDPO_STATUS_CANCELED;
            }
            if (!in_array($po->getUdropshipStatus(), $allowedStatuses)) {
                $allowedStatuses[] = $po->getUdropshipStatus();
            }
        }
        return $allowedStatuses;
    }
    public function getAllowedPoStatusesJson($po, $auto=false)
    {
        return $this->_hlp->jsonEncode(array_map('strval', $this->getAllowedPoStatuses($po, $auto)));
    }

    public function processPoStatusSave($po, $status, $save, $vendor=false, $comment='', $isVendorNotified=null, $isVisibleToVendor=null)
    {
        $allowedStatuses   =  $this->getAllowedPoStatuses($po, $vendor===false);
        $isVendorNotified  = is_null($isVendorNotified) ? false : $isVendorNotified;
        $isVisibleToVendor = is_null($isVisibleToVendor) ? true : $isVisibleToVendor;
        if ($po->getUdropshipStatus()!=$status
            && (in_array($status, $allowedStatuses) || $po->getForceStatusChangeFlag())
        ) {
            $oldStatus = $po->getUdropshipStatus();
            $this->_eventManager->dispatch(
                'udpo_po_status_save_before',
                ['po'=>$po, 'old_status'=>$oldStatus, 'new_status'=>$status]
            );
            $po->setUdropshipStatus($status);
            $_comment = '';
            if ($vendor) {
                $_comment = __("[%1 changed PO status from '%2' to '%3']",
                    $vendor->getVendorName(),
                    $this->getPoStatusName($oldStatus),
                    $this->getPoStatusName($status)
                );
            } else {
                $_comment = __("[PO status changed from '%1' to '%2']",
                    $this->getPoStatusName($oldStatus),
                    $this->getPoStatusName($status)
                );
            }
            if (!empty($comment)) {
                $_comment = $comment."\n\n".$_comment;
            }
            $po->addComment($_comment, $isVendorNotified, $isVisibleToVendor);
            if ($isVendorNotified) {
                $this->sendPoCommentNotificationEmail($po, $_comment);
            }
            $po->getResource()->saveAttribute($po, 'udropship_status');
            $po->saveComments();
            $this->_eventManager->dispatch(
                'udpo_po_status_save_after',
                ['po'=>$po, 'old_status'=>$oldStatus, 'new_status'=>$status, 'object'=>$po]
            );
            if (in_array($po->getUdropshipStatus(), array(
                Source::UDPO_STATUS_SHIPPED,
                Source::UDPO_STATUS_DELIVERED,
                Source::UDPO_STATUS_CANCELED
            ))) {
                $this->orderStateHandler()->check($po->getOrder());
                if ($po->getOrder()->dataHasChangedFor('status')) {
                    $po->getOrder()->save();
                }
            }
            return true;
        } elseif (0 && $vendor) {
            $oldStatus = $po->getUdropshipStatus();
            $po->addComment(__("%1 tried to change PO status from '%2' to '%3'",
                $vendor->getVendorName(),
                $this->getPoStatusName($oldStatus),
                $this->getPoStatusName($status)
            ), false, true);
            $po->getResource()->saveAttribute($po, 'udropship_status');
            $po->saveComments();
        }
        return false;
    }

    /**
     * @return \Magento\Sales\Model\ResourceModel\Order\Handler\State
     */
    protected function orderStateHandler()
    {
        return $this->_hlp->getObj('Magento\Sales\Model\ResourceModel\Order\Handler\State');
    }

    public function getPoStatusName($status)
    {
        $statuses = $this->src()->setPath('po_statuses')->toOptionHash();
        return isset($statuses[$status]) ? $statuses[$status] : (in_array($status, $statuses) ? $status : 'Unknown');
    }

    public function getVendorUdpoStatuses()
    {
        if ($this->scopeConfig->getValue('udropship/vendor/is_restrict_udpo_status', ScopeInterface::SCOPE_STORE)) {
            $udpoStatuses = $this->scopeConfig->getValue('udropship/vendor/restrict_udpo_status', ScopeInterface::SCOPE_STORE);
            if (!is_array($udpoStatuses)) {
                $udpoStatuses = explode(',', $udpoStatuses);
            }
            $udpoStatuses = array_filter($udpoStatuses);
            return $this->src()->setPath('po_statuses')->getOptionLabel($udpoStatuses);
        } else {
            return $this->src()->setPath('po_statuses')->toOptionHash();
        }
    }

    public function getUdpoStatuses()
    {
        return $this->src()->setPath('po_statuses')->toOptionHash();
    }

    public function assignVendorSkus($po)
    {
        $this->_hlp->assignVendorSkus($po);
        return $this;
    }

    public function unassignVendorSkus($po)
    {
        $this->_hlp->unassignVendorSkus($po);
        return $this;
    }

    public function getPoAvailableMethods($_po)
    {
        $_hlp = $this->_hlp;
        $_poHlp = $this;
        $_id = $_po->getId();
        $_vendor = $_hlp->getVendor($_po->getUdropshipVendor());

        $_order = $_po->getOrder();
        $_address = $_order->getShippingAddress() ? $_order->getShippingAddress() : $_order->getBillingAddress();

        $shipping = $_hlp->getShippingMethods();
        $vShipping = $_vendor->getShippingMethods();

        $poShippingMethod = $_po->getUdropshipMethod();
        if (null == $poShippingMethod) {
            $poShippingMethod = $_order->getShippingMethod();
        }

        $uMethod = explode('_', $_order->getShippingMethod(), 2);
        if ($uMethod[0]=='udsplit') {
            $udMethod = $this->_hlp->mapSystemToUdropshipMethod(
                $poShippingMethod,
                $_vendor
            );
            $uMethodCode = $udMethod->getShippingCode();
        } else {
            $uMethodCode = !empty($uMethod[1]) ? $uMethod[1] : '';
        }

        $method = explode('_', $poShippingMethod, 2);
        $carrierCode = !empty($method[0]) ? $method[0] : $_vendor->getCarrierCode();

        $curShipping = $shipping->getItemByColumnValue('shipping_code', $uMethodCode);
        $methodCode  = !empty($method[1]) ? $method[1] : '';

        $labelCarrierAllowAll = $this->scopeConfig->getValue('udropship/vendor/label_carrier_allow_all', ScopeInterface::SCOPE_STORE, $_order->getStoreId());
        $labelMethodAllowAll = $this->scopeConfig->getValue('udropship/vendor/label_method_allow_all', ScopeInterface::SCOPE_STORE, $_order->getStoreId());

        $availableMethods = [

        ];

        if ($curShipping && $labelMethodAllowAll) {
            $curShipping->useProfile($_vendor);
            $_carriers = [$carrierCode=>0];
            if ($labelCarrierAllowAll) {
                $_carriers = array_merge($_carriers, $curShipping->getAllSystemMethods());
            }
            foreach ($_carriers as $_carrierCode=>$_dummy) {
                $_availableMethods = $_hlp->getCarrierMethods($_carrierCode, true);
                $carrierTitle = $this->scopeConfig->getValue("carriers/$_carrierCode/title", ScopeInterface::SCOPE_STORE, $_order->getStoreId());
                foreach ($_availableMethods as $mCode => $mLabel) {
                    $_amDesc = $carrierTitle.' - '.$mLabel;
                    $_amCode = $_carrierCode.'_'.$mCode;
                    $availableMethods[$_amCode] = $_amDesc;
                }
            }
            $curShipping->resetProfile();
        } elseif ($curShipping && isset($vShipping[$curShipping->getId()])) {
            $curShipping->useProfile($_vendor);
            $methodCode  = !empty($method[1]) ? $method[1] : $curShipping->getSystemMethods($vShipping[$curShipping->getId()]['carrier_code']);
            if (!$labelCarrierAllowAll || $this->_hlp->isUdsprofileActive()) {
                foreach ($vShipping as $_sId => $__vs) {
                    foreach ($__vs as $_vs) {
                        if ($carrierCode != $_vs['carrier_code'] && !$labelCarrierAllowAll || !($_s = $shipping->getItemById($_sId)) || !($_vs['method_code'])) continue;
                        $_amCode = $_vs['carrier_code'].'_'.$_vs['method_code'];
                        $carrierMethods = $this->_hlp->getCarrierMethods($_vs['carrier_code']);
                        if (!isset($carrierMethods[$_vs['method_code']])) continue;
                        $_amDesc = $this->scopeConfig->getValue('carriers/'.$_vs['carrier_code'].'/title', ScopeInterface::SCOPE_STORE, $_order->getStoreId())
                            .' - '.$carrierMethods[$_vs['method_code']];
                        $availableMethods[$_amCode] = $_amDesc;
                    }
                }
            } else {
                foreach ($vShipping as $_sId => $__vs) {
                    if (($_s = $shipping->getItemById($_sId))) {
                        $allSystemMethods = $_s->getAllSystemMethods();
                        foreach ($allSystemMethods as $_smCarrier => $__sm) {
                            foreach ($__sm as $_smMethod) {
                                $_amCode = $_smCarrier.'_'.$_smMethod;
                                $carrierMethods = $this->_hlp->getCarrierMethods($_smCarrier);
                                if (!isset($carrierMethods[$_smMethod])) continue;
                                $_amDesc = $this->scopeConfig->getValue('carriers/'.$_smCarrier.'/title', ScopeInterface::SCOPE_STORE, $_order->getStoreId())
                                    .' - '.$carrierMethods[$_smMethod];
                                $availableMethods[$_amCode] = $_amDesc;
                            }
                        }
                    }
                }
            }
            $curShipping->resetProfile();
        }

        $labelCarrierAllowAlways = $this->scopeConfig->getValue('udropship/vendor/label_carrier_allow_always', ScopeInterface::SCOPE_STORE, $_order->getStoreId());
        if (!is_array($labelCarrierAllowAlways)) {
            $labelCarrierAllowAlways = array_filter(explode(',', $labelCarrierAllowAlways));
        }
        foreach ($labelCarrierAllowAlways as $lcaaCode) {
            $lcaaCarrierMethods = $this->_hlp->getCarrierMethods($lcaaCode, true);
            foreach ($lcaaCarrierMethods as $lcaaMethodCode=>$lcaaMethodTitle) {
                $lcaaFullMethodCode = $lcaaCode.'_'.$lcaaMethodCode;
                $lcaaDesc = $this->scopeConfig->getValue('carriers/'.$lcaaCode.'/title', ScopeInterface::SCOPE_STORE, $_order->getStoreId())
                    .' - '.$lcaaMethodTitle;
                $availableMethods[$lcaaFullMethodCode] = $lcaaDesc;
            }
        }

        if (count($method)>1) {
            $_poCarrierMethods = $this->_hlp->getCarrierMethods($method[0]);
            if (isset($_poCarrierMethods[$method[1]])) {
                $availableMethods[$poShippingMethod] = $this->scopeConfig->getValue('carriers/'.$method[0].'/title', ScopeInterface::SCOPE_STORE, $_order->getStoreId())
                    .' - '.$_poCarrierMethods[$method[1]];
            }
        }
        return $availableMethods;
    }

}

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

namespace Unirgy\DropshipPo\Observer;

use Magento\CatalogInventory\Model\StockFactory;
use Magento\Catalog\Model\ProductFactory as ModelProductFactory;
use Magento\Quote\Model\Quote\Config;
use Psr\Log\LoggerInterface;
use Unirgy\DropshipPo\Helper\Data as DropshipPoHelperData;
use Unirgy\Dropship\Helper\Data as HelperData;
use Unirgy\Dropship\Model\Source;
use Unirgy\Dropship\Model\Vendor\ProductFactory;

abstract class AbstractObserver
{
    /**
     * @var HelperData
     */
    protected $_hlp;

    /**
     * @var DropshipPoHelperData
     */
    protected $_poHlp;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var ProductFactory
     */
    protected $_vendorProductFactory;

    /**
     * @var ModelProductFactory
     */
    protected $_productFactory;

    /**
     * @var Config
     */
    protected $_quoteConfig;


    public function __construct(
        \Unirgy\Dropship\Helper\Data $udropshipHelper,
        \Unirgy\DropshipPo\Helper\Data $udpoHelper,
        \Psr\Log\LoggerInterface $logger,
        \Unirgy\Dropship\Model\Vendor\ProductFactory $vendorProductFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Quote\Model\Quote\Config $quoteConfig
    )
    {
        $this->_hlp = $udropshipHelper;
        $this->_poHlp = $udpoHelper;
        $this->_logger = $logger;
        $this->_vendorProductFactory = $vendorProductFactory;
        $this->_productFactory = $productFactory;
        $this->_quoteConfig = $quoteConfig;

    }

    protected function _udpo_po_save_before($observer, $isStatusEvent)
    {
        $po = $observer->getEvent()->getPo();
        if ($po->getUdropshipVendor()
            && ($vendor = $this->_hlp->getVendor($po->getUdropshipVendor()))
            && $vendor->getId()
            && (!$po->getStatementDate() || $po->getStatementDate() == '0000-00-00 00:00:00')
            && $vendor->getStatementPoType() == 'po'
        ) {
            $stPoStatuses = $vendor->getStatementPoStatus();
            if (!is_array($stPoStatuses)) {
                $stPoStatuses = explode(',', $stPoStatuses);
            }
            if (in_array($po->getUdropshipStatus(), $stPoStatuses)) {
                $po->setStatementDate($this->_hlp->now());
                $po->setUpdatedAt($this->_hlp->now());
                if ($isStatusEvent) {
                    $po->getResource()->saveAttribute($po, 'statement_date');
                    $po->getResource()->saveAttribute($po, 'updated_at');
                }
            }
        }
    }



    protected function _processPoCancel($po)
    {
        $order = $po->getOrder();
        if ($this->_hlp->getScopeFlag('udropship/purchase_order/cancel_order', $order->getStoreId())) {
            if ($po->getUdropshipStatus()==\Unirgy\DropshipPo\Model\Source::UDPO_STATUS_CANCELED) {
                $hasNonCanceled = false;
                $poVids = $orderVids = [];
                foreach ($order->getAllItems() as $oItem) {
                    $orderVids[$oItem->getUdropshipVendor()] = $oItem->getUdropshipVendor();
                }
                $this->_poHlp->initOrderUdposCollection($order);
                foreach ($order->getUdposCollection() as $_po) {
                    $poVids[$_po->getUdropshipVendor()] = $_po->getUdropshipVendor();
                    if ($_po->getUdropshipStatus()!=\Unirgy\DropshipPo\Model\Source::UDPO_STATUS_CANCELED) {
                        $hasNonCanceled = true;
                    }
                }
                $noPoVids = array_diff($orderVids, $poVids);
                if (!$hasNonCanceled && empty($noPoVids)) {
                    $order->cancel()->save();
                }
            }
        }
    }

    protected function _notifyByStatus($po)
    {
        try {
            $v = $this->_hlp->getVendor($po->getUdropshipVendor());
            $notifyOnPoStatus = $v->getData('notify_by_udpo_status');
            if (!is_array($notifyOnPoStatus)) {
                $notifyOnPoStatus = explode(',', $notifyOnPoStatus);
            }
            if ($v->getId() && $v->getData("new_order_notifications") == '-1'
                && in_array($po->getUdropshipStatus(), $notifyOnPoStatus)
                && !$po->getData('is_vendor_notified')
            ) {
                $po->setData('is_vendor_notified', 1);
                $shipments = [];
                foreach ($po->getShipmentsCollection() as $_shipment) {
                    if ($_shipment->getUdropshipStatus()!=Source::SHIPMENT_STATUS_CANCELED) {
                        $shipments[] = $_shipment;
                        break;
                    }
                }
                $po->setResendNotificationFlag(count($shipments));
                $this->_poHlp->sendNewPoNotificationEmail($po);
                $po->getResource()->saveAttribute($po, 'is_vendor_notified');
            }
        } catch (\Exception $e) {
            $this->_logger->error($e);
        }
    }


    protected function _correctItemQty($item, $parentItems, $getter='getQty')
    {
        $oItem = $item->getOrderItem();
        $qty = max(1, $oItem->isDummy(true) ? 1 : $item->$getter());
        $oParent = $oItem->getParentItem();
        if ($oParent && $parentItems[$oParent->getId()]) {
            $parent = $parentItems[$oParent->getId()];
            $qty *= max(1, $oParent->isDummy(true) ? 1 : $parent->$getter());
        }
        return $qty;
    }

    protected function _saveQtyCorrections($result)
    {
        $oiQtyReverts = $result->getData('oiQtyReverts');
        $oiQtyUsed    = $result->getData('oiQtyUsed');
        $siQtyCors    = $result->getData('siQtyCors');
        $vpQtyCors    = $result->getData('vpQtyCors');
        foreach ($oiQtyUsed as $_oiQtyUsed) {
            $oItem = $_oiQtyUsed['order_item'];
            $oItem->getResource()->saveAttribute($oItem, 'udpo_qty_used');
        }
        foreach ($oiQtyReverts as $oiQtyRevert) {
            $oItem = $oiQtyRevert['order_item'];
            $oItem->getResource()->saveAttribute($oItem, 'udpo_qty_reverted');
        }
        foreach ($siQtyCors as $siQtyCor) {
            $stockItem = $siQtyCor['stock_item'];
            if ($siQtyCor['qty']!=0) {
                $stockItem->setQty($stockItem->getQty()+$siQtyCor['qty']);
                $stockItem->setIsInStock($stockItem->getQty()>0)->save();
            }
        }
        foreach ($vpQtyCors as $vpQtyCor) {
            $vp = $vpQtyCor['vendor_product'];
            if ($vp->getStockQty() !== '' && null !== $vp->getStockQty() && $vpQtyCor['qty']!=0) {
                $vp->setStockQty($vp->getStockQty()+$vpQtyCor['qty']);
                $vp->save();
            }
        }
        return $this;
    }

    protected function _attachVendorProducts($udpos, $order)
    {
        $sId = $order->getStoreId();
        $isMulti = $this->_hlp->isUdmultiActive();
        $localVid = $this->_hlp->getLocalVendorId($sId);
        $isLocalIfInStock = 'local_if_in_stock'==$this->_hlp->getScopeConfig('udropship/stock/availability', $sId);
        $vIds = $pIds = [];
        foreach ($udpos as $udpo) {
            foreach ($udpo->getAllItems() as $item) {
                $pIds[] = $item->getProductId();
                $vIds[] = $item->getUdropshipVendor();
                $vIds[] = $item->getOrderItem()->getUdropshipVendor();
            }
        }
        $vpCol = $this->_vendorProductFactory->create()->getCollection()
            ->addVendorFilter($vIds)
            ->addProductFilter($pIds);
        $prods = $this->_productFactory->create()->getCollection()
            ->setStoreId($sId)
            ->addIdFilter($pIds)
            ->addAttributeToSelect($this->_quoteConfig->getProductAttributes())
            ->addStoreFilter();
        foreach ($udpos as $udpo) {
            foreach ($udpo->getAllItems() as $item) {
                $oItem = $item->getOrderItem();
                if ($prod = $prods->getItemById($item->getProductId())) {
                    $item->setProduct($prod);
                    $oItem->setProduct($prod);
                }
            }
        }
        foreach ($udpos as $udpo) {
            foreach ($udpo->getAllItems() as $item) {
                $oItem = $item->getOrderItem();
                foreach ($vpCol as $vp) {
                    if ($vp->getVendorId()==$item->getUdropshipVendor()
                        && $item->getProductId()==$vp->getProductId()
                    ) {
                        $item->setVendorProduct($vp);
                    }
                    if ($vp->getVendorId()==$oItem->getUdropshipVendor()
                        && $oItem->getProductId()==$vp->getProductId()
                    ) {
                        $oItem->setVendorProduct($vp);
                    }
                }
            }
        }
        return $this;
    }

    protected $_isPoCancel=false;

    protected function _applyQtyCorrection($oItem, $item, $qty, $result, $revOIQty=false)
    {
        $oiQtyReverts = $result->getData('oiQtyReverts');
        $oiQtyUsed    = $result->getData('oiQtyUsed');
        $siQtyCors    = $result->getData('siQtyCors');
        $vpQtyCors    = $result->getData('vpQtyCors');
        $sId = $oItem->getOrder()->getStoreId();
        $product = $item->getProduct();
        $stockItem = $this->_hlp->getStockItem($product);
        $isMulti = $this->_hlp->isUdmultiActive();
        $localVid = $this->_hlp->getLocalVendorId($sId);
        $isLocalIfInStock = 'local_if_in_stock'==$this->_hlp->getScopeConfig('udropship/stock/availability', $sId);
        $vId = $revOIQty ? $oItem->getUdropshipVendor() : $item->getUdropshipVendor();
        $vp = $revOIQty ? $oItem->getVendorProduct() : $item->getVendorProduct();
        $_oiQtyRevert = $_oiQtyUsed = $_siQtyCor = $_vpQtyCor = null;
        $_qtyUsed = $oItem->getUdpoQtyReverted()+$oItem->getUdpoQtyUsed();
        $_qtyLeft = max(0, $oItem->getQtyOrdered()-$_qtyUsed);
        if ($revOIQty) {
            $qty = min($qty, $_qtyLeft);
            $_oiQtyRevert = $qty;
        } elseif ($oItem->getVendorProduct()==$item->getVendorProduct()) {
            $_oiQtyUsed = -$qty;
            if ($this->_isPoCancel) {
                $_qtyUsed = max(0, min(
                    $oItem->getUdpoQtyUsed()+$_oiQtyUsed,
                    $oItem->getQtyOrdered()-$oItem->getUdpoQtyReverted()
                ));
                $_qtyUsed += $oItem->getUdpoQtyReverted();
                $_qtyLeft = max(0, $oItem->getQtyOrdered()-$_qtyUsed);
            }
            $qty = ($qty<0 ? -1 : 1)*max(0, abs($qty)-$_qtyLeft);
        }
        if ($isMulti) {
            if ($vp && '' !== $vp->getStockQty() && null !== $vp->getStockQty()) {
                $_siQtyCor = $_vpQtyCor = $qty;
            }
        } elseif (!$isLocalIfInStock || $localVid==$vId) {
            $_siQtyCor = $qty;
        }
        if ($_vpQtyCor !== null && $vp) {
            if (!isset($vpQtyCors[$vp->getId()])) {
                $vpQtyCors[$vp->getId()] = [
                    'vendor_product'=>$vp,
                    'qty'=>0
                ];
            }
            $vpQtyCors[$vp->getId()]['qty'] += $_vpQtyCor;
        }
        if ($_siQtyCor !== null && $stockItem) {
            if (!isset($siQtyCors[$stockItem->getId()])) {
                $siQtyCors[$stockItem->getId()] = [
                    'stock_item'=>$stockItem,
                    'qty'=>0
                ];
            }
            $siQtyCors[$stockItem->getId()]['qty'] += $_siQtyCor;
        }
        if ($revOIQty && $_oiQtyRevert !== null) {
            if (!isset($oiQtyReverts[$oItem->getId()])) {
                $oiQtyReverts[$oItem->getId()] = [
                    'order_item'=>$oItem,
                    'qty'=>0
                ];
            }
            $oiQtyReverts[$oItem->getId()]['qty'] += $_oiQtyRevert;
            $oItem->setUdpoQtyReverted(max(0, min(
                $oItem->getUdpoQtyReverted()+$_oiQtyRevert,
                $oItem->getQtyOrdered()-$oItem->getUdpoQtyUsed()
            )));
        }
        if ($_oiQtyUsed !== null) {
            if (!isset($oiQtyUsed[$oItem->getId()])) {
                $oiQtyUsed[$oItem->getId()] = [
                    'order_item'=>$oItem,
                    'qty'=>0
                ];
            }
            $oiQtyUsed[$oItem->getId()]['qty'] += $_oiQtyUsed;
            $oItem->setUdpoQtyUsed(max(0, min(
                $oItem->getUdpoQtyUsed()+$_oiQtyUsed,
                $oItem->getQtyOrdered()-$oItem->getUdpoQtyReverted()
            )));
        }
        $result->setData('oiQtyReverts', $oiQtyReverts);
        $result->setData('oiQtyUsed', $oiQtyUsed);
        $result->setData('siQtyCors', $siQtyCors);
        $result->setData('vpQtyCors', $vpQtyCors);
        return $this;
    }

    protected function _getShipmentPo($shipment, $order)
    {
        $udpo = false;
        if ($shipment->getUdpoId()) {
            $poHlp = $this->_poHlp;
            $poHlp->initOrderUdposCollection($order);
            foreach ($order->getUdposCollection() as $__po) {
                if ($__po->getId() == $shipment->getUdpoId()) {
                    $udpo = $__po;
                    break;
                }
            }
        }
        return $udpo;
    }



}

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

namespace Unirgy\DropshipPo\Controller\Adminhtml\Order\Po;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Store\Model\ScopeInterface;
use Unirgy\DropshipPo\Helper\Data as HelperData;
use Unirgy\DropshipPo\Model\PoFactory;
use Unirgy\Dropship\Helper\Data as DropshipHelperData;
use Unirgy\Dropship\Helper\ProtectedCode;

abstract class AbstractPo extends Action
{
    /**
     * @var PoFactory
     */
    protected $_poFactory;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var HelperData
     */
    protected $_poHlp;

    /**
     * @var TrackFactory
     */
    protected $_shipmentTrackFactory;

    /**
     * @var OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var DropshipHelperData
     */
    protected $_hlp;

    /**
     * @var ProtectedCode
     */
    protected $_hlpPr;

    protected $resultPageFactory;
    protected $_resultRawFactory;
    protected $resultForwardFactory;
    protected $_resultRedirectFactory;
    protected $_logger;

    public function __construct(
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
        $this->_poFactory = $poFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_poHlp = $udpoHelper;
        $this->_shipmentTrackFactory = $shipmentTrackFactory;
        $this->_orderFactory = $orderFactory;
        $this->_hlp = $udropshipHelper;
        $this->_hlpPr = $udropshipHelperProtected;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->_resultRedirectFactory = $resultRedirectFactory;
        $this->_resultRawFactory = $resultRawFactory;
        $this->_logger = $logger;

        parent::__construct($context);
    }

    protected function _getItemQtys()
    {
        $data = $this->getRequest()->getParam('udpo');
        if (isset($data['items'])) {
            $qtys = $data['items'];
        } else {
            $qtys = [];
        }
        return $qtys;
    }
	protected function _getShipmentItemQtys()
    {
        $data = $this->getRequest()->getParam('shipment');
        if (isset($data['items'])) {
            $qtys = $data['items'];
        } else {
            $qtys = [];
        }
        return $qtys;
    }
    protected function _getItemVendors()
    {
        $data = $this->getRequest()->getParam('udpo');
        if (isset($data['vendors'])) {
            $qtys = $data['vendors'];
        } else {
            $qtys = [];
        }
        return $qtys;
    }
    protected function _getItemCosts()
    {
        $data = $this->getRequest()->getParam('udpo');
        if (isset($data['costs'])) {
            $costs = $data['costs'];
        } else {
            $costs = [];
        }
        return $costs;
    }
    protected function _getItemDefaultVendorCosts()
    {
        $data = $this->getRequest()->getParam('udpo');
        if (isset($data['default_vendor_costs'])) {
            $costs = $data['default_vendor_costs'];
        } else {
            $costs = [];
        }
        return $costs;
    }
    protected function _initPo($forSave=true)
    {
        $udpoId = $this->getRequest()->getParam('udpo_id');
        $po = $this->_poFactory->create()->load($udpoId);
        if (!$po->getId()) {
            $this->messageManager->addError(__('This purchase order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        if (!$forSave) {
            $po->getOrder()->setShippingInclTax($po->getShippingAmountIncl());
            $po->getOrder()->setBaseShippingInclTax($po->getBaseShippingAmountIncl());
            $po->getOrder()->setBaseShippingAmount($po->getBaseShippingAmount());
            $po->getOrder()->setShippingAmount($po->getShippingAmount());
            $po->getOrder()->setIsVirtual($po->getIsVirtual());
        }
        $this->_coreRegistry->register('current_udpo', $po);
        $this->_coreRegistry->register('current_order', $po->getOrder());

        return $po;
    }

	protected function _initShipment($udpo, $setQtyShippedFlag)
    {
        $shipment = false;
        if (!$udpo->getId()) {
            $this->messageManager->addError(__('The po no longer exists.'));
            return false;
        }
        if (!$udpo->canCreateShipment()) {
            $this->messageManager->addError(__('Cannot do shipment for the po.'));
            return false;
        }
        $_savedQtys = $this->_getShipmentItemQtys();
        $savedQtys = [];
        $poItems = $udpo->getItemsCollection();
        foreach ($_savedQtys as $_oid => $_sq) {
        	$savedQtys[$poItems->getItemByColumnValue('order_item_id', $_oid)->getId()] = $_sq;
        }
        $udpo->setUdpoNoSplitPoFlag(true);
        $shipment = $this->_poHlp->createShipmentFromPo($udpo, $savedQtys, false, $setQtyShippedFlag);

        $tracks = $this->getRequest()->getPost('tracking');
        if ($tracks) {
            foreach ($tracks as $data) {
                $track = $this->_shipmentTrackFactory->create()
                    ->addData($data);
                $shipment->addTrack($track);
            }
        }

        $this->_coreRegistry->register('current_shipment', $shipment);
        return $shipment;
    }



    protected function checkStockAvailability($order, $vendors, $noError=false)
    {
        if ($this->_hlp->getScopeFlag('udropship/stock/reassign_skip_stockcheck', $order->getStore())) {
            return $this;
        }
        $items = $this->_orderFactory->create()->load($order->getId())->getAllItems();
        foreach ($items as $_item) {
            $_item->setUdropshipExtraStockQty([
                $_item->getUdropshipVendor() => max(
                    $_item->getQtyOrdered()-$_item->getUdpoQtyReverted(), 0
            )]);
        	$_item->setUdpoCreateQty(
                $this->_hlp->getOrderItemById($order, $_item->getId())->getUdpoCreateQty()
            );
        }

        $this->_hlpPr->reassignApplyStockAvailability($items);

        foreach ($items as $item) {
            $this->_hlp->getOrderItemById($order, $item->getId())->setData(
                '_udropship_stock_levels', $item->getData('udropship_stock_levels')
            );
        }

        if (!$noError) {
            $hasOutOfStockError = '';
            foreach ($items as $item) {
                if (!isset($vendors[$item->getId()])) continue;
                if ($item->getProductType()=='configurable') {
                    $children = $item->getChildrenItems() ? $item->getChildrenItems() : $item->getChildren();
                    foreach ($children as $child) {
                        if (!$child->getData("udropship_stock_levels/{$vendors[$item->getId()]['id']}/status")
                            && $child->getUdropshipVendor()!=$vendors[$item->getId()]['id']
                        ) {
                            $hasOutOfStockError .= __(
                                "%1 x %2 is not available at vendor '%3'",
                                $this->_hlp->getItemStockCheckQty($child), $child->getSku(),
                                $this->_hlp->getVendorName($vendors[$item->getId()]['id'])
                            )."\n";
                        }
                        break;
                    }
                } else {
                    if (!$item->getData("udropship_stock_levels/{$vendors[$item->getId()]['id']}/status")
                        && $item->getUdropshipVendor()!=$vendors[$item->getId()]['id']
                    ) {
                        $hasOutOfStockError .= __(
                            "%1 x %2 is not available at vendor '%3'",
                            $this->_hlp->getItemStockCheckQty($item), $item->getSku(),
                            $this->_hlp->getVendorName($vendors[$item->getId()]['id'])
                        )."\n";
                    }
                }
            }
            if (!empty($hasOutOfStockError)) throw new \Exception(trim($hasOutOfStockError));
        }
        return $this;
    }

    protected function __initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = $this->_orderFactory->create()->load($id);

        if (!$order->getId()) {
            $this->messageManager->addError(__('This order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }

        $this->_coreRegistry->register('sales_order', $order);
        $this->_coreRegistry->register('current_order', $order);

        return $order;
    }

    protected function _initOrder()
    {
        $order = $this->__initOrder();

        $hlp   = $this->_hlp;
        $hlpd  = $this->_hlpPr;
        $poHlp = $this->_poHlp;
        $totalCost = 0;
        $totalCostByVendor = [];
        $qtys = $this->_getItemQtys();
        $vendors = $this->_getItemVendors();
        $costs = $this->_getItemCosts();
        $defVendorCosts = $this->_getItemDefaultVendorCosts();
        $vMethods = [];
        if (!$poHlp->checkCreatePoQtys($order, $qtys)) {
            throw new \Exception(__('Cannot create PO with this qtys'));
        }
        $isVirtual = [];
        $udpoCreateQtys = [];
        foreach ($order->getAllItems() as $item) {
            if ($item->isDummy(true)) {
                $parentItem = $item->getParentItem();
                if ($parentItem && !empty($vendors[$parentItem->getId()])) {
                    $item->setUdpoUdropshipVendor($parentItem->getUdpoUdropshipVendor());
                    if ($parentItem->getProductType()=='configurable') {
                        $item->setUdpoBaseCost($parentItem->getUdpoBaseCost());
                    }
                }
                continue;
            }
            if (isset($qtys[$item->getId()]) && abs($qtys[$item->getId()]) < 0.001) continue;
            if (isset($qtys[$item->getId()])) {
                $item->setUdpoCreateQty($qtys[$item->getId()]);
            } else {
                $item->setUdpoCreateQty($poHlp->getOrderItemQtyToUdpo($item));
            }
            if (!empty($vendors[$item->getId()])) {
                $item->setUdpoUdropshipVendor($vendors[$item->getId()]['id']);
            } else {
                $children = $item->getChildrenItems() ? $item->getChildrenItems() : $item->getChildren();
                $item->setUdpoBaseCost($hlp->getItemBaseCost($item));
                if (!empty($children)) {
                    foreach ($children as $child) {
                        $item->setUdpoUdropshipVendor($child->getUdropshipVendor());
                        break;
                    }
                } else {
                    $item->setUdpoUdropshipVendor($item->getUdropshipVendor());
                }
            }
            $udpoVid = $item->getUdpoUdropshipVendor();
            if (!isset($totalCostByVendor[$udpoVid])) {
                $totalCostByVendor[$udpoVid] = 0;
            }
            if (!$item->getHasChildren() || $item->getProductType()=='configurable') {
                $item->setUdpoOrigBaseCost($item->getUdpoBaseCost());
                if (isset($costs[$item->getId()])) {
                    $item->setUdpoBaseCost($costs[$item->getId()]);
                    $item->setUdpoCustomCost(true);
                } elseif ($item->getProductType()=='configurable') {
                    foreach ($item->getChildrenItems() as $child) {
                        if (isset($defVendorCosts[$child->getId()][$udpoVid])) {
                            $child->setUdpoBaseCost($defVendorCosts[$child->getId()][$udpoVid]);
                            $item->setUdpoBaseCost($defVendorCosts[$child->getId()][$udpoVid]);
                        }
                    }
                } elseif (isset($defVendorCosts[$item->getId()][$udpoVid])) {
                    $item->setUdpoBaseCost($defVendorCosts[$item->getId()][$udpoVid]);
                }
                $totalCostByVendor[$udpoVid] += $item->getUdpoCreateQty()*$item->getUdpoBaseCost();
                $totalCost += $item->getUdpoCreateQty()*$item->getUdpoBaseCost();
            } else {
                foreach ($item->getChildrenItems() as $child) {
                    $child->setUdpoBaseCost($hlp->getItemBaseCost($child));
                    $child->setUdpoOrigBaseCost($child->getUdpoBaseCost());
                    if (isset($costs[$child->getId()])) {
                        $child->setUdpoBaseCost($costs[$child->getId()]);
                        $child->setUdpoCustomCost(true);
                    } elseif (isset($defVendorCosts[$child->getId()][$udpoVid])) {
                        $child->setUdpoBaseCost($defVendorCosts[$child->getId()][$udpoVid]);
                    }
                    $_costToAdd = $child->getUdpoBaseCost()*$child->getQtyOrdered()*$item->getUdpoCreateQty()/$item->getQtyOrdered();
                    $totalCostByVendor[$udpoVid] += $_costToAdd;
                    $totalCost += $_costToAdd;
                }
            }
            if (empty($vMethods[$udpoVid])) {
                $vMethods[$udpoVid] = [];
            }
            if (empty($udpoCreateQtys[$udpoVid])) {
                $udpoCreateQtys[$udpoVid] = 0;
            }
            $udpoCreateQtys[$udpoVid] += $item->getUdpoCreateQty();
            if (empty($isVirtual[$udpoVid])) {
                $isVirtual[$udpoVid] = true;
            }
            if (!$item->getIsVirtual()) {
                $isVirtual[$udpoVid] = false;
            }
        }
        //if (!empty($vendors)) $this->checkStockAvailability($order, $vendors);
        $this->checkStockAvailability($order, $vendors, empty($vendors));
        $__vMethods = $vMethods;
        $vMethods = [];
        foreach ($__vMethods as $vId => $vMethod) {
            if ($udpoCreateQtys[$vId]>0) {
                $vMethods[$vId] = !$isVirtual[$vId] ? $vMethod : false;
            }
        }
        $hlp->initVendorShippingMethodsForHtmlSelect($order, $vMethods);
        $order->setTotalCostByVendor($totalCostByVendor);
        $order->setTotalCost($totalCost);
        $orderVendorRates = $hlpd->getOrderVendorRates($order);

        $totalShipping = 0;
        $udpoVendorRates = $this->getRequest()->getParam('vendor_rates', []);
        $_udpoVendorRates = [];
        $__saDivider = count($vMethods)>0 ? 1/count($vMethods) : 1;
        foreach ($vMethods as $vId => $vMethod) {
            if (!isset($udpoVendorRates[$vId])) {
                if (isset($orderVendorRates[$vId])) {
                    $udpoVendorRates[$vId] = $orderVendorRates[$vId];
                } else {
                    $udpoVendorRates[$vId]['price'] = $__saDivider*$order->getBaseShippingAmount();
                }
            }
            $udpoVendorRates[$vId]['price'] = !empty($udpoVendorRates[$vId]['price']) && $vMethod ? $udpoVendorRates[$vId]['price'] : 0;
            $udpoVendorRates[$vId]['udpo_methods'] = $vMethod;
            $_udpoVendorRates[$vId] = $udpoVendorRates[$vId];

            $totalShipping += $udpoVendorRates[$vId]['price'];
        }
        unset($vRate);
        $order->setTotalShippingAmount($totalShipping);
        $order->setUdpoVendorRates($_udpoVendorRates);

        $this->_coreRegistry->register('is_udpo_page', true);

        $this->_eventManager->dispatch('udpo_adminhtml_order_init_after', ['order'=>$order]);

        return $order;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Unirgy_DropshipPo::udpo')
            && (
                !in_array($this->getRequest()->getActionName(), ['editCosts', 'saveCosts'])
                || $this->_authorization->isAllowed('Unirgy_DropshipPo::action_edit_cost')
            );
    }
    protected function _initAction()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Unirgy_DropshipPo::udpo');
        $resultPage->getConfig()->getTitle()->prepend(__('Purchase Orders'));
        $resultPage->addBreadcrumb(__('Sales'), __('Sales'));
        $resultPage->addBreadcrumb(__('Dropship'), __('Dropship'));
        $resultPage->addBreadcrumb(__('Purchase Orders'), __('Purchase Orders'));
        return $resultPage;

    }
}

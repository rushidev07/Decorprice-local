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

namespace Unirgy\DropshipPo\Block\Vendor\Po;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Block\Items\AbstractItems;
use Magento\Shipping\Model\Config;
use Unirgy\DropshipPo\Model\PoFactory;
use Unirgy\Dropship\Helper\Data as HelperData;
use Unirgy\Dropship\Helper\Item;
use Zend\Json\Json;

class Info extends AbstractItems
{
    /**
     * @var HelperData
     */
    protected $_hlp;

    /**
     * @var PoFactory
     */
    protected $_poFactory;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var Item
     */
    protected $_iHlp;

    /**
     * @var \Magento\Shipping\Model\CarrierFactory
     */
    protected $_carrierFactory;

    /**
     * @var Config
     */
    protected $_shippingConfig;

    public function __construct(
        \Magento\Shipping\Model\CarrierFactory $carrierFactory,
        \Magento\Shipping\Model\Config $shippingConfig,
        Context $context,
        HelperData $helperData, 
        PoFactory $modelPoFactory, 
        Registry $frameworkRegistry, 
        Item $helperItem, 
        array $data = [])
    {
        $this->_hlp = $helperData;
        $this->_poFactory = $modelPoFactory;
        $this->_coreRegistry = $frameworkRegistry;
        $this->_iHlp = $helperItem;
        $this->_shippingConfig = $shippingConfig;
        $this->_carrierFactory = $carrierFactory;

        parent::__construct($context, $data);
    }

    public function getVendor()
    {
        return ObjectManager::getInstance()->get('Unirgy\Dropship\Model\Session')->getVendor();
    }
    public function isShowTotals()
    {
        return $this->_hlp->getVendorFallbackFlagField(
            $this->getVendor(),
            'portal_show_totals', 'udropship/vendor/portal_show_totals'
        );
    }

    public function getPo()
    {
        if (!$this->hasData('po')) {
            $id = (int)$this->getRequest()->getParam('id');
            $po = $this->_poFactory->create()->load($id);
            $this->_coreRegistry->register('current_order', $po->getOrder());
            $this->setData('po', $po);
            $this->_hlp->assignVendorSkus($po);
            $this->_iHlp->hideVendorIdOption($po);
            if ($this->isShowTotals()) {
                $this->_iHlp->initPoTotals($po);
            }
        }
        return $this->getData('po');
    }

    public function getRemainingWeight()
    {
        $weight = 0;
        $parentItems = [];
        foreach ($this->getPo()->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();

            $children = $orderItem->getChildrenItems() ? $orderItem->getChildrenItems() : $orderItem->getChildren();
            if ($children) {
                $parentItems[$orderItem->getId()] = $item;
            }
            $__qty = $item->getQtyToShip();
            if ($orderItem->isDummy(true)) {
                if (($_parentItem = $orderItem->getParentItem())) {
                    $__qty = $orderItem->getQtyOrdered()/$_parentItem->getQtyOrdered();
                    if (@$parentItems[$_parentItem->getId()]) {
                        $__qty *= $parentItems[$_parentItem->getId()]->getQty();
                    }
                } else {
                    $__qty = max(1,$item->getQty());
                }
            }

            if ($orderItem->getParentItem()) {
                $weightType = $orderItem->getParentItem()->getProductOptionByCode('weight_type');
                if (null !== $weightType && !$weightType) {
                    $weight += $item->getWeight()*$__qty;
                }
            } else {
                $weightType = $orderItem->getProductOptionByCode('weight_type');
                if (null === $weightType || $weightType) {
                    $weight += $item->getWeight()*$__qty;
                }
            }
        }
        return $weight;
    }
    
    public function getRemainingShippingAmount()
    {
        $sa = 0;
        $po = $this->getPo();
        foreach ($po->getShipmentsCollection() as $_s) {
            $sa += $_s->getBaseShippingAmount();
        }
        return max(0,$po->getBaseShippingAmount()-$sa);
    }

    public function getRemainingValue()
    {
        $value = 0;
        foreach ($this->getPo()->getAllItems() as $item) {
            $value += $item->getPrice()*$item->getQtyToShip();
        }
        return $value;
    }

    public function getPoItemsJson($po)
    {
        $items = [];
        foreach ($po->getAllItems() as $item) {
            if ($item->getOrderItem()->isDummy(true)) continue;
            $items[$item->getId()] = [
                'product_url' => $item->getProduct() && $item->getProduct()->getProductUrl() ? $item->getProduct()->getProductUrl() : '',
                'order_item_id'=> $item->getOrderItem()->getId(),
                'item_id'      => $item->getId(),
                'is_dummy'     => (int)$item->getOrderItem()->isDummy(true),
                'is_virtual'   => (int)$item->getIsVirtual(),
                'qty_shipped'  => (int)$item->getQtyShipped(),
                'qty_to_ship'  => (int)$item->getQtyToShip(),
                'qty_canceled' => (int)$item->getQtyCanceled(),
                'weight' => $item->getWeight(),
                'price' => $item->getPrice(),
            ];
        }
        return $this->_hlp->jsonEncode($items);
    }

    public function getCarriers()
    {
        $carriers = [];
        $carrierInstances = $this->_shippingConfig->getAllCarriers(
            $this->getPo()->getStoreId()
        );
        $carriers[''] = __('* Use PO carrier *');
        $carriers['custom'] = __('Custom Value');
        foreach ($carrierInstances as $code => $carrier) {
            if ($carrier->isTrackingAvailable()) {
                $carriers[$code] = $carrier->getConfigData('title');
            }
        }
        return $carriers;
    }

    public function getCarrierTitle($code)
    {
        if ($carrier = $this->_carrierFactory->create($code)) {
            return $carrier->getConfigData('title');
        }
        else {
            return __('Custom Value');
        }
        return false;
    }
    
}

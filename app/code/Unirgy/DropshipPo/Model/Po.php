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

namespace Unirgy\DropshipPo\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Sales\Model\AbstractModel;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\InvoiceFactory;
use Magento\Sales\Model\ResourceModel\Order\Status\History as OrderStatusHistoryResource;
use Unirgy\DropshipPo\Helper\Data as UdpoHelper;
use Unirgy\DropshipPo\Model\Po\Comment;
use Unirgy\DropshipPo\Model\Po\CommentFactory;
use Unirgy\DropshipPo\Model\Po\Item;
use Unirgy\DropshipPo\Model\Po\ItemFactory;
use Unirgy\Dropship\Helper\Data as HelperData;
use Unirgy\Dropship\Model\Source as ModelSource;
use Magento\Sales\Model\EntityInterface;
use Unirgy\DropshipPo\Api\Data\UdpoInterface;

class Po extends AbstractModel implements UdpoInterface, EntityInterface
{
    protected $entityType = 'udpo_po';
    /**
     * @var OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var HelperData
     */
    protected $_hlp;

    /**
     * @var ItemFactory
     */
    protected $_itemFactory;

    /**
     * @var CommentFactory
     */
    protected $_poCommentFactory;

    protected $_poHlp;
    /**
     * @var OrderStatusHistoryResource
     */
    private $orderStatusHistoryResource;

    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        OrderFactory $modelOrderFactory,
        UdpoHelper $udpoHelper,
        OrderStatusHistoryResource $orderStatusHistoryResource,
        HelperData $helperData,
        ItemFactory $itemFactory,
        CommentFactory $poCommentFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->_orderFactory = $modelOrderFactory;
        $this->_hlp = $helperData;
        $this->_poHlp = $udpoHelper;
        $this->_itemFactory = $itemFactory;
        $this->_poCommentFactory = $poCommentFactory;

        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $resource, $resourceCollection, $data);
        $this->orderStatusHistoryResource = $orderStatusHistoryResource;
    }

    protected $_items;
    protected $_order;
    protected $_comments;
    protected $_vendorComments;
    protected $_shipments;
    protected $_invoices;

    protected $_eventPrefix = 'udpo_po';
    protected $_eventObject = 'po';

    protected $_commentsChanged = false;

    protected function _construct()
    {
        $this->_init('Unirgy\DropshipPo\Model\ResourceModel\Po');
    }

    public function loadByIncrementId($incrementId)
    {
        $ids = $this->getCollection()
            ->addAttributeToFilter('increment_id', $incrementId)
            ->getAllIds();

        if (!empty($ids)) {
            reset($ids);
            $this->load(current($ids));
        }
        return $this;
    }


    /**
     * Declare order for shipment
     *
     * @param   Order $order
     * @return  Order\Shipment
     */
    public function setOrder(Order $order)
    {
        $this->_order = $order;
        $this->setOrderId($order->getId())
            ->setStoreId($order->getStoreId());
        return $this;
    }


    /**
     * Retrieve hash code of current order
     *
     * @return string
     */
    public function getProtectCode()
    {
        return (string)$this->getOrder()->getProtectCode();
    }

    /**
     * Retrieve the order the shipment for created for
     *
     * @return Order
     */
    public function getOrder()
    {
        if (!$this->_order instanceof Order) {
            $this->_order = $this->_orderFactory->create()->load($this->getOrderId());
        }
        return $this->_order;
    }

    public function getEntityType()
    {
        return $this->entityType;
    }
    public function getIncrementId()
    {
        return $this->getData('increment_id');
    }

    protected $_stockPo;
    public function getStockPo()
    {
        if ($this->_hlp->isModuleActive('Unirgy_DropshipStockPo')) {
            if (null === $this->_stockPo && $this->getUstockpoId()) {
                $this->_stockPo = $this->_hlp->createObj('Unirgy\DropshipStockPo\Model\Po')->load($this->getUstockpoId());
            }
        }
        return $this->_stockPo;
    }
    public function setStockPo($stockPo)
    {
        $this->_stockPo = $stockPo;
        return $this;
    }

    /**
     * Retrieve billing address
     *
     * @return Order\Address
     */
    public function getBillingAddress()
    {
        return $this->getOrder()->getBillingAddress();
    }

    /**
     * Retrieve shipping address
     *
     * @return Order\Address
     */
    public function getShippingAddress()
    {
        return $this->getOrder()->getShippingAddress();
    }

    public function register()
    {
        return $this;
    }

    public function cancel()
    {
        foreach ($this->getAllItems() as $item) {
            $item->cancel();
        }
        $this->_eventManager->dispatch('udpo_po_cancel', ['order'=>$this->getOrder(), 'udpo'=>$this]);
        return $this;
    }

    public function hasShippedItem()
    {
        foreach ($this->getAllItems() as $item) {
            if ($item->getQtyShipped() > 0) {
                return true;
            }
        }
    }

    public function hasCanceledItem()
    {
        foreach ($this->getAllItems() as $item) {
            if ($item->getQtyCanceled() > 0) {
                return true;
            }
        }
    }

    public function hasItemToShip()
    {
        foreach ($this->getAllItems() as $item) {
            if ($item->getQtyToShip() > 0 && !$item->getOrderItem()->getIsVirtual()) {
                return true;
            }
        }
    }

    public function getItemsCollection()
    {
        if (empty($this->_items)) {
            $this->_items = $this->_itemFactory->create()->getCollection()
                ->setPoFilter($this->getId());

            if ($this->getId()) {
                foreach ($this->_items as $item) {
                    $item->setPo($this);
                }
            }
        }
        return $this->_items;
    }

    public function getItems()
    {
        return $this->getItemsCollection()->getItems();
    }
    public function getComments()
    {
        return $this->getCommentsCollection()->getItems();
    }
    public function getInvoices()
    {
        return $this->getInvoicesCollection()->getItems();
    }
    public function getShipments()
    {
        return $this->getShipmentsCollection()->getItems();
    }

    public function getAllItems()
    {
        $items = [];
        foreach ($this->getItemsCollection() as $item) {
            if (!$item->isDeleted()) {
                $items[] =  $item;
            }
        }
        return $items;
    }

    public function getItemById($itemId)
    {
        foreach ($this->getItemsCollection() as $item) {
            if ($item->getId()==$itemId) {
                return $item;
            }
        }
        return false;
    }

    public function addItem(Item $item)
    {
        $item->setPo($this)
            ->setParentId($this->getId())
            ->setStoreId($this->getStoreId());
        if (!$item->getId()) {
            $this->getItemsCollection()->addItem($item);
        }
        $this->_hasDataChanges = true;
        return $this;
    }

    public function getUdropshipStatusName($status=null)
    {
        if (is_null($status)) {
            $status = $this->getUdropshipStatus();
        }
        $statuses = $this->_poHlp->src()->setPath('po_statuses')->toOptionHash();
        return isset($statuses[$status]) ? $statuses[$status] : (in_array($status, $statuses) ? $status : 'Unknown');
    }

    public function addComment($comment, $isVendorNotified=false, $visibleToVendor=false)
    {
        $this->_commentsChanged = true;
        if (!($comment instanceof Comment)) {
            $comment = $this->_poCommentFactory->create()
                ->setComment($comment)
                ->setIsVendorNotified($isVendorNotified)
                ->setIsVisibleToVendor($visibleToVendor)
                ->setUdropshipStatus($this->getUdropshipStatusName());
        }
        if ($this->getUseCommentUsername()) {
            $comment->setUsername($this->getUseCommentUsername());
        }
        $comment->setPo($this)
            ->setParentId($this->getId())
            ->setStoreId($this->getStoreId());
        if (!$comment->getId()) {
            $this->getOrder()->addStatusHistoryComment(__("Purchase Order # %1: (%2)\n%3",
                $this->getIncrementId(), $this->getUdropshipStatusName(), $comment->getComment()
            ));
            $this->getCommentsCollection()->addItem($comment);
        }
        $this->_hasDataChanges = true;
        return $this;
    }

    public function getCommentsCollection($reload=false)
    {
        if (is_null($this->_comments) || $reload) {
            $this->_comments = $this->_poCommentFactory->create()->getCollection()
                ->setPoFilter($this->getId())
                ->setCreatedAtOrder();

            /**
             * When shipment created with adding comment, comments collection must be loaded before we added this comment.
             */
            $this->_comments->load();

            if ($this->getId()) {
                foreach ($this->_comments as $comment) {
                    $comment->setPo($this);
                }
            }
        }
        return $this->_comments;
    }

    public function getVendorCommentsCollection($reload=false)
    {
        if (is_null($this->_vendorComments) || $reload) {
            $this->_vendorComments = $this->_poCommentFactory->create()->getCollection()
                ->setPoFilter($this->getId())
                ->addFieldToFilter('is_visible_to_vendor', 1)
                ->setCreatedAtOrder();

            /**
             * When shipment created with adding comment, comments collection must be loaded before we added this comment.
             */
            $this->_vendorComments->load();

            if ($this->getId()) {
                foreach ($this->_vendorComments as $comment) {
                    $comment->setPo($this);
                }
            }
        }
        return $this->_vendorComments;
    }

    public function canCreateShipment()
    {
        foreach ($this->getAllItems() as $item) {
            if ($item->getQtyToShip()>0 && !$item->getIsVirtual()) {
                return true;
            }
        }
        return false;
    }

	public function canCreateInvoice()
    {
    	$canFlag = false;
        foreach ($this->getAllItems() as $item) {
            $oItemIds[] = $item->getOrderItemId();
            if ($item->getQtyToInvoice()>0) {
                $canFlag = true;
            }
        }
        return $canFlag && !$this->getResource()->hasExternalInvoice($this, $oItemIds);
    }

    public function canInvoiceShipment($shipment)
    {
        if ($this->getId()!=$shipment->getUdpoId()) {
            return false;
        }
        $shipInvoiceMatch = true;
        $oItemIds = [];
        foreach ($shipment->getAllItems() as $sItem) {
            $oItem = $sItem->getOrderItem();
            $oItemIds[] = $oItem->getId();
            if ($oItem->isDummy(true) != $oItem->isDummy()) {
                return false;
            }
            if (!($poItem = $this->getItemById($sItem->getUdpoItemId()))) {
                return false;
            }
            if ($poItem->getQtyToInvoice()<$sItem->getQtyShipped()) {
                return false;
            }
        }
        $hasExternalInvoice = false;
        foreach ($this->getOrder()->getInvoiceCollection() as $oInvoice) {
            if ($oInvoice->getUdpoId() != $this->getId()) {
                foreach ($oInvoice->getAllItems() as $iItem) {
                    if (in_array($iItem->getOrderItemId(), $oItemIds)) {
                        $hasExternalInvoice = true;
                    }
                }
            }
        }
        return !$hasExternalInvoice
            && !$this->getResource()->hasExternalInvoice($this, $oItemIds)
            && !$this->getOrder()->getInvoiceCollection()->getItemByColumnValue('shipment_id', $shipment->getId());
    }

    public function canCancel()
    {
        foreach ($this->getAllItems() as $item) {
            if ($item->getQtyToCancel()>0) {
                return true;
            }
        }
        return false;
    }

    public function isShipmentsShipped($all=true)
    {
        if ($this->getIsVirtual()) return true;
        $shipped = false;
        foreach ($this->getShipmentsCollection() as $shipment) {
            if ($shipment->getUdropshipStatus()==ModelSource::SHIPMENT_STATUS_CANCELED) {
                continue;
            }
            if ($shipment->getUdropshipStatus()!=ModelSource::SHIPMENT_STATUS_SHIPPED
                && $shipment->getUdropshipStatus()!=ModelSource::SHIPMENT_STATUS_DELIVERED
                && $all
            ) {
                return false;
            } elseif ($shipment->getUdropshipStatus()==ModelSource::SHIPMENT_STATUS_SHIPPED
                || $shipment->getUdropshipStatus()==ModelSource::SHIPMENT_STATUS_DELIVERED
            ) {
                $shipped = true;
            }
        }
        return $shipped;
    }

    public function isShipmentsDelivered()
    {
        if ($this->getIsVirtual()) return true;
        $delivered = false;
        foreach ($this->getShipmentsCollection() as $shipment) {
            if ($shipment->getUdropshipStatus()==ModelSource::SHIPMENT_STATUS_CANCELED) {
                continue;
            }
            if ($shipment->getUdropshipStatus()!=ModelSource::SHIPMENT_STATUS_DELIVERED) {
                return false;
            } elseif ($shipment->getUdropshipStatus()==ModelSource::SHIPMENT_STATUS_DELIVERED) {
                $delivered = true;
            }
        }
        return $delivered;
    }

    /**
     * Before object save
     *
     * @return Order\Shipment
     */
    public function beforeSave()
    {
        if ((!$this->getId() || null !== $this->_items) && !count($this->getAllItems())) {
            throw new \Exception(
                __('Cannot create an empty purchase order.')
            );
        }

        if (!$this->getOrderId() && $this->getOrder()) {
            $this->setOrderId($this->getOrder()->getId());
            $this->setShippingAddressId($this->getOrder()->getShippingAddress()->getId());
        }
        if (!$this->getUstockpoId() && $this->getStockPo()) {
            $this->setUstockpoId($this->getStockPo()->getId());
        }

        return parent::beforeSave();
    }

    public function getShipmentsCollection($forceReload=false)
    {
        if (is_null($this->_shipments) || $forceReload) {
            if ($this->getId()) {
                $this->_shipments = $this->_hlp->createObj('\Magento\Sales\Model\Order\Shipment')->getCollection()
                    ->addAttributeToFilter('udpo_id', $this->getId())
                    ->load();
                foreach ($this->_shipments as $s) {
                    $s->setUdpo($this);
                    $s->setOrder($this->getOrder());
                }
            } else {
                return false;
            }
        }
        return $this->_shipments;
    }

    public function getBaseShippingAmountLeft()
    {
        $usedBaseSa = 0;
        foreach ($this->getShipmentsCollection() as $_s) {
        	if ($_s->getUdropshipStatus()==ModelSource::SHIPMENT_STATUS_CANCELED) {
                continue;
            }
            $usedBaseSa += $_s->getBaseShippingAmount();
        }
        return max(0,$this->getBaseShippingAmount()-$usedBaseSa);
    }

    public function getShippingAmountLeft()
    {
        $usedSa = 0;
        foreach ($this->getShipmentsCollection() as $_s) {
        	if ($_s->getUdropshipStatus()==ModelSource::SHIPMENT_STATUS_CANCELED) {
                continue;
            }
            $usedSa += $_s->getShippingAmount();
        }
        return max(0,$this->getShippingAmount()-$usedSa);
    }

	public function getRemainingWeight()
    {
        $weight = 0;
        foreach ($this->getAllItems() as $item) {
            $weight += $item->getWeight()*$item->getQtyToShip();
        }
        return $weight;
    }

    public function getRemainingValue()
    {
        $value = 0;
        foreach ($this->getAllItems() as $item) {
            $value += $item->getPrice()*$item->getQtyToShip();
        }
        return $value;
    }

    public function getInvoicesCollection($forceReload=false)
    {
        if (is_null($this->_invoices) || $forceReload) {
            if ($this->getId()) {
                $this->_invoices = $this->_hlp->createObj('\Magento\Sales\Model\Order\Invoice')->getCollection()
                    ->addAttributeToFilter('udpo_id', $this->getId())
                    ->load();
                foreach ($this->_invoices as $i) {
                    $i->setUdpo($this);
                }
            } else {
                return false;
            }
        }
        return $this->_invoices;
    }

    public function afterSave()
    {
        if (null !== $this->_items) {
            foreach ($this->_items as $item) {
                $item->save();
            }
        }

        $this->saveComments();

        return parent::afterSave();
    }

    public function saveComments()
    {
        if ($this->_commentsChanged) {
            $this->getCommentsCollection()->save();
            $order = $this->getOrder();
            if (null !== $order->getStatusHistories()) {
                /** @var \Magento\Sales\Model\Order\Status\History $statusHistory */
                foreach ($order->getStatusHistories() as $statusHistory) {
                    $statusHistory->setParentId($order->getId());
                    $statusHistory->setOrder($order);
                    $this->orderStatusHistoryResource->save($statusHistory);
                }
            }
        }
        return $this;
    }

    public function getStore()
    {
        return $this->getOrder()->getStore();
    }

    public function getVendorName()
    {
        return $this->getVendor()->getVendorName();
    }

    public function getVendor()
    {
        return $this->_hlp->getVendor($this->getUdropshipVendor());
    }

    public function getStockVendorName()
    {
        return $this->getStockVendor()->getVendorName();
    }

    public function getStockVendor()
    {
        return $this->_hlp->getVendor($this->getUstockVendor());
    }

    /**
     * Returns billing_address_id
     *
     * @return int
     */
    public function getBillingAddressId()
    {
        return $this->getData(UdpoInterface::BILLING_ADDRESS_ID);
    }

    /**
     * Returns created_at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(UdpoInterface::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(UdpoInterface::CREATED_AT, $createdAt);
    }

    /**
     * Returns customer_id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->getData(UdpoInterface::CUSTOMER_ID);
    }

    /**
     * Returns email_sent
     *
     * @return int
     */
    public function getEmailSent()
    {
        return $this->getData(UdpoInterface::EMAIL_SENT);
    }

    /**
     * Returns order_id
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->getData(UdpoInterface::ORDER_ID);
    }

    /**
     * Returns shipment_status
     *
     * @return int
     */
    public function getUdropshipStatus()
    {
        return $this->getData(UdpoInterface::UDROPSHIP_STATUS);
    }

    /**
     * Returns shipping_address_id
     *
     * @return int
     */
    public function getShippingAddressId()
    {
        return $this->getData(UdpoInterface::SHIPPING_ADDRESS_ID);
    }

    /**
     * Returns store_id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getData(UdpoInterface::STORE_ID);
    }

    /**
     * Returns total_qty
     *
     * @return float
     */
    public function getTotalQty()
    {
        return $this->getData(UdpoInterface::TOTAL_QTY);
    }

    /**
     * Returns total_weight
     *
     * @return float
     */
    public function getTotalWeight()
    {
        return $this->getData(UdpoInterface::TOTAL_WEIGHT);
    }

    /**
     * Returns updated_at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(UdpoInterface::UPDATED_AT);
    }

    /**
     * Sets items
     *
     * @codeCoverageIgnore
     *
     * @param mixed $items
     * @return $this
     */
    public function setItems($items)
    {
        return $this->setData(UdpoInterface::ITEMS, $items);
    }

    /**
     * Sets comments
     *
     * @param \Unirgy\DropshipPo\Api\Data\UdpoCommentInterface[] $comments
     * @return $this
     */
    public function setComments($comments = null)
    {
        return $this->setData(UdpoInterface::COMMENTS, $comments);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($id)
    {
        return $this->setData(UdpoInterface::STORE_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setTotalWeight($totalWeight)
    {
        return $this->setData(UdpoInterface::TOTAL_WEIGHT, $totalWeight);
    }

    /**
     * {@inheritdoc}
     */
    public function setTotalQty($qty)
    {
        return $this->setData(UdpoInterface::TOTAL_QTY, $qty);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmailSent($emailSent)
    {
        return $this->setData(UdpoInterface::EMAIL_SENT, $emailSent);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderId($id)
    {
        return $this->setData(UdpoInterface::ORDER_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId($id)
    {
        return $this->setData(UdpoInterface::CUSTOMER_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingAddressId($id)
    {
        return $this->setData(UdpoInterface::SHIPPING_ADDRESS_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setBillingAddressId($id)
    {
        return $this->setData(UdpoInterface::BILLING_ADDRESS_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setUdropshipStatus($udropshipStatus)
    {
        return $this->setData(UdpoInterface::UDROPSHIP_STATUS, $udropshipStatus);
    }

    /**
     * {@inheritdoc}
     */
    public function setIncrementId($id)
    {
        return $this->setData(UdpoInterface::INCREMENT_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($timestamp)
    {
        return $this->setData(UdpoInterface::UPDATED_AT, $timestamp);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Unirgy\DropshipPo\Api\Data\UdpoExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Unirgy\DropshipPo\Api\Data\UdpoExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Unirgy\DropshipPo\Api\Data\UdpoExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}

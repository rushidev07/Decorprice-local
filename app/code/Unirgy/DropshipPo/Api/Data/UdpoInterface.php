<?php

namespace Unirgy\DropshipPo\Api\Data;

interface  UdpoInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /*
     * Entity ID.
     */
    const ENTITY_ID = 'entity_id';
    /*
     * Store ID.
     */
    const STORE_ID = 'store_id';
    /*
     * Total weight.
     */
    const TOTAL_WEIGHT = 'total_weight';
    /*
     * Total quantity.
     */
    const TOTAL_QTY = 'total_qty';
    /*
     * Email sent flag.
     */
    const EMAIL_SENT = 'email_sent';
    /*
     * Order ID.
     */
    const ORDER_ID = 'order_id';
    /*
     * Customer ID.
     */
    const CUSTOMER_ID = 'customer_id';
    /*
     * Shipping address ID.
     */
    const SHIPPING_ADDRESS_ID = 'shipping_address_id';
    /*
     * Billing address ID.
     */
    const BILLING_ADDRESS_ID = 'billing_address_id';
    /*
     * Udpo status.
     */
    const UDROPSHIP_STATUS = 'udropship_status';
    /*
     * Increment ID.
     */
    const INCREMENT_ID = 'increment_id';
    /*
     * Created-at timestamp.
     */
    const CREATED_AT = 'created_at';
    /*
     * Updated-at timestamp.
     */
    const UPDATED_AT = 'updated_at';
    /*
     * Items.
     */
    const ITEMS = 'items';
    /*
     * Comments.
     */
    const COMMENTS = 'comments';

    /**
     * Gets the billing address ID for the shipment.
     *
     * @return int|null Billing address ID.
     */
    public function getBillingAddressId();

    /**
     * Gets the created-at timestamp for the shipment.
     *
     * @return string|null Created-at timestamp.
     */
    public function getCreatedAt();

    /**
     * Sets the created-at timestamp for the shipment.
     *
     * @param string $createdAt timestamp
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Gets the customer ID for the shipment.
     *
     * @return int|null Customer ID.
     */
    public function getCustomerId();

    /**
     * Gets the email-sent flag value for the shipment.
     *
     * @return int|null Email-sent flag value.
     */
    public function getEmailSent();

    /**
     * Gets the ID for the shipment.
     *
     * @return int|null Shipment ID.
     */
    public function getEntityId();

    /**
     * Sets entity ID.
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * Gets the increment ID for the shipment.
     *
     * @return string|null Increment ID.
     */
    public function getIncrementId();

    /**
     * Gets the order ID for the shipment.
     *
     * @return int Order ID.
     */
    public function getOrderId();

    /**
     * Gets the udpo status.
     *
     * @return int|null Udpo status.
     */
    public function getUdropshipStatus();

    /**
     * Gets the shipping address ID for the shipment.
     *
     * @return int|null Shipping address ID.
     */
    public function getShippingAddressId();

    /**
     * Gets the store ID for the shipment.
     *
     * @return int|null Store ID.
     */
    public function getStoreId();

    /**
     * Gets the total quantity for the shipment.
     *
     * @return float|null Total quantity.
     */
    public function getTotalQty();

    /**
     * Gets the total weight for the shipment.
     *
     * @return float|null Total weight.
     */
    public function getTotalWeight();

    /**
     * Gets the updated-at timestamp for the shipment.
     *
     * @return string|null Updated-at timestamp.
     */
    public function getUpdatedAt();

    /**
     * Gets the items for the shipment.
     *
     * @return \Unirgy\DropshipPo\Api\Data\UdpoItemInterface[] Array of items.
     */
    public function getItems();

    /**
     * Sets the items for the shipment.
     *
     * @param \Unirgy\DropshipPo\Api\Data\UdpoItemInterface[] $items
     * @return $this
     */
    public function setItems($items);

    /**
     * Gets the comments for the shipment.
     *
     * @return \Unirgy\DropshipPo\Api\Data\UdpoCommentInterface[] Array of comments.
     */
    public function getComments();

    /**
     * Sets the comments for the shipment.
     *
     * @param \Unirgy\DropshipPo\Api\Data\UdpoCommentInterface[] $comments
     * @return $this
     */
    public function setComments($comments = null);

    /**
     * Sets the store ID for the shipment.
     *
     * @param int $id
     * @return $this
     */
    public function setStoreId($id);

    /**
     * Sets the total weight for the shipment.
     *
     * @param float $totalWeight
     * @return $this
     */
    public function setTotalWeight($totalWeight);

    /**
     * Sets the total quantity for the shipment.
     *
     * @param float $qty
     * @return $this
     */
    public function setTotalQty($qty);

    /**
     * Sets the email-sent flag value for the shipment.
     *
     * @param int $emailSent
     * @return $this
     */
    public function setEmailSent($emailSent);

    /**
     * Sets the order ID for the shipment.
     *
     * @param int $id
     * @return $this
     */
    public function setOrderId($id);

    /**
     * Sets the customer ID for the shipment.
     *
     * @param int $id
     * @return $this
     */
    public function setCustomerId($id);

    /**
     * Sets the shipping address ID for the shipment.
     *
     * @param int $id
     * @return $this
     */
    public function setShippingAddressId($id);

    /**
     * Sets the billing address ID for the shipment.
     *
     * @param int $id
     * @return $this
     */
    public function setBillingAddressId($id);

    /**
     * Sets the udpo status.
     *
     * @param int $udropshipStatus
     * @return $this
     */
    public function setUdropshipStatus($udropshipStatus);

    /**
     * Sets the increment ID for the shipment.
     *
     * @param string $id
     * @return $this
     */
    public function setIncrementId($id);

    /**
     * Sets the updated-at timestamp for the shipment.
     *
     * @param string $timestamp
     * @return $this
     */
    public function setUpdatedAt($timestamp);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Unirgy\DropshipPo\Api\Data\UdpoExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Unirgy\DropshipPo\Api\Data\UdpoExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Unirgy\DropshipPo\Api\Data\UdpoExtensionInterface $extensionAttributes);
}
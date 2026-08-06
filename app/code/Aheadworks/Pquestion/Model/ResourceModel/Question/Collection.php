<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Model\ResourceModel\Question;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Catalog\Model\ResourceModel\ConfigFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Entity\AttributeFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\DB\Select;
use Aheadworks\Pquestion\Model\Question;
use Aheadworks\Pquestion\Model\ResourceModel\Question as QuestionResource;
use Aheadworks\Pquestion\Model\Source\Question\Status;
use Magento\Catalog\Model\Entity\Attribute;
use Magento\Store\Model\Store;
use Aheadworks\Pquestion\Model\Source\Question\Sharing\Type;
use Magento\Customer\Model\Customer;
use Aheadworks\Pquestion\Model\Source\Question\Visibility;

/**
 * Class Collection
 * @package Aheadworks\Pquestion\Model\ResourceModel\Question
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ConfigFactory
     */
    protected $_catalogConfFactory;

    /**
     * @var AttributeFactory
     */
    protected $_catalogAttrFactory;

    /**
     * @var DateTime
     */
    protected $_dateTime;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ConfigFactory $catalogConfFactory
     * @param AttributeFactory $catalogAttrFactory
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param DateTime $dateTime
     * @param MetadataPool $metadataPool
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ConfigFactory $catalogConfFactory,
        AttributeFactory $catalogAttrFactory,
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        DateTime $dateTime,
        MetadataPool $metadataPool,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_catalogConfFactory = $catalogConfFactory;
        $this->_catalogAttrFactory = $catalogAttrFactory;
        $this->_dateTime = $dateTime;
        $this->metadataPool = $metadataPool;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            Question::class,
            QuestionResource::class
        );
        $this->addFilterToMap('store_id', 'main_table.store_id');
        $this->addFilterToMap('entity_id', 'main_table.entity_id');
    }

    /**
     * @return $this
     */
    public function joinPendingAnswerCount()
    {
        $pendingStatus = Status::PENDING_VALUE;
        $this->getSelect()->joinLeft(
            new \Zend_Db_Expr(
                "(SELECT COUNT(entity_id) as pending_answers, question_id"
                . " FROM {$this->getTable('aw_pq_answer')}"
                . " WHERE  status={$pendingStatus}"
                . " GROUP BY question_id)"
            ),
            "main_table.entity_id = t.question_id",
            ['pending_answers' => "IFNULL(t.pending_answers, 0)"]
        );
        $this->addFilterToMap('pending_answers', 't.pending_answers');

        return $this;
    }

    /**
     * @return $this
     */
    public function joinTotalAnswerCount()
    {
        $this->getSelect()->joinLeft(
            new \Zend_Db_Expr(
                "(SELECT COUNT(entity_id) as total_answers, question_id"
                . " FROM {$this->getTable('aw_pq_answer')}"
                . " GROUP BY question_id)"
            ),
            "main_table.entity_id = t_2.question_id",
            ['total_answers' => "IFNULL(t_2.total_answers, 0)"]
        );
        $this->addFilterToMap('total_answers', 't_2.total_answers');

        return $this;
    }

    /**
     * @return $this
     */
    public function joinProductName()
    {
        $linkField = $this->metadataPool->getMetadata(CategoryInterface::class)->getLinkField();
        $entityTypeId = $this->_catalogConfFactory->create()->getEntityTypeId();
        /** @var Attribute $attribute */
        $attribute = $this->_catalogAttrFactory->create()->loadByCode($entityTypeId, 'name');
        $storeId = $this->_storeManager->getStore(Store::ADMIN_CODE)->getId();

        $this->getSelect()->joinLeft(
            ['product_entity' => $this->getTable('catalog_product_entity')],
            'product_entity.entity_id = main_table.product_id',
            []
        );
        $this->getSelect()->joinLeft(
            ['product_name' => $attribute->getBackendTable()],
            "product_name.{$linkField}=product_entity.{$linkField}" .
            ' AND product_name.store_id=' .
            $storeId .
            ' AND product_name.attribute_id=' .
            $attribute->getId(),
            ['product_name' => 'product_name.value']
        );
        $this->addFilterToMap('product_name', 'product_name.value');

        return $this;
    }

    /**
     * @param Product $product
     *
     * @return $this
     */
    public function addFilterByProduct(Product $product)
    {
        $_productId = $product->getId();
        if (null === $_productId) {
            $_productId = 0;
        }
        $this
            ->getSelect()
            ->where(
                'sharing_type = ' .
                Type::ALL_PRODUCTS_VALUE
                . ' OR sharing_type = ' .
                Type::SPECIFIED_PRODUCTS_VALUE
                . ' OR (sharing_type = ' .
                Type::ORIGINAL_PRODUCT_VALUE
                . ' AND product_id = ' . intval($_productId) . ')'
            )
        ;

        return $this;
    }

    /**
     * @param int|string|Customer $customer
     *
     * @return $this
     */
    public function addFilterByCustomer($customer)
    {
        $customerValue = $this->_getCustomerFilteredValue($customer);
        if (is_string($customerValue)) {
            return $this->addFieldToFilter('author_email', $customerValue);
        }

        return $this->addFieldToFilter('customer_id', $customerValue);
    }

    /**
     * int customerId | string customerEmail
     * @param int |string|Customer $customer
     *
     * @return int|string
     */
    protected function _getCustomerFilteredValue($customer)
    {
        if (is_string($customer)) {
            return $customer;
        }

        $customerId = $customer;
        $customerEmail = '';
        if ($customer instanceof Customer) {
            $customerEmail = $customer->getEmail();
            $customerId    = (int)$customer->getId();
            if (!$customerId && empty($customerEmail)) {
                $customerId = -1; //empty collection should be returned
            }
        }

        if ($customerId) {
            return $customerId;
        }

        return $customerEmail;
    }

    /**
     * @param int $storeId
     *
     * @return $this
     */
    public function addShowInStoresFilter($storeId)
    {
        $this
            ->getSelect()
            ->where("FIND_IN_SET(0, show_in_store_ids) OR FIND_IN_SET({$storeId}, show_in_store_ids)")
        ;

        return $this;
    }

    /**
     * @param int $visibility
     *
     * @return $this
     */
    public function addVisibilityFilter($visibility)
    {
        $this->addFieldToFilter('visibility', $visibility);

        return $this;
    }

    /**
     * @return $this
     */
    public function addPublicFilter()
    {
        return $this->addVisibilityFilter(Visibility::PUBLIC_VALUE);
    }

    /**
     * @return $this
     */
    public function addPrivateFilter()
    {
        return $this->addVisibilityFilter(Visibility::PRIVATE_VALUE);
    }

    /**
     * @param mixed $status
     *
     * @return $this
     */
    public function addStatusFilter($status)
    {
        $this->addFieldToFilter('status', $status);

        return $this;
    }

    /**
     * @return $this
     */
    public function addApprovedStatusFilter()
    {
        return $this->addStatusFilter(Status::APPROVED_VALUE);
    }

    /**
     * @return $this
     */
    public function addCreatedAtLessThanNowFilter()
    {
        $dateFormat = $this->_dateTime->gmtDate();

        return $this->addFieldToFilter(
            'created_at',
            ['lteq' => $dateFormat]
        );
    }

    /**
     * @param number|null $from
     * @param number|null $to
     *
     * @return $this
     */
    public function addPendingAnswerFilter($from, $to)
    {
        $this->joinPendingAnswerCount();
        if (null !== $from) {
            $this->getSelect()->where("IFNULL(t.pending_answers, 0) >= ?", $from);
        }
        if (null !== $to) {
            $this->getSelect()->where("IFNULL(t.pending_answers, 0) <= ?", $to);
        }

        return $this;
    }

    /**
     * @param number|null $from
     * @param number|null $to
     *
     * @return $this
     */
    public function addTotalAnswerFilter($from, $to)
    {
        $this->joinTotalAnswerCount();
        if (null !== $from) {
            $this->getSelect()->where("IFNULL(t_2.total_answers, 0) >= ?", $from);
        }
        if (null !== $to) {
            $this->getSelect()->where("IFNULL(t_2.total_answers, 0) <= ?", $to);
        }

        return $this;
    }

    /**
     * @param string $sort
     *
     * @return $this
     */
    public function sortByHelpfull($sort = Select::SQL_DESC)
    {
        return $this->setOrder('helpfulness', $sort);
    }

    /**
     * @param string $sort
     *
     * @return $this
     */
    public function sortByDate($sort = Select::SQL_DESC)
    {
        return $this->setOrder('created_at', $sort);
    }

    /**
     * @return $this
     */
    public function joinCustomerIsset()
    {
        $this->getSelect()->joinLeft(
            ['customer' => $this->getTable('customer_entity')],
            'customer.entity_id = main_table.customer_id',
            ['customer_isset' => 'IF(ISNULL(customer.email), 0, 1)']
        );

        return $this;
    }
}

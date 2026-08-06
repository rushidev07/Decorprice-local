<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\CatalogWidget\Model\Rule
     */
    protected $_ruleModel;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\CatalogWidget\Model\Rule $ruleModel
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogWidget\Model\Rule $ruleModel
    ) {
        $this->_storeManager = $storeManager;
        $this->_ruleModel = $ruleModel;
        parent::__construct($context);
    }

    /**
     * @param mixed $storeId
     * @return \Magento\Framework\Phrase|string
     */
    public function getStoreLabel($storeId)
    {
        $store = $this->_storeManager->getStore($storeId);
        if (null === $store->getId()) {
            return __('[DELETED]');
        }
        return $store->getWebsite()->getName()
            . ' / ' . $store->getGroup()->getName()
            . ' / ' . $store->getName()
        ;
    }

    /**
     * @param mixed $product
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getCustomerWhoBoughtProductCollection($product)
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $salesCollection */
        $salesCollection = \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Magento\Sales\Model\ResourceModel\Order\Collection::class)
        ;
        $select = $salesCollection->getSelect();
        $itemTableName = $salesCollection->getTable('sales_order_item');
        $quoteTableName = $salesCollection->getTable('quote');
        $select
            ->joinLeft(
                ['item_table' => $itemTableName],
                "item_table.order_id = main_table.entity_id",
                ['product_id' => 'item_table.product_id']
            )
            ->joinLeft(
                ['quote_table' => $quoteTableName],
                "quote_table.entity_id = main_table.quote_id",
                ['customer_email' => 'quote_table.customer_email']
            )
        ;
        if (!$product instanceof \Magento\Catalog\Model\Product) {
            $product = \Magento\Framework\App\ObjectManager::getInstance()
                ->create(\Magento\Catalog\Model\Product::class)
                ->load($product)
            ;
        }
        $_productId = $product->getId();
        if (null === $_productId) {
            $_productId = 0;
        }
        $salesCollection->addFieldToFilter('state', \Magento\Sales\Model\Order::STATE_COMPLETE);
        $salesCollection->addFieldToFilter('product_id', $_productId);
        $salesCollection->getSelect()->group('quote_table.customer_email');
        return $salesCollection;
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return bool
     */
    public function isCustomerBoughtProduct($customer, $product)
    {
        $_customerEmails = $this->getCustomerWhoBoughtProductCollection($product)->getColumnValues('customer_email');
        return in_array($customer->getEmail(), $_customerEmails);
    }

    /**
     * @param mixed $content
     *
     * @return mixed
     */
    public function parseContentUrls($content)
    {
        return preg_replace(
            '/\b(?:(http(s?):\/\/)|(?=www\.))(\S+)/is',
            '<a href="http$2://$3" target="_blank">$1$3</a>',
            $content
        );
    }

    /**
     * @return array
     */
    public function getPointsEmailVariables()
    {
        $_pointsVariables = [
            'points_enabled' => false,
            'points_registration_amount' => 0,
            'points_amount' => 0
        ];
        if ($this->_moduleManager->isEnabled('Aheadworks_Points')) {
            $pointsConfigHelper = \Magento\Framework\App\ObjectManager::getInstance()
                ->create('Aheadworks\Points\Helper\Config')
            ;
            $_pointsVariables = [
                'points_enabled'             => true,
                'points_registration_amount' => $pointsConfigHelper->getPointsForRegistration(),
                'points_amount'              => $pointsConfigHelper->getPointsForAnsweringProductQuestion()
            ];
        }
        return $_pointsVariables;
    }

    /**
     * @param \Aheadworks\Pquestion\Model\ResourceModel\Question\Collection $collection
     * @param \Magento\Catalog\Model\Product $product
     * @return \Aheadworks\Pquestion\Model\ResourceModel\Question\Collection
     */
    public function filterQuestionCollectionByConditions(
        \Aheadworks\Pquestion\Model\ResourceModel\Question\Collection $collection,
        \Magento\Catalog\Model\Product $product
    ) {
        foreach ($collection as $key => $question) {
            /** @var \Aheadworks\Pquestion\Model\Question $question */
            if ($question->getSharingType()
                != \Aheadworks\Pquestion\Model\Source\Question\Sharing\Type::SPECIFIED_PRODUCTS_VALUE
            ) {
                continue;
            }
            $question->getResource()->unserializeFields($question);
            $this->_ruleModel->loadPost($question->getSharingValue());
            if ($this->_ruleModel->validate($product)) {
                continue;
            }
            $collection->removeItemByKey($key);
        }
        return $collection;
    }
}

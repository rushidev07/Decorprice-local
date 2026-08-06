<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_NameYourPrice
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\NameYourPrice\Model;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogRule\Model\Rule\Condition\Combine;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rule\Model\AbstractModel;
use Magento\Rule\Model\Action\Collection;

/**
 * Class Condition
 * @package Mageplaza\NameYourPrice\Model
 */
class Condition extends AbstractModel
{
    /**
     * Store matched product Ids
     *
     * @var array
     */
    protected $_productIds;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var CollectionFactory
     */
    protected $productColFactory;

    /**
     * @var Iterator
     */
    protected $resourceIterator;

    /**
     * Condition constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TimezoneInterface $localeDate
     * @param ProductFactory $productFactory
     * @param CollectionFactory $productColFactory
     * @param Iterator $resourceIterator
     * @param AbstractDb|null $resourceCollection
     * @param AbstractResource|null $resource
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        ProductFactory $productFactory,
        CollectionFactory $productColFactory,
        Iterator $resourceIterator,
        AbstractDb $resourceCollection = null,
        AbstractResource $resource = null
    ) {
        $this->productFactory = $productFactory;
        $this->productColFactory = $productColFactory;
        $this->resourceIterator = $resourceIterator;

        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection);
    }

    /**
     * @param $condition
     *
     * @return array|null
     */
    public function getMatchingProductIds($condition, $productId)
    {

        if ($this->_productIds === null) {
            $this->_productIds = [];

            /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
            $productCollection = $this->productColFactory->create();
            $productCollection
                ->addFieldToSelect('*');

            $this->setConditionsSerialized($condition);
            $this->getConditions()->collectValidatedAttributes($productCollection);
            $product = $this->productFactory->create()->load($productId);
            $this->getConditions()->validate($product);
            if ($this->getConditions()->validate($product)) {
                $this->_productIds[] = $productId;
            }
        }
        return $this->_productIds;
    }

    /**
     * Callback function for product matching
     *
     * @param $args
     */
    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);

        if ($this->getConditions()->validate($product)) {
            $this->_productIds[] = $product->getId();
        }
    }

    /**
     * @return Collection|\Magento\Rule\Model\Condition\Combine|mixed
     */
    public function getConditionsInstance()
    {
        return $this->getActionsInstance();
    }

    /**
     * @return Collection|mixed
     */
    public function getActionsInstance()
    {
        return ObjectManager::getInstance()->create(Combine::class);
    }
}

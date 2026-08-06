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

namespace Mageplaza\NameYourPrice\Observer;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Model\ResourceModel\Iterator;
use Mageplaza\NameYourPrice\Helper\Data as HelperData;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Mageplaza\NameYourPrice\Model\Condition as BargainCondition;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\ResourceConnection;
/**
 * Class ChangeTax
 * @package Mageplaza\NameYourPrice\Observer
 */
class ChangeConfig implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var ScopeConfig
     */
    protected $_scopeConfig;

    /**
     * @var ProductCollection
     */
    protected $productColFactory;

    /**
     * @var BargainCondition
     */
    protected $bargainCondition;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var Iterator
     */
    protected $resourceIterator;

    protected $_productIds;

    protected $writerConfig;

    protected $resourceConnection;

    /**
     * ChangeTax constructor.
     *
     * @param HelperData $helperData
     * @param ScopeConfig $scopeConfig
     * @param ProductCollection $productColFactory
     * @param Iterator $resourceIterator
     * @param ProductFactory $productFactory
     * @param BargainCondition $bargainCondition
     * @param WriterInterface $writerConfig
     */
    public function __construct(
        HelperData $helperData,
        ScopeConfig $scopeConfig,
        ProductCollection $productColFactory,
        Iterator $resourceIterator,
        ProductFactory $productFactory,
        BargainCondition $bargainCondition,
        WriterInterface $writerConfig,
        ResourceConnection $resourceConnection
    ) {
        $this->_scopeConfig      = $scopeConfig;
        $this->_helperData       = $helperData;
        $this->productColFactory = $productColFactory;
        $this->bargainCondition  = $bargainCondition;
        $this->productFactory    = $productFactory;
        $this->resourceIterator  = $resourceIterator;
        $this->writerConfig      = $writerConfig;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $dataConfig = $observer->getData('configData');

        if($dataConfig['section'] === 'mppricebargain') {
            $this->_productIds = [];
            $bargainCondition = $this->_scopeConfig->getValue('mppricebargain/general/condition',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $storeId = $objectManager->create('\Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
            
            $productCollection = $this->productFactory->create()->setStoreId($storeId)->getCollection();
            $productCollection->addAttributeToSelect('*')->addFieldToSelect('*');
            $sizeCol = $productCollection->getSize();
            $this->bargainCondition->setConditionsSerialized($bargainCondition);
            $this->bargainCondition->getConditions()->collectValidatedAttributes($productCollection);
            $this->resourceIterator->walk($productCollection->getSelect(), [[$this, 'callbackValidateProduct']], [
                'attributes' => $this->bargainCondition->getCollectedAttributes(),
                'product'    => $this->productFactory->create(),
            ]);
            if($sizeCol === count($this->_productIds)) {
                $result = 'all';
            } elseif (!$this->_productIds) {
                $result = 'null';
            } else {
                $result = implode(',', $this->_productIds);
            }

            try {
                $connection = $this->resourceConnection->getConnection();
                $sql = "SELECT config_id FROM core_config_data WHERE path = 'mpprice/custom/ids'";
                $resSql = $connection->fetchAll($sql);
                if (!$resSql) {
                    $scope = ScopeConfig::SCOPE_TYPE_DEFAULT;
                    $newConfig = "INSERT INTO core_config_data(scope, scope_id, path, value) VALUES ('$scope', 0, 'mpprice/custom/ids', '$result')";
                    $connection->query($newConfig);
                } else {
                    $updateConfig = "UPDATE core_config_data SET value = '$result' WHERE path='mpprice/custom/ids'";
                    $connection->query($updateConfig);
                }
            } catch (\Exception $e) {
                //
            }
//            $this->writerConfig->save($path, $result, $scope = ScopeConfig::SCOPE_TYPE_DEFAULT, $scopeId = 0);
        }
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

        if ($this->bargainCondition->getConditions()->validate($product)) {
            $this->_productIds[] = $product->getId();
        }
    }
}

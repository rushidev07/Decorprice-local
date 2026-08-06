<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Houzz
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Houzz\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProductSaveAfter implements ObserverInterface
{
    /**
     * Object Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Message Manager
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;

    /**
     * Request
     * @var \Magento\Framework\App\RequestInterface
     */
    public $request;

    /**
     * Registry
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * Data Helper
     * @var \Ced\Houzz\Helper\Data
     */
    public $dataHelper;

    /**
     * Ced Logger
     * @var \Ced\Houzz\Helper\CedLogger
     */
    public $cedLogger;

    protected  $houzzHelper;

    /**
     * Json Parser
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $json;

    /**
     * ProductSaveAfter constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Ced\Houzz\Helper\Data $data
     * @param \Ced\Houzz\Helper\CedLogger $cedLogger
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Ced\Houzz\Helper\Data $data,
        \Ced\Houzz\Helper\HouzzLogger $cedLogger,
        \Magento\Framework\Json\Helper\Data $json,
        \Ced\Houzz\Helper\Houzz $houzzHelper

    ) {
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->registry  = $registry;
        $this->dataHelper = $data;
        $this->cedLogger =$cedLogger;
        $this->json = $json;
        $this->houzzHelper = $houzzHelper;
    }

    /**
     * Catalog product save after event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return boolean
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();

        if(empty($product)){
            return false;
        }

        $prevSku = $this->registry->registry('prev_sku');
        $newSku = null;
        $cache = $this->objectManager->create('\Magento\Framework\App\Cache');
        $cacheArray = ($cache->load('ced_validate') && !is_null($cache->load('ced_validate'))) ? $this->json->jsonDecode($cache->load('ced_validate')) : array();
        if(isset($cacheArray[$product->getId()])) {
            unset($cacheArray[$product->getId()]);
            $cache->save($this->json->jsonEncode($cacheArray),'ced_validate');
        }
        if (empty($prevSku)) {
            $prevSku = $product->getSku();
        } else {
            $newSku = $product->getSku();
        }

        if ((!empty($newSku) && $prevSku != $newSku)) {
            $prevSkuExist = $this->dataHelper->getItem($prevSku);
            if ($prevSkuExist) {
                $additionalAttributes = [
                    '_attribute' => [],
                    '_value' => []
                ];
                $additionalAttributes['_value'][0] =
                    [
                        'additionalProductAttribute' => [
                            'productAttributeName' => 'sku_override',
                            'productAttributeValue' => 'true',
                        ]
                    ];
                if ($product->getTypeId() == 'simple' && $product->getVisibility() == 1) {
                    $parentIds = $this->objectManager
                        ->get('Magento\ConfigurableProduct\Model\Product\Type\Configurable')
                        ->getParentIdsByChild($product->getId());
                    if (!empty($parentIds)) {
                        foreach ($parentIds as $parentId) {
                            $isMapped = $this->dataHelper->getHouzzCategory($parentId);
                            if ($isMapped) {
                                $this->dataHelper->createProductOnHouzz([$parentId], $additionalAttributes);
                            }
                        }
                    }
                } elseif ($product->getTypeId() == 'simple') {
                    $this->dataHelper->createProductOnHouzz([$product->getId()], $additionalAttributes);
                }

            }
        }
        if($product->getTypeId() == 'simple') {

            //capture stock change
            $orgQty = $product->getOrigData('quantity_and_stock_status');
            $oldValue = (int)$orgQty['qty'];

            $postData = $this->request->getParam('product');
            $newValue = (int)$postData['quantity_and_stock_status']['qty'];

            $isInStock = (boolean)$postData['quantity_and_stock_status']['is_in_stock'];
            //if out of stock then set value to 0
            if (!$isInStock)
                $newValue = 0;

            if ($oldValue == $newValue)
                return false;

            $productId = $product->getId();

            $model = $this->objectManager->create('Ced\Houzz\Model\Productchange');
            $type = \Ced\Houzz\Model\Productchange::CRON_TYPE_INVENTORY;
            $model->setProductChange($productId, $oldValue, $newValue, $type);
            $this->checkForPriceChange($observer);
            // $price = $this->houzzHelper->getHouzzPrice($configurableProducts);
        }

        $this->registry->unregister('prev_sku');
        return true;
    }


    public function checkForPriceChange($observer)
    {
        $product = $observer->getProduct();

        $type = \Ced\Houzz\Model\Productchange::CRON_TYPE_PRICE;


        $helper = $this->objectManager->create('Ced\Houzz\Helper\Data');
        $pcode = "";
        if($profile = $helper->getCurrentProfile($product->getId())){
            $pcode = $profile['profile_code'];
        }
        $configPrice = trim($helper->getConfigData($pcode, 'houzz_configuration/productinfo_map/houzz_product_price'));

        $priceAttr = 'special_price';
        if($configPrice == 'differ'){
            $priceAttr = trim($helper->getConfigData($pcode,'houzz_configuration/productinfo_map/houzz_different_price'));
            $origSpecialPrice = $product->getOrigData('special_price');
            $specialPrice = $product->getData('special_price');
        }
        if($priceAttr != 'special_price'){
            $origPrice = $product->getOrigData($priceAttr);
            $price = $product->getData($priceAttr);
        }else{
            $origPrice = $product->getOrigData($priceAttr);
            $price = $product->getData($priceAttr);
            if($price == ''){
                $priceAttr = 'price';
                $origPrice = $product->getOrigData($priceAttr);
                $price = $product->getData($priceAttr);
            }
        }
        if($origPrice != $price){
            $prices = $this->houzzHelper->getHouzzPrice($product);
            $model = $this->objectManager->create('Ced\Houzz\Model\Productchange');
            $model->setProductChange($product->getId(), $origPrice, $prices['splprice'], $type);
        }
    }

}
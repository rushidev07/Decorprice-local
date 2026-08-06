<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Houzz
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Houzz\Helper;

/**
 * Class For Houzz
 * @package Ced\Houzz\Helper
 */
class Houzz extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Object Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Scope Manager
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfigManager;

    /**
     * Value Manager
     * @var \Magento\Framework\App\Config\ValueInterface
     */
    public $configValueManager;

    /**
     * Houzz constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
        parent::__construct($context);
        $this->scopeConfigManager = $this->objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->configValueManager = $this->objectManager->get('Magento\Framework\App\Config\ValueInterface');
    }

    /**
     * Get Refunded Qty Info
     * @param string $order
     * @param string $itemSku
     * @return array
     */
    public function getRefundedQtyInfo($order ="",$itemSku ="")
    {
        $itemSku = trim($itemSku);
        $check=[];
        $check['error']=1;
        if ($order == "") {
            $check['error_msg']="Order not found for current item.";
            return $check;
        }
        if ($itemSku=="") {
            $check['error_msg']="Item Sku not found for current item.";
            return $check;
        }
        if ($order instanceof \Magento\Sales\Model\Order) {
            $qtyOrdered=0;
            $qtyRefunded=0;

            foreach ($order->getAllItems() as $items) {
                if ($itemSku == $items->getSku()) {
                    $qtyOrdered = intval($items->getQtyOrdered());
                    $qtyRefunded = intval($items->getQtyRefunded());
                }
            }
            $availableToRefundQty = intval($qtyOrdered - $qtyRefunded);
            $check['error']=0;
            $check['qty_already_refunded'] = $qtyRefunded;
            $check['available_to_refund_qty'] = $availableToRefundQty;
            $check['qty_ordered'] = $qtyOrdered;
            return $check;
        }
        return $check;
    }

    /**
     * @return array
     */

    public function feedbackOptArray()
    {
        return [
            [
                'value' => '',
                'label' => __('Please Select an Option')
            ],
            [
                'value' => 'item damaged', 'label' =>__('item damaged')
            ],
            [
                'value' => 'not shipped in original packaging',
                'label' => __('not shipped in original packaging')
            ],
            [
                'value' => 'customer opened item',
                'label' => __('customer opened item')
            ]
        ];
    }


    /**
     * @return array
     */
    public function refundreasonOptionArr()
    {
        return [
            [
                'value' => '', 'label' => __('Please Select an Option')
            ],
            [
                'value' => 'BillingError', 'label' =>  __('BillingError')
            ],
            [
                'value' => 'TaxExemptCustomer', 'label' =>  __('TaxExemptCustomer')
            ],
            [
                'value' => 'ItemNotAsAdvertised', 'label' =>  __('ItemNotAsAdvertised')
            ],
            [
                'value' =>'IncorrectItemReceived', 'label' =>  __('IncorrectItemReceived')
            ],
            [
                'value' => 'CancelledYetShipped', 'label' =>  __('CancelledYetShipped')
            ],
            [
                'value' => 'ItemNotReceivedByCustomer', 'label' =>  __('ItemNotReceivedByCustomer')
            ],
            [
                'value' => 'IncorrectShippingPrice', 'label' =>  __('IncorrectShippingPrice')
            ],
            [
                'value' => 'DamagedItem', 'label' =>  __('DamagedItem')
            ],
            [
                'value' => 'DefectiveItem', 'label' =>  __('DefectiveItem')
            ],
            [
                'value' => 'CustomerChangedMind', 'label' =>  __('CustomerChangedMind')
            ],
            [
                'value' => 'CustomerReceivedItemLate', 'label' =>  __('CustomerReceivedItemLate')
            ],
            [
                'value' => 'Missing Parts / Instructions', 'label' =>  __('Missing Parts / Instructions')
            ],
            [
                'value' => 'Finance -> Goodwill', 'label' =>  __('Finance -> Goodwill')
            ],
            [
                'value' => 'Finance -> Rollback', 'label' =>  __('Finance -> Rollback')
            ]
        ];
    }
    
    /**
     * Get Houzz Price
     * @param Object $productObject
     * @return array
     */
    public function getHouzzPrice( $productObject )
    {
        $helperHouzz = $this->objectManager->create('Ced\Houzz\Helper\Data');
        $profile = $helperHouzz->getCurrentProfile($productObject->getId());
        $priceAttribute = "";
        foreach($profile['profile_attribute_mapping']['required_attributes'] as $attribute){
            if($attribute['houzz_attribute_name'] == 'price/amount'){
                $priceAttribute = $attribute['magento_attribute_code'];
                break;
            }
        }
        if($priceAttribute == 'price'){
            $splprice =(float)$productObject->getFinalPrice();
            $price = (float)$productObject->getPrice();
        }else{
            $splprice = $price = (float)$productObject->getData($priceAttribute);
        }
        $profile = $helperHouzz->getCurrentProfile($productObject->getId());
        $profileCode = $profile['profile_code'];


        $configPrice = $this->scopeConfigManager->getValue($profileCode."/".'houzzconfiguration/productinfo_map/houzz_product_price');
        if(!$configPrice){
            $configPrice = $this->scopeConfigManager->getValue('houzzconfiguration/productinfo_map/houzz_product_price');
            $profileCode = '';
        }
        switch($configPrice) {
            case 'plus_fixed':
                $fixedPrice = trim($helperHouzz->getConfigData($profileCode,'houzzconfiguration/productinfo_map/houzz_fix_price'));
                $price = $this->forFixPrice($price, $fixedPrice, 'plus_fixed');
                $splprice = $this->forFixPrice($splprice, $fixedPrice, 'plus_fixed');
                break;

            case 'plus_per':
                $percentPrice = trim($helperHouzz->getConfigData($profileCode,
                    'houzzconfiguration/productinfo_map/houzz_percentage_price'));
                $price = $this->forPerPrice($price, $percentPrice, 'plus_per');
                $splprice = $this->forPerPrice($splprice, $percentPrice, 'plus_per');
                break;

            case 'min_fixed':
                $fixedPrice = trim($helperHouzz->getConfigData($profileCode,
                    'houzzconfiguration/productinfo_map/houzz_fix_price'));
                $price = $this->forFixPrice($price, $fixedPrice, 'min_fixed');
                $splprice = $this->forFixPrice($splprice, $fixedPrice, 'min_fixed');
                break;

            case 'min_per':
                $percentPrice = trim($helperHouzz->getConfigData($profileCode,
                    'houzzconfiguration/productinfo_map/houzz_percentage_price'));
                $price = $this->forPerPrice($price, $percentPrice, 'min_per');
                $splprice = $this->forPerPrice($splprice, $percentPrice, 'min_per');
                break;

            case 'differ':
                $customPriceAttr = trim($helperHouzz->getConfigData($profileCode,
                    'houzzconfiguration/productinfo_map/houzz_different_price'));
                try {
                    $cprice =(float)$productObject -> getData($customPriceAttr);
                } catch(\Exception $e) {
                    $this->_logger->debug(" Houzz: Houzz Helper: getHouzzPrice() : " . $e->getMessage());
                }
                $price =(isset($cprice) && $cprice != 0) ? $cprice : $price ;
                $splprice = $price;
                break;

            default:
                return [
                    'price' => (string)$price,
                    'splprice' => (string)$splprice,
                ];
        }
        return [
            'price' => (string)$price,
            'splprice' => (string)$splprice,
        ];
    }

    /**
     * ForFixPrice
     * @param null $price
     * @param null $fixedPrice
     * @param string $configPrice
     * @return float|null
     */
    public function forFixPrice($price = null, $fixedPrice = null, $configPrice)
    {
        if (is_numeric($fixedPrice) && ($fixedPrice != '')) {
            $fixedPrice =(float)$fixedPrice;
            if ($fixedPrice > 0) {
                $price= $configPrice == 'plus_fixed' ?(float)($price + $fixedPrice)
                    :(float)($price - $fixedPrice);
            }
        }
        return $price;
    }

    /**
     * ForPerPrice
     * @param null $price
     * @param null $percentPrice
     * @param string $configPrice
     * @return float|null
     */
    public function forPerPrice($price = null, $percentPrice = null, $configPrice)
    {
        if (is_numeric($percentPrice)) {
            $percentPrice =(float)$percentPrice;
            if ($percentPrice > 0) {
                $price = $configPrice == 'plus_per' ?
                    (float)($price + (($price/100)*$percentPrice))
                    :(float)($price - (($price/100)*$percentPrice));
            }
        }
        return $price;
    }


    /**
     * Get Updated Refund Quantity
     * @param string $merchantOrderId
     * @return array
     */
  /*  public function getUpdatedRefundQty($merchantOrderId)
    {
        $refundcollection = $this->objectManager->create('Ced\Houzz\Model\HouzzRefund')
            ->getCollection()
            ->addFieldToFilter('refund_id', $merchantOrderId);
        $refundQty = [];
        if ($refundcollection->getSize()>0) {

            foreach ($refundcollection as $coll) {
                $refundData = unserialize($coll->getData('saved_data'));
                foreach ($refundData['sku_details'] as $data) {
                    $refundQty[$data['merchant_sku']]+=$data['refund_quantity'];
                }
            }
        }
        return $refundQty;
    }*/
}


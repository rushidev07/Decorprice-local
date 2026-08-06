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
 * @category  Ced
 * @package   Ced_Houzz
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Houzz\Controller\Adminhtml\Order;

class Ship extends \Magento\Backend\App\Action
{
    /**
     * ResultPageFactory
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * Ship constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeConfigManager = $this->_objectManager->get ( 'Magento\Framework\App\Config\ScopeConfigInterface' );

    }

    /**
     * Ship action
     * @return void
     */
    public function execute()
    {
        if(!$this->_objectManager->create('\Ced\Houzz\Helper\Data')->checkForConfiguration()) {
            return $this->getResponse()->setBody( 'Houzz API not Enabled or Invalid. Please check Houzz Configuration' );
        }
        $postData = $this->getRequest()->getPost();
        $tracking = $postData['tracking'];
        $id = $postData['key1'];
        $shippingMethod = $postData['shipping_method'];
        $orderId = $postData['orderid'];
        $itemsData = $postData['items'];
        $data = json_decode($itemsData);
        if (count($itemsData) == 0) {
            $this->getResponse()->setBody("You have no item in your Order.");
            return;
        }

        $orderToComplete = NULL;
        $orderCancel = NULL;
        $mixed = NULL;
        $shipmentArray = [];
        $dataShip = [];
        foreach ($data as $itemsData) {
            $merchantSku = $itemsData[0];
            $quantityOrdered = $itemsData[1];
            $k = 0;
            $time = time() + ($k + 1);
            $shpId = implode("-", str_split($time, 3));
            //flag for 3 cases complete , cancel and mixed.
            if ($quantityOrdered > 0) {
                if($postData['buttonId'] == 'submit_shippment') {
                    $orderToComplete[$merchantSku] = 'complete';
                } else {
                    $orderCancel[$merchantSku] = 'cancel';
                }
                // case 1 complete_order
                $shipmentArray [] = [
                    /*'lineNumber' => $lineNumber,*/
                    'shipment_item_id' => "$shpId",
                    'merchant_sku' => $merchantSku,
                    'response_shipment_sku_quantity' => intval($quantityOrdered),
                ];
                $uniqueRandomNumber = $id.mt_rand(10, 10000);
                $dataShip = [];
                $zip = trim($this->scopeConfigManager->getValue('houzzconfiguration/return_location/zip_code'));

                $dataShip['shipments'][] = [
                    'purchaseOrderId' => $orderId,
                    'alt_shipment_id' => $uniqueRandomNumber,
                    'shipment_tracking_number' => "$tracking",
                    'ship_from_zip_code' => "$zip",
                    'shipment_items' => $shipmentArray,
                    'shipping_method' => $shippingMethod,
                    'cancel_code' => $postData['cancel_code'],
                    'cancel_comment' => $postData['cancel_comment']
                ];
                continue;

            }
        }
        if ($dataShip) {
            $msg = $this->_objectManager->get('Ced\Houzz\Helper\Order')
                ->putShipOrder($dataShip, $postData, $orderToComplete, $orderCancel, $mixed);
        } else {
            $msg = "You have no information to Ship on Houzz.com";
        }
        return $this->getResponse()->setBody( $msg );
    }

    /**
     * Get Standard Off Set UTC
     * @return string | boolean
     */
    public function getStandardOffsetUTC()
    {
        $timezone = date_default_timezone_get();
        if ($timezone == 'UTC') {
            return '';
        } else {
            $timezone =$this->_objectManager->create('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
            $timezone = $timezone->getConfigTimezone();
            $transitions = array_slice($timezone->getTransitions(), -3, null, true);
            foreach (array_reverse($transitions, true) as $transition) {
                if ($transition['isdst'] == 1) {
                    continue;
                }
                return sprintf('UTC %+03d:%02u', $transition['offset'] / 3600,
                    abs($transition['offset']) % 3600 / 60);
            }
            return false;
        }
    }

    /**
     * IsALLowed
     * @return boolean
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_Houzz::Houzz');
    }
}
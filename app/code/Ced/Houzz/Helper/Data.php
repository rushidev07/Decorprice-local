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
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Houzz\Helper;
use Ced\Jet\Model\Source\ShipException\Exception;
use \Magento\Framework\Message\Manager;
use Magento\Framework\App\Filesystem\DirectoryList;


/*require_once BP . '/vendor/houzz-sdk/autoload.php';*/
/**
 * Class Data For Houzz Authenticated Seller Api
 * @package Ced\Houzz\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const HOUZZ_SANDBOX_API_URL = 'http://sellersandbox.houzz.com/SellerPortal/api/';
    const GET_FEEDS_SUB_URL = 'reports/v1/processing-report/%s?sellerId=%s';
    const ADD_LISTING_URL = 'method=addListing';
    const UPDATE_LISTING_URL = 'method=updateListing';
    const GET_LISTING_URL = 'method=getListing&SKU=';
    const GET_ORDERS_URL = 'method=getOrders';
    const GET_ORDER_URL = 'method=getOrder';
    const GET_NEW_ORDER_URL = 'method=getOrders&Status=CHARGED';
    const SHIP_ORDER_UPDATE_URL = 'method=updateOrder';
    const UPDATE_INVENTORY_URL = 'method=updateInventory';
    const CONSUMER_CHANEL_TYPE_ID = '7b2c8dab-c79c-4cee-97fb-0ac399e17ade';
    const SSL_VERIFY = false;

    public $replaceStringArr = [
        
    ];

    /**
     * Curl Object
     * @var \Magento\Framework\HTTP\Adapter\Curl
     */
    public $resource;
    protected $_filesystem;

    /**
     * Object Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Config Manager
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfigManager;

    /**
     * Json Parser
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $json;

    /**
     * Xml Parser
     * @var \Magento\Framework\Convert\Xml
     */
    public $xml;

    /**
     * DirectoryList
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    public $directoryList;

    /**
     * Date/Time
     * @var $dateTime
     */
    public $dateTime;

    /**
     * File Manager
     * @var $fileIo
     */
    public $fileIo;

    /**
     * Api Base Url
     * @var string $apiUrl
     */
    public $apiUrl;

    /**
     * Api Consumer Id
     * @var string $apiConsumerId
     */
    public $apiConsumerId;

    /**
     * Api Consumer Channel Id
     * @var string $apiConsumerChannelId
     */
    public $apiConsumerChannelId;

    /**
     * Api Private Key
     * @var string $apiPrivateKey
     */
    public $apiPrivateKey;

    /**
     * Api Signature Class Object
     * @var \Ced\Houzz\Helper\Signature
     */
    public $apiSignature;

    /**
     * Houzz Helper
     * @var \\Ced\Houzz\Helper\Houzz
     */
    public $houzzHelper;

    /**
     * Debug Log Mode
     * @var boolean
     */
    public $debugMode;

    /**
     * InventoryFullfillmentTime in days
     * @var integer
     */
    public $fulfillmentLagTime;

    /**
     * ProductIdType [UPC/EAN/GTIN/ISSN/ISBN]
     * @var integer
     */
    public $productIdType;

    /**
     * Houzz Logger
     * @var \Ced\Houzz\Helper\HouzzLogger
     */
    public $houzzLogger;

    /**
     * Selected Store Id
     * @var $selectedStore
     */
    public $selectedStore;
    /*
     * Mage Core Registry
     */
    public $registry;

    protected $_cache;

    protected $pcode;

    protected  $currentProfileId;

    protected  $_profile;

    public $parser;
    /*
     * Magento\Framework\Message\Manager
     */
    public $messageManager;
    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\HTTP\Adapter\Curl $curl
     * @param \Magento\Framework\Json\Helper\Data $json
     * @param \Magento\Framework\Xml\Generator $generator
     * @param \Magento\Framework\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Filesystem\Io\File $fileIo
     * @param \Ced\Houzz\Helper\Signature $signature
     * @param \Ced\Houzz\Helper\Houzz $houzzHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\HTTP\Adapter\Curl $curl,
        \Magento\Framework\Json\Helper\Data $json,
        \Magento\Framework\Xml\Generator $generator,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Io\File $fileIo,
        \Ced\Houzz\Helper\Signature $signature,
        \Ced\Houzz\Helper\Houzz $houzzHelper,
        \Ced\Houzz\Helper\Cache $cache,
        Manager $manager,
        \Magento\Framework\Filesystem $_filesystem,
        \Magento\Backend\App\Action\Context $actionContext,
        \Magento\Framework\Xml\Parser $parser
    ) {
        parent::__construct($context);

        $this->objectManager = $objectManager;
        $this->resource = $curl;
        $this->json = $json;
        $this->parser=$parser;
        $this->xml = $generator;
        $this->_filesystem = $_filesystem;
        $this->directoryList = $directoryList;
        $this->fileIo = $fileIo;
        $this->houzzHelper = $houzzHelper;
        $this->_cache = $cache;
        $this->scopeConfigManager = $this->objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->apiToken=$this->scopeConfigManager->getValue('houzzconfiguration/houzzsetting/api_token');
        $this->apiUsername=$this->scopeConfigManager->getValue('houzzconfiguration/houzzsetting/api_username');
        $this->apiAppName=$this->scopeConfigManager->getValue('houzzconfiguration/houzzsetting/api_appname');
         $this->apiConsumerId = $this->scopeConfigManager->getValue('houzzconfiguration/houzzsetting/customer_id');
        $this->apiPrivateKey = $this->scopeConfigManager->getValue('houzzconfiguration/houzzsetting/private_key');
        $this->apiConsumerChannelId = self::CONSUMER_CHANEL_TYPE_ID;
        $this->apiSignature = $signature;
        $this->dateTime =  $this->objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
        $this->houzzLogger =  $this->objectManager->create('\Ced\Houzz\Helper\HouzzLogger');
        $this->registry =  $this->objectManager->create('\Magento\Framework\Registry');
        $this->debugMode = $this->scopeConfigManager->getValue('houzzconfiguration/houzzsetting/debug');
        $this->fulfillmentLagTime = $this->scopeConfigManager->getValue('houzzconfiguration/productinfo_map/houzz_fullfillment_lagtime');
        $this->environment = $this->scopeConfigManager->getValue('houzzconfiguration/houzzsetting/environment');
        if($this->environment == 'production'){
            $this->apiUrl = 'https://api.houzz.com/api?format=xml&';
            $this->apiJsonUrl= 'https://api.houzz.com/api?format=json&';
        }else{
            $this->apiUrl = 'https://api.houzz2.com/api?format=xml&';
            $this->apiJsonUrl= 'https://api.houzz2.com/api?format=json&';
        }

        if (!is_numeric($this->fulfillmentLagTime) || empty($this->fulfillmentLagTime)) {
            $this->fulfillmentLagTime = '1';
        }
        $this->productIdType = $this->scopeConfigManager->getValue
        ('houzzconfiguration/productinfo_map/houzz_productid_type');
        $this->selectedStore = $this->scopeConfigManager->getValue('houzzconfiguration/product_edit/houzz_storeid');
        $this->selectedStore =
            !empty($this->selectedStore) ? $this->selectedStore : 0 ;
        $this->messageManager = $manager;
        $this->session =  $actionContext->getSession();
    }

    /**
     * @param string $name
     * @param string $code
     * @return array|string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function createDir($name = 'houzz', $code='var')
    {
        $path = $this->directoryList->getPath($code) . "/" . $name;
        if (file_exists($path)) {
            return ['status' => true,'path' => $path, 'action' => 'dir_exists'];
        } else {
            try
            {
                $this->fileIo->mkdir($path, 0775, true);
                return  ['status' => true,'path' => $path,  'action' => 'dir_created'];
            }
            catch (\Exception $e){
                return $code . '/' . $name . "Directory Creation Failed.";
            }
        }
    }

    /**
     * @param $data
     * @param array $params
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function createFile($data, $params = [])
    {
        $type = 'json';
        $timestamp = $this->objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime');
        $name = 'houzz_' . $timestamp->gmtTimestamp();
        $path = 'houzz';
        $code = 'var';

        if (isset($params['type'])) {
            $type = $params['type'];
        }
        if (isset($params['name'])) {
            $name = $params['name'];
        }
        if (isset($params['path'])) {
            $path = $params['path'];
        }
        if (isset($params['code'])) {
            $code = $params['code'];
        }

        if ($type == 'xml') {
            $xmltoarray = $this->objectManager->create('Magento\Framework\Convert\ConvertArray');
            $data = $xmltoarray->assocToXml($data);
        } elseif ($type == 'json') {
            $data = $this->json->jsonEncode($data);
        } elseif ($type == 'string') {
            $data = ($data);
        }

        $dir = $this->createDir($path, $code);
        $filePath = $dir['path'];
        $fileName = $name ."." . $type;
        try {
            $this->fileIo->write($filePath . "/" . $fileName, $data);
        }
        catch (\Exception $e){
            return false;
        }

        return true;
    }

    /**
     * @param $url
     * @param array $params
     * @return bool|mixed
     */
    public function getRequest($url, $params = array())
    {
        try {
            $url = $this->apiUrl.$url;
            $headers = array(
                "X-HOUZZ-API-SSL-TOKEN: ".trim($this->apiToken),
                "X-HOUZZ-API-USER-NAME: ".trim($this->apiUsername),
                "X-HOUZZ-API-APP-NAME: ".trim($this->apiAppName)
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $serverOutput = curl_exec($ch);
            $curlError = curl_error($ch);
            curl_close($ch);
            return $serverOutput;
        } catch (\Exception $e) {
            if ($this->debugMode) {
                $this->houzzLogger->logger(
                    'Get Request',
                    'Exception In Function',
                    $e->getMessage(),
                    'houzz->Helper->Data.php : GetRequest()'
                );
            }
            return false;
        }
    }

    /**
     * @param $url
     * @param array $params
     * @return bool|mixed
     */
    public function getjsonRequest($url, $params = array())
    {
        try {
            $url = $this->apiJsonUrl.$url;
            $headers = array(
                "X-HOUZZ-API-SSL-TOKEN: ".trim($this->apiToken),
                "X-HOUZZ-API-USER-NAME: ".trim($this->apiUsername),
                "X-HOUZZ-API-APP-NAME: ".trim($this->apiAppName)
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $serverOutput = curl_exec($ch);
            $curlError = curl_error($ch);
            curl_close($ch);
            return $serverOutput;
        } catch (\Exception $e) {
            if ($this->debugMode) {
                $this->houzzLogger->logger(
                    'Get Request',
                    'Exception In Function',
                    $e->getMessage(),
                    'houzz->Helper->Data.php : GetRequest()'
                );
            }
            return false;
        }
    }
    /**
     * @param $response
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function responseStatus($response)
    {

        try {
            if (!is_array($response)) {
                $response = $this->parser->loadXML($response)->xmlToArray();
            }
            if (is_array($response)) {

                reset($response); // make sure array pointer is at first element
                $firstKey = key($response);

                if ($response[$firstKey]['Ack'] == "Success") {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }catch (\Exception $e){
             $e->getMessage();

    }
    }

    /**
     * @param $url
     * @param array $params
     * @return bool|mixed
     */
    public function postRequest($url, $params = [])
    {
        try {
            $url = $this->apiUrl.$url;
            $body = '';
            if (isset($params['file'])) {
                $body = file_get_contents($params['file']);
            } elseif (isset($params['data'])) {
                $body = $params['data'];
            }
            $headers = array(
                "X-HOUZZ-API-SSL-TOKEN: ".trim($this->apiToken),
                "X-HOUZZ-API-USER-NAME: ".trim($this->apiUsername),
                "X-HOUZZ-API-APP-NAME: ".trim($this->apiAppName)
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS,$body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $serverOutput = curl_exec($ch);
            $curlError = curl_error($ch);
            curl_close($ch);
            return $serverOutput;
        } catch (\Exception $e) {
            if ($this->debugMode) {
            }
            return false;
        }
    }
    /**
     * Delete Request on https://marketplace.houzzapis.com/
     * @param string $url
     * @param string|[] $params
     * @return string
     */
    public function deleteRequest($url, $params = [])
    {
        try{
            $signature = $this->apiSignature->getSignature($url, 'DELETE');
            $url = $this->apiUrl . $url;
            $headers = [];
            $headers[] = "WM_SVC.NAME: Houzz Marketplace";
            $headers[] = "WM_QOS.CORRELATION_ID: " . base64_encode(\phpseclib\Crypt\Random::string(16));
            $headers[] = "WM_SEC.TIMESTAMP: " . $this->apiSignature->timestamp;
            $headers[] = "WM_SEC.AUTH_SIGNATURE: " . $signature;
            $headers[] = "WM_CONSUMER.ID: " .  $this->apiConsumerId;
            $headers[] = "Content-Type: application/json";
            $headers[] = "Accept: application/xml";
            if (isset($params['headers']) && !empty($params['headers'])) {
                $headers[] = $params['headers'];
            }
            $headers[] = "HOST: marketplace.houzzapis.com";
            //turning off header from curl response
            $this->resource->setConfig(['header' => 0]);
            $this->resource->setOptions([CURLOPT_HEADER => 1, CURLOPT_RETURNTRANSFER=>'true' ]);
            //for curl https use install certificate, add certificate location to php.ini
            $this->resource->setOptions([
                CURLOPT_HEADER => 1,
                CURLOPT_RETURNTRANSFER=>'true',
                CURLOPT_CUSTOMREQUEST => "DELETE"
            ]);
            $this->resource->write("DELETE", $url, '1.1', $headers);
            $serverOutput = $this->resource->read();
            if (!$serverOutput) {
                return false;
            }
            $this->resource->close();
            return $serverOutput;
        } catch (\Exception $e) {
            if($this->debugMode)
                $this->logger(
                    'Delete Request',
                    'Exception In Function',
                    $e->getMessage(),
                    'houzz->Helper->Data.php : deleteRequest()'
                );
            return false;
        }
    }



    /**
     * @param $purchaseOrderId
     * @param string $subUrl
     * @return bool|mixed
     * @throws \DOMException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function acknowledgeOrder($purchaseOrderId , $subUrl =\Ced\Houzz\Helper\Data::SHIP_ORDER_UPDATE_URL)
    {
        $processArray = [
            'UpdateOrderRequest' =>
                [
                    '_attribute' => [
                    ],
                    '_value' =>  [
                        'OrderId' => [
                            '_attribute' => [
                            ],
                            '_value' => $purchaseOrderId
                        ],
                        'Action' => [
                            '_attribute' => [
                            ],
                            '_value' => 'Process'
                        ]
                    ]
                ]
        ];
        $path = $this->createDir('houzz', 'var');
        $this->xml->arrayToXml($processArray)->save($path['path'] . '/' . 'ProcessOrder.xml');
        $this->unEscapeData($path['path'] . '/' . 'ProcessOrder.xml');
        $response =  $this->getRequest( \Ced\Houzz\Helper\Data::SHIP_ORDER_UPDATE_URL,[ 'file' => $path['path'] . '/' . 'ProcessOrder.xml']);
        $responseStatus = $this->responseStatus($response);
        if(!$responseStatus){
            return false;
        }
        try {
            $response = $this->json->jsonDecode($response);
            if (isset($response['error'])) {
                throw new \Exception();
            }
            return isset($response['order']) ? $response['order'] : $response;
        }
        catch(\Exception $e){
            if ($this->debugMode) {
                $this->houzzLogger->logger('Houzz Order' , ' Acknowledge Order',var_export($response,true) , 'Exception-'.$e->getMessage());
            }
            return false;
        }
    }

    /**
     * @param null $postData
     * @param string $subUrl
     * @return bool|string
     * @throws \DOMException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function shipOrder($postData = null , $subUrl = \Ced\Houzz\Helper\Data::SHIP_ORDER_UPDATE_URL)
    {
        $purchaseOrderId = $postData['shipments'][0]['purchaseOrderId'];
        $shipArray = [
            'UpdateOrderRequest' =>
                [
                    '_attribute' => [
                    ],
                    '_value' =>  [
                        'OrderId' => [
                            '_attribute' => [
                            ],
                            '_value' => $purchaseOrderId
                        ],
                        'Action' => [
                            '_attribute' => [
                            ],
                            '_value' => 'Ship'
                        ],
                        'ShippingMethod' => [
                            '_attribute' => [
                            ],
                            '_value' => $postData['shipments'][0]['shipping_method']
                        ],
                        'TrackingNumber' => [
                            '_attribute' => [
                            ],
                            '_value' => $postData['shipments'][0]['shipment_tracking_number']
                        ]
                    ],
                ]
        ];
        $path = $this->createDir('houzz', 'var');
        $this->xml->arrayToXml($shipArray)->save($path['path'] . '/' . 'ProductShip.xml');
        $this->unEscapeData($path['path'] . '/' . 'ProductShip.xml');
        $response =  $this->postRequest( \Ced\Houzz\Helper\Data::SHIP_ORDER_UPDATE_URL,[ 'file' => $path['path'] . '/' . 'ProductShip.xml']);
        $responseStatus = $this->responseStatus($response);
        if(!$responseStatus){
            return false;
        }
        try{
            $response = str_replace('ns:2', '', $response);
            $data = $this->parser->loadXML($response)->xmlToArray();
            if ($this->debugMode) {
                $this->houzzLogger->logger('Shipment Order-'.$purchaseOrderId , 'Response (Post Request)' , var_export($data,true) ,'No Exception Case | Purchase Order Id '.$purchaseOrderId );
            }
            return $this->json->jsonEncode($data);
        }
        catch(\Exception $e){
            if ($this->debugMode) {
                $this->houzzLogger->logger('Shipment-'.$purchaseOrderId, 'Response (Post Request)', $response, 'Exception-' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * @param $purchaseOrderId
     * @param $dataship
     * @param string $subUrl
     * @return array|bool|mixed
     * @throws \DOMException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function rejectOrders($purchaseOrderId , $dataship , $subUrl = \Ced\Houzz\Helper\Data::SHIP_ORDER_UPDATE_URL)
    {
        $cancelArray = [
            'UpdateOrderRequest' =>
                [
                    '_attribute' => [
                    ],
                    '_value' =>  [
                        'OrderId' => [
                            '_attribute' => [
                            ],
                            '_value' => '<![CDATA['.$purchaseOrderId.']]>'
                        ],
                        'Action' => [
                            '_attribute' => [
                            ],
                            '_value' => 'Cancel'
                        ],
                        'Comments' => [
                            '_attribute' => [
                            ],
                            '_value' => $dataship['shipments'][0]['cancel_comment']
                        ],
                        'CancelCode' => [
                            '_attribute' => [
                            ],
                            '_value' => $dataship['shipments'][0]['cancel_code']
                        ]
                    ],
                ]
        ];
        $path = $this->createDir('houzz', 'var');
        $this->xml->arrayToXml($cancelArray)->save($path['path'] . '/' . 'CancelOrder.xml');
        $this->unEscapeData($path['path'] . '/' . 'CancelOrder.xml');
        $response =  $this->postRequest( \Ced\Houzz\Helper\Data::SHIP_ORDER_UPDATE_URL,[ 'file' => $path['path'] . '/' . 'CancelOrder.xml']);
        $response = str_replace('ns:2', '', $response);
        $response = $this->parser->loadXML($response)->xmlToArray();
        $responseStatus = $this->responseStatus($response);
        if(!$responseStatus){
            return false;
        }
        try{
            if (isset($response['error'])) {
                throw new \Exception();
            }
            if ($this->debugMode) {
                $this->houzzLogger->logger('Reject Order-'.$purchaseOrderId , 'Response (Post Request)' , var_export($response , true) ,'No Exception Case | Purchase Order Id '.$purchaseOrderId );
            }
            return $response;
        }
        catch(\Exception $e){
            if ($this->debugMode) {
                $this->houzzLogger->logger('Reject Order-'.$purchaseOrderId, 'Response (Post Request)', $response, 'Exception-' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Get Reports
     * @param string|[] $params
     * @param string $subUrl
     * @return string compressed csv file
     * @link https://developer.houzzapis.com/#get-report
     */
    public function getReports($params = [], $subUrl = self::GET_REPORTS_SUB_URL)
    {
        if (!isset($params['type']) || empty($params['type'])) {
            $params['type'] = 'item';
        }
        $queryString = empty($params) ? '' : '?' . http_build_query($params);
        $response = $this->getRequest($subUrl . $queryString);
        //csv file in response
        return $response;
    }

    /**
     * Get Item
     * @param string $sku
     * @param string $returnField
     * @param string $subUrl
     * @return string|[]
     * @link https://developer.houzzapis.com/#get-an-item
     */
    public function getItem($sku, $returnField = null, $subUrl = self::GET_ITEMS_SUB_URL )
    {
        $response = $this->getRequest($subUrl . '?sku=' . $sku);
        try {
            $response = $this->json->jsonDecode($response);
            if (isset($response['error'])) {
                throw new \Exception();
            }
            if ($returnField) {
                return $response['MPItemView'][0]['publishedStatus'];
            }
            return $response;
        }
        catch(\Exception $e){
            if ($this->debugMode) {
                $this->houzzLogger->logger("Houzz: getItem:","Get Response: SKU: ".$sku   , var_export($response, true));
            }
            return false;
        }
    }

    /**
     * Get Items
     * @param string|[] $params
     * @param string $subUrl
     * @return string
     * @link https://developer.houzzapis.com/#get-all-items
     */
    public function getItems($params = [], $subUrl = self::GET_ITEMS_SUB_URL)
    {
        if (!isset($params['limit']) || empty($params['limit'])) {
            $params['limit'] = '20';
        }
        $queryString = empty($params) ? '' : '?' . http_build_query($params);
        $response = $this->getRequest($subUrl . $queryString);
        return $response;
    }


    /**
     * @param $prodIds
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function syncStatus($prodIds)
    {
        foreach($prodIds as $prodId) {
            $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($prodId);
            $response =  $this->getRequest( \Ced\Houzz\Helper\Data::GET_LISTING_URL.$this->prepareSku( $product->getSku() ));
            $response = $this->parser->loadXML($response)->xmlToArray();
            $responseStatus = $this->responseStatus($response);
            $errorFlag = true;
            if(!$responseStatus) {
                $errorFlag = false;
            }
            reset($response); // make sure array pointer is at first element
            $firstKey = key($response);
            if(isset($response[$firstKey]['Listing']['SKU'])) {
                $product->setHouzzProductStatus($response[$firstKey]['Listing']['Status'])
                    ->getResource()->saveAttribute($product, 'houzz_product_status');
            }
            if ($product->getTypeId() == 'configurable'){
                /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productType */
                $productType = $product->getTypeInstance();
                /** @var array $products */
                $chProducts = $productType->getUsedProducts($product);
                foreach ($chProducts as $chProduct) {
                    $childProduct = $this->objectManager->create('Magento\Catalog\Model\Product')->load($chProduct->getId());
                    $response =  $this->getRequest( \Ced\Houzz\Helper\Data::GET_LISTING_URL.$this->prepareSku( $childProduct->getSku() ));
                    $response = $this->parser->loadXML($response)->xmlToArray();
                    $responseStatus = $this->responseStatus($response);
                    $errorFlag = true;
                    if(!$responseStatus) {
                        $errorFlag = false;
                    }
                    reset($response); // make sure array pointer is at first element
                    $firstKey = key($response);
                    if(isset($response[$firstKey]['Listing']['SKU'])) {
                        $childProduct->setHouzzProductStatus($response[$firstKey]['Listing']['Status'])
                            ->getResource()->saveAttribute($childProduct, 'houzz_product_status');
                    }
                }
            }
        }

        return $errorFlag;
    }


    /**
     * @param array $prodids
     * @param string $prodStatus
     * @return bool
     * @throws \DOMException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function changeProductStatusOnHouzz( $prodids = [], $prodStatus = "Active") {
        $errorFlag = true;
        $response=[];
        foreach($prodids as $prodid) {
            $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($prodid)
                ->setStoreId($this->selectedStore);
            $inventoryArray = [
                'UpdateInventoryRequest' =>
                    [
                        '_attribute' => [
                        ],
                        '_value' =>  [
                            'SKU' => [
                                '_attribute' => [
                                ],
                                '_value' => $this->prepareSku( $product->getSku() )
                            ],
                            'Action' => [
                                '_attribute' => [
                                ],
                                '_value' => 'update'
                            ],
                            'Status' => [
                                '_attribute' => [
                                ],
                                '_value' => $prodStatus
                            ]
                        ]
                    ]
            ];
            $path = $this->createDir('houzz', 'var');
            $path = $path['path'] . '/' . 'StatusInventoryFeed.xml';
            $this->xml = $this->objectManager->create('\Magento\Framework\Xml\Generator');
            $this->xml->arrayToXml($inventoryArray)->save($path);
            $response[] =  $this->postRequest( \Ced\Houzz\Helper\Data::UPDATE_INVENTORY_URL, ['file' => $path]);
            $responseStatus = $this->responseStatus($response);
            if(!$responseStatus) {
            }
        }
        return $response;
    }


    /**
     * @param $ids
     * @param array $additionalOverrideAttributes
     * @return bool|string
     * @throws \DOMException
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createProductOnHouzz($ids, $action = 'AddListingRequest')
    {
        $this->session->unsParentSkusSession();
        $ids = $this->validateAllProducts($ids, false);
        $prodids = [];
        foreach ($ids as $id) {
            if(!isset($id['id'])){
                continue;
            } else {
                $prodids[] = $id;
            }
        }
        if (count($ids) > 0 ) {
            $currency = $this->objectManager->create('\Magento\Store\Model\StoreManager')
                ->getStore()
                ->getBaseCurrencyCode();
            $timeStamp = (string)$this->dateTime->gmtTimestamp();
            $productToUpload = '';
            $processedProd = $key = 0;
            $totalProducts = count($prodids);
            $additionalAttributes = array();
            $hasError = false;
            foreach ($prodids as $id) {
                $processedProd++;
                if($totalProducts == $processedProd) {
                    $this->session->setAllBatchCompleted(true);
                }
                $listingArr = array();
                $product = $this->objectManager->create('Magento\Catalog\Model\Product')
                    ->load($id['id'])
                    ->setStoreId($this->selectedStore);

                //if configurable product, than load parent category
                if (isset($id['parentid'])) {
                    $confPro = $this->objectManager->create('Magento\Catalog\Model\Product')
                        ->load($id['parentid'])
                        ->setStoreId($this->selectedStore);
                    $parentSkusSession = $this->session->getParentSkusSession();
                    if(!isset($parentSkusSession[$id['parentid']])) {
                        $parentRes = $this->uploadConfigProd($id, $confPro, $action);
                        $parentSkusSession[$id['parentid']] = $parentRes;
                        $this->session->setParentSkusSession($parentSkusSession);
                    }
                    $additionalAttributes =
                        $this->prepareAdditionalAttributes
                        ($id['variantattrmapped'], array(), $product);
                    $id['additionalAttributes'] =   $additionalAttributes;
                } else {
                    //  $category = $this->getHouzzCategory($id['id'], $product);
                }

                $customAttrs =  array();
                if (isset($id['parentid'])) {
                    //all simple attributes needed here (Case 1)
                    $this->getCurrentProfile($id['parentid']);
                    $attributes = $this->getHouzzAttributes($id['parentid'], [
                        'required' => false, 'mapped' => true, 'validation' => false
                    ]);
                    $customAttrs = $this->getHouzzAttributes($id['parentid'], [
                        'required' => false, 'mapped' => true, 'validation' => true
                    ])['attributes'];
                } else {
                    //all simple attributes needed here (Case 1)
                    $this->getCurrentProfile($id['id']);
                    $attributes = $this->getHouzzAttributes($id['id'], [
                        'required' => false, 'mapped' => true, 'validation' => false
                    ]);
                    $customAttrs = $this->getHouzzAttributes($id['id'], [
                        'required' => false, 'mapped' => true, 'validation' => true
                    ])['attributes'];
                }
                $category = $attributes['category'];
                $attributes = $attributes["attributes"];
                $counter = 0;
                foreach ($customAttrs as $customAttr) {
                    if ($customAttr['magento_attribute_code'] == 'default') {
                        $product->setData('ced_'.$counter, $customAttr['default']);
                        $attributes[$customAttr['houzz_attribute_name']] = 'ced_'.$counter;
                        $counter++;
                    }
                }
                $attrValueArray = [];
                foreach($attributes as $attrKey => $attrValue) {
                    $attrValueArray[$attrKey] = $this->getMagentoProductAttributeValue($product, $attrKey, $attributes);
                }
                $attrValueArray['category_id']=$category['parent_cat_id'];

                if($attrValueArray['assembly_required'] == null){
                    $attrValueArray['assembly_required'] = '0';
                }
                $listingArr = [
                    'Listing' => [
                        '_attribute' => [
                        ],
                        '_value' => [
                            'Title' => [
                                '_attribute' => [
                                ],
                                '_value' => htmlentities($attrValueArray['title'])
                            ],
                            'Description' => [
                                '_attribute' => [
                                ],
                                '_value' => htmlentities($attrValueArray['description'])
                            ],
                            'AssemblyRequired' => [
                                '_attribute' => [
                                ],
                                '_value' =>(string)$attrValueArray['assembly_required']
                            ],
                            'Prop65Disclosure' => [
                                '_attribute' => [
                                ],
                                '_value' =>(string)$attrValueArray['Prop65Disclosure']
                            ],
                            'MinimumOrderQuantity' => [
                                '_attribute' => [
                                ],
                                '_value' => $attrValueArray['MinimumOrderQuantity']
                            ],
                            'SKU' => [
                                '_attribute' => [
                                ],
                                '_value' => $this->prepareSku( $attrValueArray['sku'] )
                            ],
                            'UPC' => [
                                '_attribute' => [
                                ],
                                '_value' => $attrValueArray['upc']
                            ],
                            'CategoryId' => [
                                '_attribute' => [
                                ],
                                '_value' => $attrValueArray['category_id']
                            ],
                            'Price' => [
                                '_attribute' => [
                                ],
                                '_value' => $attrValueArray['price']
                            ],
                            'Currency' => [
                                '_attribute' => [
                                ],
                                '_value' => $attrValueArray['currency']
                            ],
                            'Manufacturer' => [
                                '_attribute' => [
                                ],
                                '_value' => $attrValueArray['manufacturer']
                            ],
                            /*'Style' => [
                                '_attribute' => [
                                ],
                                '_value' => $attrValueArray['style']
                            ],*/
                            'MSRP' => [
                                '_attribute' => [
                                ],
                                '_value' => $attrValueArray['msrp']
                            ],
                            'Quantity' => [
                                '_attribute' => [
                                ],
                                '_value' => isset($attrValueArray['quantity']['qty']) ? $attrValueArray['quantity']['qty'] : $attrValueArray['quantity']
                            ],
                            /*'Status' => [
                                '_attribute' => [
                                ],
                                '_value' => $attrValueArray['status']
                            ],*/
                            'ProductSpec' => [
                                '_attribute' => [
                                ],
                                '_value' => [
                                    'Width' => [
                                        '_attribute' => [
                                        ],
                                        '_value' => $attrValueArray['width']
                                    ],
                                    'Height' => [
                                        '_attribute' => [
                                        ],
                                        '_value' => $attrValueArray['height']
                                    ],
                                    'Depth' => [
                                        '_attribute' => [
                                        ],
                                        '_value' => $attrValueArray['depth']
                                    ],
                                    'Weight' => [
                                        '_attribute' => [
                                        ],
                                        '_value' => $attrValueArray['weight']
                                    ],
                                    'DimensionsUnit' => [
                                        '_attribute' => [
                                        ],
                                        '_value' => $attrValueArray['dimensions_unit']
                                    ],
                                    'WeightUnit' => [
                                        '_attribute' => [
                                        ],
                                        '_value' => $attrValueArray['weight_unit']
                                    ]
                                ]
                            ],
                            'ShippingDetails' => [
                                '_attribute' => [
                                ],
                                '_value' => [
                                    'Packages' => [
                                        '_attribute' => [
                                        ],
                                        '_value' => [
                                            'Package' => [
                                                '_attribute' => [
                                                ],
                                                '_value' => [
                                                    'Width' => [
                                                        '_attribute' => [
                                                        ],
                                                        '_value' => $attrValueArray['package_width']
                                                    ],
                                                    'Height' => [
                                                        '_attribute' => [
                                                        ],
                                                        '_value' => $attrValueArray['package_height']
                                                    ],
                                                    'Depth' => [
                                                        '_attribute' => [
                                                        ],
                                                        '_value' => $attrValueArray['package_depth']
                                                    ],
                                                    'Weight' => [
                                                        '_attribute' => [
                                                        ],
                                                        '_value' => $attrValueArray['package_weight']
                                                    ],
                                                ]
                                            ]
                                        ]
                                    ],
                                    'ShippingOptions' => [
                                        '_attribute' => [
                                        ],
                                        '_value' => [
                                            'ShippingOption' => [
                                                '_attribute' => [
                                                ],
                                                '_value' => [
                                                    'Country' => [
                                                        '_attribute' => [
                                                        ],
                                                        '_value' => $attrValueArray['shipping_country']
                                                    ],
                                                    'Type' => [
                                                        '_attribute' => [
                                                        ],
                                                        '_value' => $attrValueArray['shipping_type']
                                                    ],
                                                    'Price' => [
                                                        '_attribute' => [
                                                        ],
                                                        '_value' =>$attrValueArray['shipping_price']
                                                    ],
                                                ]
                                            ]
                                        ]
                                    ],
                                    'LeadTimeMin' => [
                                        '_attribute' => [
                                        ],
                                        '_value' => $attrValueArray['lead_time_min']
                                    ],
                                    'FreightItem' => [
                                        '_attribute' => [
                                        ],
                                        '_value' => $attrValueArray['FreightItem']
                                    ],
                                    'LeadTimeMax' => [
                                        '_attribute' => [
                                        ],
                                        '_value' => $attrValueArray['lead_time_max']
                                    ],
                                    'PackageDimensionsUnit' => [
                                        '_attribute' => [
                                        ],
                                        '_value' => $attrValueArray['package_dimensions_unit']
                                    ],
                                    'PackageWeightUnit' => [
                                        '_attribute' => [
                                        ],
                                        '_value' => $attrValueArray['package_weight_unit']
                                    ],
                                ]
                            ],
                        ]
                    ]
                ];
                if(isset($attrValueArray['Keywords']) && $attrValueArray['Keywords'] != null) {
                    $listingArr['Listing']['_value']['Keywords']['_attribute'] = array();
                    $listingArr['Listing']['_value']['Keywords']['_value'] = $attrValueArray['Keywords'];
                }
                if(isset($attrValueArray['Prop65WarningType']) && $attrValueArray['Prop65WarningType'] != null) {
                    $listingArr['Listing']['_value']['Prop65WarningType']['_attribute'] = array();
                    $listingArr['Listing']['_value']['Prop65WarningType']['_value'] = $attrValueArray['Prop65WarningType'];
                }
                $productImages = $product->getMediaGalleryImages();
                $imgIndex = 0;
                foreach ($productImages as $image) {
                    if($imgIndex >= 5) {
                        break;
                    }
                    $listingArr['Listing']['_value']['Images']['_attribute'] = array();
                    $listingArr['Listing']['_value']['Images']['_value'][$imgIndex]['ImageLink']['_attribute'] = array();
                    $listingArr['Listing']['_value']['Images']['_value'][$imgIndex]['ImageLink']['_value'] = $image->getUrl();
                    $imgIndex++;
                }
                if (isset($id['parentid'])) {
                    $listingArr['Listing']['_value']['ProductAttributes']['_attribute'] =
                    $listingArr['Listing']['_value']['RelationshipType']['_attribute'] =
                    $listingArr['Listing']['_value']['Parentage']['_attribute'] =
                    $listingArr['Listing']['_value']['ParentSKU']['_attribute'] = array();
                    $listingArr['Listing']['_value']['ProductAttributes']['_value'] = $additionalAttributes['_value'];
                    $listingArr['Listing']['_value']['RelationshipType']['_value'] = 'Variation';
                    $listingArr['Listing']['_value']['Parentage']['_value'] = 'Child';
                    $listingArr['Listing']['_value']['ParentSKU']['_value'] = $this->prepareSku( $confPro->getSku() );
                }
                $productToUpload = [
                    $action =>
                        [
                            '_attribute' => [
                            ],
                            '_value' => $listingArr
                        ]
                ];
                $path = $this->createDir('houzz', 'var');
                $this->xml = $this->objectManager->create('Ced\Houzz\Helper\Custom\Generator');
                $this->xml->arrayToXml($productToUpload)->save($path['path'] . '/' . 'MPProduct.xml');
                $this->unEscapeData($path['path'] . '/' . 'MPProduct.xml');// return true;
                if($action == 'UpdateListingRequest')
                    $actionUrl = self::UPDATE_LISTING_URL;
                else
                    $actionUrl = self::ADD_LISTING_URL;
                $response =  $this->postRequest( $actionUrl, [ 'file' => $path['path'] . '/' . 'MPProduct.xml']);
                $paths=$path['path'] . '/' . 'MPProduct.xml';
                $response = $this->checkSaveResponseParse($response, $product, 'item',$paths);
                if(!$response && !$hasError) {
                    $hasError = true;
                }
            }
            if($hasError)
                return false;
            else
                return true;

        }
        return false;
    }

    /**
     * @param $product
     * @param $code
     * @param $attribute
     * @return nulls
     */

    public function getMagentoProductAttributeValue($product, $code, $attribute){

        if(isset($attribute[$code])
            && $attribute[$code]!="default") {
            if($code == 'shippingWeight/value') {
            }
            $attributeCode = $attribute[$code];
            if(!$product->getData($attributeCode) || $product->getData($attributeCode)=="") {
                return NULL;
            }
            return $product->getData($attributeCode);
        } else {
            return $attribute[$code]['default'];
        }
    }

    /**
     * @param null $ids
     * @param null $data
     * @return bool|string
     * @throws \DOMException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateInventoryOnHouzz($ids = null, $parentID = null)
    {
        $hasError = false;
        $stockItem = $this->objectManager->create('\Magento\CatalogInventory\Model\Stock\StockItemRepository');
        $key = 0;
        $errorFlag = true;
        foreach ($ids as $id) {
            $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($id)
                ->setStoreId($this->selectedStore);
            if (isset($parentID) && $parentID) {
                //all simple attributes needed here (Case 1)
                $this->getCurrentProfile($parentID);
                $attributes = $this->getHouzzAttributes($parentID, [
                    'required' => false, 'mapped' => true, 'validation' => false
                ]);
                $customAttrs = $this->getHouzzAttributes($parentID, [
                    'required' => false, 'mapped' => true, 'validation' => true
                ])['attributes'];
            } else {
                //all simple attributes needed here (Case 1)
                $this->getCurrentProfile($id);
                $attributes = $this->getHouzzAttributes($id, [
                    'required' => false, 'mapped' => true, 'validation' => false
                ]);
                $customAttrs = $this->getHouzzAttributes($id, [
                    'required' => false, 'mapped' => true, 'validation' => true
                ])['attributes'];
            }
            $category = $attributes['category'];
            $attributes = $attributes["attributes"];
            $counter = 0;
            foreach ($customAttrs as $customAttr) {
                if ($customAttr['magento_attribute_code'] == 'default') {
                    $product->setData('ced_'.$counter, $customAttr['default']);
                    $attributes[$customAttr['houzz_attribute_name']] = 'ced_'.$counter;
                    $counter++;
                }
            }
            $attrValueArray = [];
            foreach($attributes as $attrKey => $attrValue) {
                $attrValueArray[$attrKey] = $this->getMagentoProductAttributeValue($product, $attrKey, $attributes);
            }
            if ($product->getTypeId() == 'configurable') {
                $parentId = $product->getId();
                $productType = $product->getTypeInstance();
                $products = $productType->getUsedProducts($product);
                foreach ($products as $chproduct) {
                    $response = $this->updateInventoryOnHouzz(array($chproduct->getId()), $parentId);
                    if (!$response && !$hasError) {
                        $hasError = true;
                    }
                    if ($hasError)
                        return false;
                    else
                        return true;
                }
            } elseif ($product->getTypeId() == 'simple' || $product->getTypeId() == 'virtual') {
                $key += 1;
                if($product->getStatus() == 0) {
                    $prodStatus = 'inactive';
                } else {
                    $prodStatus = 'active';
                }
                $inventoryArray = [
                    'UpdateInventoryRequest' =>
                        [
                            '_attribute' => [
                            ],
                            '_value' =>  [
                                'SKU' => [
                                    '_attribute' => [
                                    ],
                                    '_value' => $this->prepareSku( $attrValueArray['sku'] )
                                ],
                                'Action' => [
                                    '_attribute' => [
                                    ],
                                    '_value' => 'update'
                                ],
                                'Price' => [
                                    '_attribute' => [
                                    ],
                                    '_value' => $attrValueArray['price']
                                ],
                                'Quantity' => [
                                    '_attribute' => [
                                    ],
                                    '_value' => isset($attrValueArray['quantity']['qty']) ? $attrValueArray['quantity']['qty'] : $attrValueArray['quantity']
                                ],
                                'Status' => [
                                    '_attribute' => [
                                    ],
                                    '_value' => $prodStatus
                                ],
                                'ShippingLeadTimeMin' => [
                                    '_attribute' => [
                                    ],
                                    '_value' => $attrValueArray['lead_time_min']
                                ],
                                'ShippingLeadTimeMax' => [
                                    '_attribute' => [
                                    ],
                                    '_value' => $attrValueArray['lead_time_max']
                                ],
                                'MSRP' => [
                                    '_attribute' => [
                                    ],
                                    '_value' => $attrValueArray['msrp']
                                ]
                            ]
                        ]
                ];
            }
            $path = $this->createDir('houzz', 'var');
            $path = $path['path'] . '/' . 'InventoryFeed.xml';
            $this->xml = $this->objectManager->create('\Magento\Framework\Xml\Generator');
            $this->xml->arrayToXml($inventoryArray)->save($path);
            $response =  $this->postRequest( \Ced\Houzz\Helper\Data::UPDATE_INVENTORY_URL, ['file' => $path]);
            //$paths=$path['path'] . '/' . 'InventoryFeed.xml';
            return $this->checkSaveResponseParse($response, $product, 'inventory',$path);
        }
        return $errorFlag;
    }

    /**
     * @param $productId
     * @return bool|mixed
     */
    public function getProductQty($productId){
        if(is_object($productId))
            $productId = $productId->getId();
        $profile = $this->getCurrentProfile($productId);
        if(!is_array($profile))
            return false;
        $pcode = $profile['profile_code'];
        $stockStatus = $this->scopeConfigManager->getValue($pcode.'/'.'houzzconfiguration/houzz_inventory_settings/advanced_threshold_status');
        if(!$stockStatus){
            $stockStatus = $this->scopeConfigManager->getValue('houzzconfiguration/houzz_inventory_settings/advanced_threshold_status');
            $pcode = '';
        }
        $stockItemRepository = $this->objectManager->create('\Magento\CatalogInventory\Api\StockStateInterface');
        $stockQty = $stockItemRepository->getStockQty($productId);
        if($stockStatus && $stockQty>0){
            $thresholdVal = $this->getConfigData($pcode,'houzzconfiguration/houzz_inventory_settings/inventory_rule_threshold');
            $sendMin = $this->getConfigData($pcode,'houzzconfiguration/houzz_inventory_settings/send_inventory_for_lesser_than_threshold');
            $sendMax = $this->getConfigData($pcode,'houzzconfiguration/houzz_inventory_settings/send_inventory_for_greater_than_threshold');
            if($thresholdVal > $stockQty){
                $stockQty = $sendMin;
            }else{
                $stockQty = $sendMax;
            }
        }
        return $stockQty;

    }

    /**
     * @param null $ids
     * @return bool|mixed|string
     * @throws \DOMException
     */
    public function updatePriceOnHouzz($ids = null)
    {
        $timeStamp = (string)$this->dateTime->gmtTimestamp();
        $priceArray = [
            'PriceFeed' => [
                '_attribute' => [
                    'xmlns:gmp' => "http://houzz.com/",
                ],
                '_value' => [
                    0 => [
                        'PriceHeader' => [
                            'version' => '1.5',
                        ],
                    ],
                ]
            ]
        ];
        $currency = $this->objectManager->create('\Magento\Store\Model\StoreManager')
            ->getStore()
            ->getBaseCurrencyCode();
        $key = 0;
        foreach ($ids as $id) {
            $product = $this->objectManager->create('Magento\Catalog\Model\Product')
                ->load($id)->setStoreId($this->selectedStore);
            if ($product->getVisibility() == 4) {
                if ($product->getTypeId() == 'configurable') {
                    $productType = $product->getTypeInstance();
                    $products = $productType->getUsedProducts($product);
                    foreach ($products as $product) {
                        $key += 1;
                        $price = $this->houzzHelper->getHouzzPrice($product);
                        $priceArray['PriceFeed']['_value'][$key] = [
                            'Price' => [
                                'itemIdentifier' => [
                                    'sku' => $product->getSku()
                                ],
                                'pricingList' => [
                                    'pricing' => [
                                        'currentPrice' => [
                                            'value' => [
                                                '_attribute' => [
                                                    'currency' => $currency,
                                                    'amount' => $price['splprice']
                                                ],
                                                '_value' => [

                                                ]
                                            ]
                                        ],
                                        'currentPriceType' => 'BASE',
                                        'comparisonPrice' => [
                                            'value' => [
                                                '_attribute' => [
                                                    'currency' => $currency,
                                                    'amount' => $price['price']
                                                ],
                                                '_value' => [

                                                ]
                                            ]
                                        ],
                                    ]
                                ]
                            ]
                        ];
                    }
                } elseif ($product->getTypeId() == 'simple') {
                    $key += 1;
                    $price = $this->houzzHelper->getHouzzPrice($product);
                    $priceArray['PriceFeed']['_value'][$key] = [
                        'Price' => [
                            'itemIdentifier' => [
                                'sku' => $product->getSku()
                            ],
                            'pricingList' => [
                                'pricing' => [
                                    'currentPrice' => [
                                        'value' => [
                                            '_attribute' => [
                                                'currency' => $currency,
                                                'amount' => $price['splprice']
                                            ],
                                            '_value' => [

                                            ]
                                        ]
                                    ],
                                    'currentPriceType' => 'BASE',
                                    'comparisonPrice' => [
                                        'value' => [
                                            '_attribute' => [
                                                'currency' => $currency,
                                                'amount' => $price['price']
                                            ],
                                            '_value' => [

                                            ]
                                        ]
                                    ],
                                ]
                            ]
                        ]
                    ];
                }
            }
        }
        $path = $this->createDir('houzz', 'var');
        $path = $path['path'] . '/' . 'PriceFeed.xml';

        $this->xml = $this->objectManager->create('Ced\Houzz\Helper\Custom\Generator');
        $this->xml->arrayToXml($priceArray)->save($path);
        $response = $this->postRequest(self::GET_FEEDS_PRICE_SUB_URL, ['file' => $path]);
        $cpPath =  $this->createDir('houzz', 'media');
        $cpPath = $cpPath['path'].'/'.'PriceFeed_'.$timeStamp.'.xml';
        $paths=$path['path'] . '/' . 'PriceFeed.xml';
        if($this->debugMode) {
            $this->fileIo->cp($path, $cpPath);
            $response = $this->responseParse($response, 'price', 'No Feed File Available. Pleas enable Debug Mode');
            return $response;
        }
        $this->fileIo->cp($path, $cpPath);

        $response = $this->responseParse($response, 'price', $cpPath);
        return $response;
    }

    /**
     * @param $pCode
     * @param $path
     * @return mixed
     */
    public function getConfigData($pCode, $path ){
        $value = $this->scopeConfigManager->getValue($pCode."/".$path);
        if(!$value){
            $value = $this->scopeConfigManager->getValue($path);
        }
        return $value;
    }

    /**
     * @param $productId
     * @throws \Exception
     */

    public function getCurrentProfile($productId){
     $profileId = '';
        if(!$profileId){
            if($profileProduct = $this->objectManager->create('Ced\Houzz\Model\Profileproducts')->loadByField('product_id', $productId)){
                $profileId = $profileProduct->getProfileId();
                if($profileId)
                    $this->_cache->setValue(\Ced\Houzz\Helper\Cache::PROFILE_PRODUCT_CACHE_KEY.$productId, $profileId);
            }else{
                $this->messageManager->addErrorMessage(__('Product with Product Id %1 is not assigned with any profile yet. Associate it with profile first then upload.', $productId));
            }
        }
        //skip this product if the profile is not associated with this product
        if(!$profileId)
            return;
        $this->currentProfileId = $profileId;
        $profile =false;
        //check for the profile for the current product
        if(!isset($this->_profile[$profileId])){
            if($profile = $this->_cache->getValue(\Ced\Houzz\Helper\Cache::PROFILE_CACHE_KEY.$profileId)){
                $this->_profile[$profileId] = $profile;
            }else{

                $profile = $this->objectManager->create('Ced\Houzz\Model\Profile')->load($profileId)->getData();
                $profile['profile_attribute_mapping'] = json_decode($profile['profile_attribute_mapping'], true);
                $this->_cache->setValue(\Ced\Houzz\Helper\Cache::PROFILE_CACHE_KEY.$profileId, $profile);

                $this->_profile[$profileId] = $profile;
            }
        }
        if(isset($profile['profile_code']))
            $this->pcode = $profile['profile_code'];
        $this->selectedStore = $this->getConfigData($this->pcode, 'houzzconfiguration/product_edit/houzz_storeid');
        $this->selectedStore = !empty($this->selectedStore) ? $this->selectedStore : 0 ;

        $this->fulfillmentLagTime = $this->getConfigData($this->pcode,'houzzconfiguration/productinfo_map/houzz_fullfillment_lagtime');
        if (!is_numeric($this->fulfillmentLagTime) || empty($this->fulfillmentLagTime)) {
            $this->fulfillmentLagTime = '1';
        }
        $this->productIdType = $this->getConfigData($this->pcode, 'houzzconfiguration/productinfo_map/houzz_productid_type');
        return $this->_profile[$this->currentProfileId];
    }

    /**
     * @param array $ids
     * @param bool $statusCheck
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateAllProducts($ids = [], $statusCheck = true)
    {
        $cacheArray = array();
        $validatedProducts = [];
        $cache = $this->objectManager->create('\Magento\Framework\App\Cache');
        //$cacheArray = !is_null($cache->load('ced_validate')) ? json_decode($cache->load('ced_validate'),true) : array();

        foreach ($ids as $id) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->objectManager->create('Magento\Catalog\Model\Product')
                ->load($id)
                ->setStoreId($this->selectedStore);
            if ($product->getTypeId() == 'configurable' &&
                $product->getVisibility() != 1) {
                $parentError = false;
                $configurableProductObject = $product;
                $errorsForConfigurable = [];
                $productid = $this->validateProduct($product->getId(), $product, $statusCheck);
                if (isset($productid['errors'])){
                    $parentError = true;
                    $validatedProducts['error'][$product->getSku()] = $productid['errors'];
                    $errorsForConfigurable[] = $productid['errors'];
                }
                $sku = $product->getSku();
                $parentId = $product->getId();
                /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productType */
                $productType = $product->getTypeInstance();
                /** @var array $products */
                $products = $productType->getUsedProducts($product);
                $attributes = $productType->getConfigurableAttributesAsArray($product);
                foreach ($attributes as $attribute) {
                    $variantAttr[] = $attribute['attribute_code'];
                }

                $houzzVariantAttr = $profile = [];

                if (!empty($parentId) && $parentId!=null) // if config product
                    $profile =$this->getCurrentProfile($parentId);
                elseif (empty($profile))
                    $profile = $this->getCurrentProfile($id); //if simple product
                $key = 0;

                $houzzVariantAttr = isset($profile['profile_attribute_mapping']['variant_attributes']) ? $profile['profile_attribute_mapping']['variant_attributes'] : [];
                $notMappedVariantAttribute = array();
                $houzzVariantAttrCustom = array_flip(array_column($houzzVariantAttr, 'magento_attribute_code'));


                foreach ($variantAttr as $code){
                    if(!isset($houzzVariantAttrCustom[$code]))
                        $notMappedVariantAttribute[] = $code;
                }

                foreach ($products as $product) {

                    if(isset($cacheArray[$product->getId()]) && !empty($cacheArray[$product->getId()])) {
                        $productid = $cacheArray[$product->getId()];
                    } else {
                        $productid = $this->validateProduct($product->getId(), null, $statusCheck, $parentId);
                    }
                    if (isset($productid['id'])) {
                        // load current product profile
                        //Check if all mappedAttributes are mapped
                        if (count($notMappedVariantAttribute)==0) {
                            $validatedProducts[$product->getId()]['id'] = $productid['id'];
                            $validatedProducts[$product->getId()]['status'] = $productid['status'];
                            $validatedProducts[$product->getId()]['type'] = 'configurable';
                            $validatedProducts[$product->getId()]['variantid'] = $sku;
                            $validatedProducts[$product->getId()]['parentid'] = $parentId;
                            $houzzVariantAttrArray = [];
                            foreach ($houzzVariantAttr as $value) {
                                $attribute = explode('/', $value['houzz_attribute_name']);
                                $houzzVariantAttrArray[] =  $attribute[0];
                            }
                            $validatedProducts[$product->getId()]['variantattr'] = $houzzVariantAttrArray;
                            $validatedProducts[$product->getId()]['variantattrmapped'] = $houzzVariantAttr;
                            $validatedProducts[$product->getId()]['isprimary'] = 'false';
                            if ($key == 0) {
                                $validatedProducts[$product->getId()]['isprimary'] = 'true';
                                $key = 1;
                            }
                            $cacheArray[$product->getId()] = $validatedProducts[$product->getId()];
                        } else {
                            $productid['errors'] = [
                                'sku' => $product->getSku(),
                                'id' => $product->getId(),
                                'url' => $product->getId(),
                                'errors' => [
                                    'Variant Attributes' =>
                                        implode(",", $notMappedVariantAttribute) .
                                        ' - Some of Configurable Attributes are not mapped.'
                                ]
                            ];
                            $errorsForConfigurable[] =   $productid['errors'];
                            continue;
                        }
                    } elseif (isset($productid['errors'])) {
                        if(count($notMappedVariantAttribute) > 0) {
                            $productid['errors']['errors']['Variant Attributes'] = implode(",", $notMappedVariantAttribute) .
                                ' - Some of Configurable Attributes are not mapped.' ;
                        }
                        $errorsForConfigurable[] = $productid['errors'];
                    }
                }
                if (count($errorsForConfigurable) > 0) {
                    $validatedProducts['errors'][$configurableProductObject->getSku()]= $errorsForConfigurable;
                    /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productType */
                    $configurableProductObject->setData('houzz_validation_errors',$this->json->jsonEncode($errorsForConfigurable));
                    $configurableProductObject->setHouzzProductValidation('Invalid');
                    $configurableProductObject->getResource()->saveAttribute($configurableProductObject,'houzz_validation_errors')->saveAttribute($configurableProductObject,'houzz_product_validation');
                } else {
                    $configurableProductObject->setHouzzValidationErrors(NULL);
                    $configurableProductObject->setHouzzProductValidation('Valid');
                    $configurableProductObject->getResource()->saveAttribute($configurableProductObject,'houzz_product_validation')->saveAttribute($configurableProductObject,'houzz_validation_errors');
                }
            } elseif ($product->getTypeId() == 'simple' /*&& $product->getVisibility() != 1*/ ) {

                if(isset($cacheArray[$product->getId()]) && !empty($cacheArray[$product->getId()])) {
                    $productid = $cacheArray[$product->getId()];
                } else {
                   // $this->messageManager->addErrorMessage('Product SKU '.$product->getSku().' associated to profile '.$this->_profile[$this->currentProfileId]['profile_code'].' is disabled so skip from houzz upload.');
                    $productid = $this->validateProduct($product->getId(), $product, $statusCheck);
                }


                if (isset($productid['id'])) {
                    $cacheArray[$product->getId()] = $validatedProducts[$product->getId()] = [
                        'id' => $productid['id'],
                        'status' => $productid['status'],
                        'type' => 'simple',
                        'variantid' => null,
                        'variantattr' => null
                    ];
                }

                if (isset($productid['errors'])){
                    $validatedProducts['errors'][$product->getSku()] = $productid['errors'];
                }
            }
        }

        $cache->save($this->json->jsonEncode($cacheArray),'ced_validate');

        return $validatedProducts;
    }

    /**
     * @param $id
     * @param null $product
     * @param bool $statusCheck
     * @param null $parentProductId
     * @return bool
     * @throws \Exception
     */
    public function validateProduct($id, $product = null, $statusCheck = true, $parentProductId = null)
    {
        $configDefaultValues = $this->scopeConfigManager->getValue('houzzconfiguration/productinfo_map/config_prod_default_values');
        $configDefaultValues = unserialize($configDefaultValues);
        $configDefaultValues = (is_array($configDefaultValues)) ? array_column($configDefaultValues, 'default_value', 'attribute_code') : array();
        //check for the current profile
        if(!empty($parentProductId) && $parentProductId!=null) // if config product
        {
            $profile=$this->getCurrentProfile($parentProductId);
        } else {
        $profile= $this->getCurrentProfile($id); //if simple product

        }


        $validatedProduct = false;
        //if product object is not passed, then load in case of Simple product
        if ($product == null) {
            $product = $this->objectManager->create('Magento\Catalog\Model\Product')
                ->load($id)
                ->setStoreId($this->selectedStore);
        }

        if(!$profile['profile_status']){
            $this->messageManager->addErrorMessage('Product SKU '.$product->getSku(). 'Current profile is not Enable.');
            $validatedProduct['errors'] = 'Current profile is not Enable.';
            return $validatedProduct;
        }
        if(!$this->currentProfileId){
            $this->messageManager->addErrorMessage('Product SKU '.$product->getSku(). 'is not assigned to any profile.');
            $validatedProduct['errors'] = 'Not Assigned to any profile.';
            return $validatedProduct;
        }
        if(isset($this->_profile[$this->currentProfileId]['profile_status'])
            && $this->_profile[$this->currentProfileId]['profile_status']==0){
            $this->messageManager->addErrorMessage('Product SKU '.$product->getSku().' associated to profile '.$this->_profile[$this->currentProfileId]['profile_code'].' is disabled so skip from houzz upload.');
            $validatedProduct['errors'] = 'Profile '.$this->_profile[$this->currentProfileId]['profile_code'] .' is disabled';
            return $validatedProduct;
        }
        $magentoAttributes = $this->getHouzzAttributes($id, ['required' => true, 'mapped' => true,
            'validation' => true], $product);
        //Case 1: Category is Mapped
        if (!empty($magentoAttributes)) {
            $category = $magentoAttributes["category"];
            $magentoAttributes = $magentoAttributes["attributes"];
            $productArray = $product->toArray();
            $productArray['blank'] = '';
            $sku = '';
            if (isset($productArray['sku'])) {
                $sku = $productArray['sku'];
            }
            $attributesEmpty = [];
            foreach ($magentoAttributes as $houzzAttribute => $magentoAttribute) {
                if($houzzAttribute == 'upc' && !empty($productArray[$magentoAttribute['magento_attribute_code']])) {
                    //Code for UPC Check Digit Validation
                    $flag = $this->validateProductID($productArray[$magentoAttribute['magento_attribute_code']]);
                    if(!$flag) {
                        $attributesEmpty[] = $houzzAttribute . ' : Invalid UPC';
                    }
                }
                elseif (!isset($productArray[$magentoAttribute['magento_attribute_code']])
                    || empty($productArray[$magentoAttribute['magento_attribute_code']])
                ) {

                    if(!empty($magentoAttribute['default'])) {
                        continue;
                    }

                    if($houzzAttribute=='assembly_required' || $houzzAttribute=='AssemblyRequired'){
                        continue;
                    }

                    if($product->getTypeId() == 'configurable' && isset($configDefaultValues[$houzzAttribute]) && $configDefaultValues[$houzzAttribute] != ''){
                        continue;
                    }

                    //assembly_required
                    $attributesEmpty["$houzzAttribute"] = 'Required-Attribute-Empty';

                } elseif (isset($magentoAttribute['houzz_attribute_type'])) {
                    //Case 2: if the attribute value is set, then max length check
                    $maxlength = explode(',', $magentoAttribute['houzz_attribute_type']);
                    if (isset($maxlength[1]) &&
                        strlen($productArray[$magentoAttribute['magento_attribute_code']]) > $maxlength[1]
                    ) {
                        $length = strlen(htmlspecialchars($productArray[$magentoAttribute['magento_attribute_code']]));
                        $attributesEmpty["$houzzAttribute"] = $length . ' MaxLength-' . $maxlength[1] . '-Exceded';
                    }
                }
            }

            //Setting Errors in product validation attribute
            if (count($attributesEmpty) > 0) {
                //  $attributesEmpty = implode(',', $attributesEmpty);
                $attributesEmpty = [
                    "sku" => "$sku",
                    "id" => "$id",
                    "url" => "$id",
                    "errors" => $attributesEmpty
                ];
                $validatedProduct['errors'] = $attributesEmpty;
                $attributesEmpty = $this->json->jsonEncode([$attributesEmpty]);
                $product->setData('houzz_validation_errors', $attributesEmpty);
                $product->setData('houzz_product_validation', 'Invalid');
            } else {
                $product->setData('houzz_product_validation', 'Valid');
                $product->setData('houzz_validation_errors', NULL);
                $validatedProduct['id'] = $id;
                $validatedProduct['category'] = $category;
            }
            $validatedProduct['status'] = 'UNPUBLISHED';
            if ($statusCheck) {
                //Checking current Houzz Product Status by hitting the get Item API
                //$validatedProduct['status'] = $this->getItem($product->getSku(), 'publishedStatus');
                $product->setData('houzz_product_status', $validatedProduct['status']);
            }
            /** @var \Magento\Catalog\Model\Product $product */
            $product->getResource()/*->saveAttribute($product,'houzz_product_status')*/
                ->saveAttribute($product,'houzz_product_validation')
                ->saveAttribute($product,'houzz_validation_errors');
            return $validatedProduct;
        }
        //Case 2: Category Not Mapped
        $sku = $product->getSku();
        $attributesEmpty = [
            "sku" => "$sku",
            "id" => "$id",
            "url" => "$id",
            "errors" =>
                [
                    "Category Not Mapped" => "Product's Magento Category is not Mapped with any Houzz Category"
                ]
        ];
        $validatedProduct['errors'] = $attributesEmpty;
        $attributesEmpty = $this->json->jsonEncode([$attributesEmpty]);
        /** @var \Magento\Catalog\Model\Product $product */
        $product->setData('houzz_product_validation', $attributesEmpty);
        $product->getResource()->saveAttribute($product,'houzz_product_validation');
        return $validatedProduct;
    }

    /**
     * @param $productID
     * @return bool
     */
    public function validateProductID($productID) {
        if (preg_match('/[^0-9]/', $productID))
        {
            // is not numeric
            return false;
        }
        // pad with zeros to lengthen to 14 digits
        switch (strlen($productID))
        {
            case 8:
                $productID = "000000".$productID;
                break;
            case 12:
                $productID = "00".$productID;
                break;
            case 13:
                $productID = "0".$productID;
                break;
            case 14:
                break;
            default:
                // wrong number of digits
                return false;
        }
        // calculate check digit
        $a = '';
        $a[0] = (int)($productID[0]) * 3;
        $a[1] = (int)($productID[1]);
        $a[2] = (int)($productID[2]) * 3;
        $a[3] = (int)($productID[3]);
        $a[4] = (int)($productID[4]) * 3;
        $a[5] = (int)($productID[5]);
        $a[6] = (int)($productID[6]) * 3;
        $a[7] = (int)($productID[7]);
        $a[8] = (int)($productID[8]) * 3;
        $a[9] = (int)($productID[9]);
        $a[10] = (int)($productID[10]) * 3;
        $a[11] = (int)($productID[11]);
        $a[12] = (int)($productID[12]) * 3;
        $sum = $a[0] + $a[1] + $a[2] + $a[3] + $a[4] + $a[5] + $a[6] + $a[7] + $a[8] + $a[9] + $a[10] + $a[11] + $a[12];
        $check = (10 - ($sum % 10)) % 10;
        // evaluate check digit
        $last = (int)($productID[13]);
        return $check == $last;
    }


    /**
     * @param $productId
     * @param array $params
     * @param null $product
     * @return array|bool
     * @throws \Exception
     */
    public function getHouzzAttributes($productId, $params =
    ['required' => true, 'mapped' => false, 'validation' => false], $product=null
    ) {
        // load current product profile
        $this->getCurrentProfile($productId);
        $profile = $this->_profile[$this->currentProfileId];
        $attributes = $catData =[];
        $attributes = [];
        //Case 1 when required param is true and other false
        if(isset($params['required'], $params['mapped'], $params['validation'])){
            switch ($params){
                case $params['required'] == true && $params['mapped'] == true && $params['validation'] == true:
                {
                    $attributes = [];
                    if(isset($profile['profile_attribute_mapping']['required_attributes']))
                        foreach ($profile['profile_attribute_mapping']['required_attributes'] as $value) {
                            $attributes[$value['houzz_attribute_name']] = $value;
                        }

                    return ["attributes" => $attributes, "category" => $profile['profile_category_level_1']];
                }
                case $params['required'] == false && $params['mapped'] == true && $params['validation'] == true:
                {

                    $attributes = [];
                    if(isset($profile['profile_attribute_mapping']['required_attributes']))
                        $attributes = $profile['profile_attribute_mapping']['required_attributes'];
                    if(isset($profile['profile_attribute_mapping']['optional_attributes']))
                        $attributes = array_merge_recursive($attributes,$profile['profile_attribute_mapping']['optional_attributes']);
                    return ["attributes" => $attributes, "category"=> [ "parent_cat_id" => $profile['profile_category_level_1']/*,  "cat_id" => $profile['profile_category_level_2']*/]];
                }
                case $params['required'] == false && $params['mapped'] == true && $params['validation'] == false:
                {
                    $attributes = [];
                    if(isset($profile['profile_attribute_mapping']['required_attributes']))
                        foreach ($profile['profile_attribute_mapping']['required_attributes'] as $value) {
                            $attributes[$value['houzz_attribute_name']] = $value['magento_attribute_code'];
                        }
                    if(isset($profile['profile_attribute_mapping']['optional_attributes']))
                        foreach ($profile['profile_attribute_mapping']['optional_attributes'] as $value) {
                            $attributes[$value['houzz_attribute_name']] = $value['magento_attribute_code'];
                        }
                    return ["attributes" => $attributes, "category"=> [ "parent_cat_id" => $profile['profile_category_level_1']/*,  "cat_id" => $profile['profile_category_level_2']*/]];
                }
            }
        }
        return false;
    }

    /**
     * @param string $response
     * @param null $product
     * @param $type
     * @return bool|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkSaveResponseParse($response = '', $product = null, $type ,$paths)
    {
        if(!$this->session->getAllBatchCompleted()) {
            $data = array();
            if($response != null) {
                $data = $this->parser->loadXML($response)->xmlToArray();
            }
            reset($data); // make sure array pointer is at first element
            $firstKey = key($data);

            if(empty($this->session->getResponseSession()))
                $this->session->setResponseSession([]);
            $responseParseArr = $this->session->getResponseSession();
            if (isset($data[$firstKey]['Errors'])) {
                array_push($responseParseArr, [$product->getSku() => $data[$firstKey]['Errors']]);
                $this->session->setResponseSession($responseParseArr);
                return false;
            }
            return true;
        } else {
            $data = array();
            if($response != null) {
                $data = $this->parser->loadXML($response)->xmlToArray();
            }
            if(empty($this->session->getResponseSession()))
                $this->session->setResponseSession([]);
            reset($data); // make sure array pointer is at first element
            $firstKey = key($data);
            $responseParseArr = $this->session->getResponseSession();

            if (isset($data[$firstKey]['Errors'])) {
                array_push($responseParseArr, [$product->getSku() => $data[$firstKey]['Errors']]);
            }
            $path = $this->createDir('houzz' ,'media');
            $timeStamp = (string)$this->dateTime->gmtTimestamp();
            $cpPath = $path['path'] . '/' . $type . '_'.$timeStamp.'.xml';
            if(!empty($responseParseArr)) {
                if($this->debugMode){
                    $this->fileIo->cp($path['path'].'/'.$type.'.xml', $cpPath);

                    $response = $this->responseParseCompleted($responseParseArr, $type, $cpPath ,$paths);
                    $this->session->unsResponseSession();
                    $this->session->unsAllBatchCompleted();

                    if (isset($data[$firstKey]['Errors']) || count($data) <= 0) {
                        return false;
                    }else{
                        return true;
                    }
                }
                $response = $this->responseParseCompleted($responseParseArr, $type, 'No File Available . Please Enable Debug Mode');
                $this->session->unsResponseSession();
                $this->session->unsAllBatchCompleted();

                if (isset($data[$firstKey]['Errors'])) {
                    return false;
                }
            }
            return true;
        }
    }

    /**
     * Response Parse and Save to db
     * $response =
     *'<ns2:FeedAcknowledgement xmlns:ns2="http://houzz.com/">
     * <ns2:feedId>F34AC08BE61843E59739C665C5761D0C@AQMB_wA</ns2:feedId></ns2:FeedAcknowledgement>';
     * @param string $response
     * @param string $type
     * @param string $filePath
     * @return string|[]
     */
    public function responseParse($response = '', $type = null, $filePath = '' )
    {
        if ($type) {
            $feedModel = $this->objectManager->create('\Ced\Houzz\Model\Feeds');
            $data = str_replace('ns2:', "", $response);
            try {
                $data = $this->parser->loadXML($data)->xmlToArray();
                reset($data); // make sure array pointer is at first element
                $firstKey = key($data);
                $feedModel->setData('feed_id', rand());
                $feedModel->setData('feed_status', $data[$firstKey]['Ack']);
                $feedModel->setData('feed_date', date( 'Y-m-d H:i:s'));
                $feedModel->setData('feed_type', $type);
                if($data[$firstKey]['Ack'] == "Error") {
                    $feedModel->setData('feed_errors', $this->json->jsonEncode($data[$firstKey]['Errors']));
                }
                $feedModel->setData('feed_file', $filePath);

                $this->xml->arrayToXml($data)->save($filePath);
                if($data[$firstKey]['Ack'] == "Error") {
                    return false;
                } else {
                    return true;
                }
            }
            catch (\Exception $e) {
                if ($this->debugMode) {
                    $this->houzzLogger->
                    logger(
                        $type,
                        "responseParse",
                        $e->getMessage(),
                        "Parse  response error Exception"
                    );
                }
            }

        }
        return true;
    }


    /**
     * Response Parse Completed and Save to db
     * $response =
     *'<ns2:FeedAcknowledgement xmlns:ns2="http://houzz.com/">
     * <ns2:feedId>F34AC08BE61843E59739C665C5761D0C@AQMB_wA</ns2:feedId></ns2:FeedAcknowledgement>';
     * @param string $response
     * @param string $type
     * @param string $filePath
     * @return string|[]
     */
    public function responseParseCompleted($response = '', $type = null, $filePath = '' ,$paths = '')
    {
        if ($type) {
            $feedModel = $this->objectManager->create('\Ced\Houzz\Model\Feeds');
            $parser = $this->objectManager->create('\Magento\Framework\Xml\Parser');
            try {
                reset($response); // make sure array pointer is at first element
                $firstKey = key($response);
                $feedModel->setData('feed_id', rand());
                $feedModel->setData('feed_status', "Error");
                $feedModel->setData('feed_date', date( 'Y-m-d H:i:s'));
                $feedModel->setData('feed_type', $type);
                if(!empty($response)) {
                    $feedModel->setData('feed_errors', $this->json->jsonEncode($response));
                }
                $feedModel->setData('feed_file', $filePath);
                $feedModel->save();
                $this->fileIo->cp($paths,$filePath);
                if(empty($response)) {
                    return false;
                } else {
                    return true;
                }
            }
            catch (\Exception $e) {
                if ($this->debugMode) {
                    $this->houzzLogger->
                    logger(
                        $type,
                        "responseParse",
                        $e->getMessage(),
                        "Parse  response error Exception"
                    );
                }
            }

        }
        return true;
    }



    /**
     * To Convert Escaped Characters in XML to HTML chars
     * @param string $path
     * @return bool
     */
    public function unEscapeData($path)
    {
        if ($this->fileIo->fileExists($path)) {
            $data = $this->fileIo->read($path);
            $data = htmlspecialchars_decode($data);
            //$data = preg_replace( "/\r|\n|\t/", "", $data );
            $this->fileIo->write($path, $data);
        }
        return false;
    }

    /**
     * Prepare Additional Attributes for Variations
     * @param $attributes
     * @param $product
     * @return array|bool
     */
    public function prepareAdditionalAttributes($attributes, $additionalAttributes, $product)
    {
        if (count($attributes) > 0) {
            if (!isset($additionalAttributes['_value'])) {
                $additionalAttributes = [
                    '_attribute' => [],
                    '_value' => []
                ];
            }
            foreach ($attributes as $attribute) {
                $attr = $product->getResource()->getAttribute($attribute['magento_attribute_code']);
                if ($attr && ($attr->usesSource() || $attr->getData('frontend_input')=='select')) {
                    $productAttributeValue =
                        $attr->getSource()->getOptionText($product->getData($attribute['magento_attribute_code']));
                    if ($productAttributeValue == 'No') {
                        $productAttributeValue = 'false';
                    } elseif ($productAttributeValue == 'Yes') {
                        $productAttributeValue = 'true';
                    }
                }
                $additionalAttributes['_value'][]['Attribute'] =
                    array(
                        '_attribute' => array(
                        ),
                        '_value' => array(
                            'Type' => array(
                                '_attribute' => array(
                                ),
                                '_value' => $attribute['houzz_attribute_name']
                            ),
                            'Value' => array(
                                '_attribute' => array(
                                ),
                                '_value' => $productAttributeValue
                            )
                        )
                    );
            }
        }
        return $additionalAttributes;
    }

    /**
     * Prepare Shipping Overrides
     * @param $product
     * @return array
     */
    public function checkForConfiguration()
    {
        $apiFlag = $this->scopeConfigManager->getValue('');
        $validationFlag = $this->scopeConfigManager->getValue('houzzconfiguration/houzzsetting/validate_details');
        if($apiFlag && $validationFlag) {
            return true;
        }
        return false;
    }

    /**
     * @param array $params
     * @return bool|mixed
     */
    public function validateAPI($params = [])
    {
        try{
            $url = $this->apiJsonUrl.self::GET_ORDERS_URL;
            //setting request headers
            $headers = array(
                "X-HOUZZ-API-SSL-TOKEN: ".trim($this->apiToken),
                "X-HOUZZ-API-USER-NAME: ".trim($this->apiUsername),
                "X-HOUZZ-API-APP-NAME: ".trim($this->apiAppName)
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $serverOutput = curl_exec($ch);
            $curlError = curl_error($ch);
            curl_close($ch);
            return $serverOutput;
        } catch(\Exception $e) {
            if($this->debugMode)
                $this->houzzLogger->logger(
                    'Get Request',
                    'Exception In Function',
                    $e->getMessage(),
                    'houzz->Helper->Data.php : GetRequest()'
                );
            return false;
        }
    }

    public function uploadConfigProd($id, $product, $action = 'AddListingRequest') {
        $configDefaultValues = $this->scopeConfigManager->getValue('houzzconfiguration/productinfo_map/config_prod_default_values');
        $configDefaultValues = unserialize($configDefaultValues);
        $attributes =  $customAttrs = array();
        if (isset($id['parentid'])) {
            $addAttr = implode(", ",array_column($id['variantattrmapped'], 'houzz_attribute_name'));
            //all simple attributes needed here (Case 1)
            $this->getCurrentProfile($id['parentid']);
            $attributes = $this->getHouzzAttributes($id['parentid'], [
                'required' => false, 'mapped' => true, 'validation' => false
            ]);
            $customAttrs = $this->getHouzzAttributes($id['parentid'], [
                'required' => false, 'mapped' => true, 'validation' => true
            ])['attributes'];
        } else {
            //all simple attributes needed here (Case 1)
            $this->getCurrentProfile($id['id']);
            $attributes = $this->getHouzzAttributes($id['id'], [
                'required' => false, 'mapped' => true, 'validation' => false
            ]);
            $customAttrs = $this->getHouzzAttributes($id['id'], [
                'required' => false, 'mapped' => true, 'validation' => true
            ])['attributes'];
        }
        $category = $attributes['category'];
        $attributes = $attributes["attributes"];
        $counter = 0;
        foreach ($customAttrs as $customAttr) {
            if ($customAttr['magento_attribute_code'] == 'default') {
                $product->setData('ced_'.$counter, $customAttr['default']);
                $attributes[$customAttr['houzz_attribute_name']] = 'ced_'.$counter;
                $counter++;
            }
        }
        $attrValueArray = [];
        foreach($attributes as $attrKey => $attrValue) {
            $attrValueArray[$attrKey] = $this->getMagentoProductAttributeValue($product, $attrKey, $attributes);
        }
        $productArray = $product->toArray();
        foreach($attributes as $attrKey => $attrValue) {
            if($attrValue == 'default') {
                $attrValueArray[$attrKey] = $customAttrs[$attrKey]['default'];
                continue;
            }
            $attrValueArray[$attrKey] = isset($productArray[$attrValue]) ? $productArray[$attrValue] : '';
        }

        $attrValueArray['category_id']=$category['parent_cat_id'];

        foreach ($configDefaultValues as $configDefaultValue) {
            if(isset($attrValueArray[$configDefaultValue['attribute_code']]) && $attrValueArray[$configDefaultValue['attribute_code']] == '')
                $attrValueArray[$configDefaultValue['attribute_code']] = $configDefaultValue['default_value'];
        }

        $listingArr = [
            'Listing' => [
                '_attribute' => [
                ],
                '_value' => [
                    'Title' => [
                        '_attribute' => [
                        ],
                        '_value' => htmlentities($attrValueArray['title'])
                    ],
                    'Description' => [
                        '_attribute' => [
                        ],
                        '_value' => htmlentities($attrValueArray['description'])
                    ],
                    'AssemblyRequired' => [
                        '_attribute' => [
                        ],
                        '_value' =>(string)$attrValueArray['assembly_required']
                    ],
                    'MinimumOrderQuantity' => [
                        '_attribute' => [
                        ],
                        '_value' => $attrValueArray['MinimumOrderQuantity']
                    ],
                    'SKU' => [
                        '_attribute' => [
                        ],
                        '_value' => $this->prepareSku( $attrValueArray['sku'] )
                    ],
                    'UPC' => [
                        '_attribute' => [
                        ],
                        '_value' => $attrValueArray['upc']
                    ],
                    /*'Images' => [
                        '_attribute' => [
                        ],
                        '_value' => [
                            'ImageLink' => [
                                '_attribute' => [
                                ],
                                '_value' => 'http://sample.li/castle.jpg'
                            ]
                        ]
                    ],*/
                    'CategoryId' => [
                        '_attribute' => [
                        ],
                        '_value' => $attrValueArray['category_id']
                    ],
                    'Price' => [
                        '_attribute' => [
                        ],
                        '_value' => $attrValueArray['price']
                    ],
                    'Currency' => [
                        '_attribute' => [
                        ],
                        '_value' => $attrValueArray['currency']
                    ],
                    'Manufacturer' => [
                        '_attribute' => [
                        ],
                        '_value' => $attrValueArray['manufacturer']
                    ],
                    /*'Style' => [
                        '_attribute' => [
                        ],
                        '_value' => $attrValueArray['style']
                    ],*/
                    'MSRP' => [
                        '_attribute' => [
                        ],
                        '_value' => $attrValueArray['msrp']
                    ],
                    'Quantity' => [
                        '_attribute' => [
                        ],
                        '_value' => isset($attrValueArray['quantity']['qty']) ? $attrValueArray['quantity']['qty'] : $attrValueArray['quantity']
                    ],
                    /*'Status' => [
                        '_attribute' => [
                        ],
                        '_value' => $attrValueArray['status']
                    ],*/
                    'ProductSpec' => [
                        '_attribute' => [
                        ],
                        '_value' => [
                            'Width' => [
                                '_attribute' => [
                                ],
                                '_value' => $attrValueArray['width']
                            ],
                            'Height' => [
                                '_attribute' => [
                                ],
                                '_value' => $attrValueArray['height']
                            ],
                            /*'Depth' => [
                                '_attribute' => [
                                ],
                                '_value' => $attrValueArray['depth']
                            ],*/
                            'Weight' => [
                                '_attribute' => [
                                ],
                                '_value' => $attrValueArray['weight']
                            ],
                            'DimensionsUnit' => [
                                '_attribute' => [
                                ],
                                '_value' => $attrValueArray['dimensions_unit']
                            ],
                            'WeightUnit' => [
                                '_attribute' => [
                                ],
                                '_value' => $attrValueArray['weight_unit']
                            ]
                        ]
                    ],
                    'ShippingDetails' => [
                        '_attribute' => [
                        ],
                        '_value' => [
                            'Packages' => [
                                '_attribute' => [
                                ],
                                '_value' => [
                                    'Package' => [
                                        '_attribute' => [
                                        ],
                                        '_value' => [
                                            'Width' => [
                                                '_attribute' => [
                                                ],
                                                '_value' => $attrValueArray['package_width']
                                            ],
                                            'Height' => [
                                                '_attribute' => [
                                                ],
                                                '_value' => $attrValueArray['package_height']
                                            ],
                                            'Depth' => [
                                                '_attribute' => [
                                                ],
                                                '_value' => $attrValueArray['package_depth']
                                            ],
                                            'Weight' => [
                                                '_attribute' => [
                                                ],
                                                '_value' => $attrValueArray['package_weight']
                                            ],
                                        ]
                                    ]
                                ]
                            ],
                            'ShippingOptions' => [
                                '_attribute' => [
                                ],
                                '_value' => [
                                    'ShippingOption' => [
                                        '_attribute' => [
                                        ],
                                        '_value' => [
                                            'Country' => [
                                                '_attribute' => [
                                                ],
                                                '_value' => $attrValueArray['shipping_country']
                                            ],
                                            'Type' => [
                                                '_attribute' => [
                                                ],
                                                '_value' => $attrValueArray['shipping_type']
                                            ],
                                            'Price' => [
                                                '_attribute' => [
                                                ],
                                                '_value' =>$attrValueArray['shipping_price']
                                            ],
                                        ]
                                    ]
                                ]
                            ],
                            'LeadTimeMin' => [
                                '_attribute' => [
                                ],
                                '_value' => $attrValueArray['lead_time_min']
                            ],
                            'FreightItem' => [
                                '_attribute' => [
                                ],
                                '_value' => $attrValueArray['FreightItem']
                            ],
                            'LeadTimeMax' => [
                                '_attribute' => [
                                ],
                                '_value' => $attrValueArray['lead_time_max']
                            ],
                            'PackageDimensionsUnit' => [
                                '_attribute' => [
                                ],
                                '_value' => $attrValueArray['package_dimensions_unit']
                            ],
                            'PackageWeightUnit' => [
                                '_attribute' => [
                                ],
                                '_value' => $attrValueArray['package_weight_unit']
                            ],
                        ]
                    ],
                ]
            ]
        ];
        if(isset($attrValueArray['Keywords']) && $attrValueArray['Keywords'] != null) {
            $listingArr['Listing']['_value']['Keywords']['_attribute'] = array();
            $listingArr['Listing']['_value']['Keywords']['_value'] = $attrValueArray['Keywords'];
        }
        if(isset($attrValueArray['Prop65WarningType']) && $attrValueArray['Prop65WarningType'] != null) {
            $listingArr['Listing']['_value']['Prop65WarningType']['_attribute'] = array();
            $listingArr['Listing']['_value']['Prop65WarningType']['_value'] = $attrValueArray['Prop65WarningType'];
        }
        if (isset($id['parentid'])) {
            $listingArr['Listing']['_value']['VariationTheme']['_attribute'] =
            $listingArr['Listing']['_value']['Parentage']['_attribute'] = array();
            $listingArr['Listing']['_value']['VariationTheme']['_value'] = $addAttr;
            $listingArr['Listing']['_value']['Parentage']['_value'] = 'Parent';
        }
        $productImages = $product->getMediaGalleryImages();
        $imgIndex = 0;
        foreach ($productImages as $image) {
            if($imgIndex >= 5) {
                break;
            }
            $listingArr['Listing']['_value']['Images']['_attribute'] = array();
            $listingArr['Listing']['_value']['Images']['_value'][$imgIndex]['ImageLink']['_attribute'] = array();
            $listingArr['Listing']['_value']['Images']['_value'][$imgIndex]['ImageLink']['_value'] = $image->getUrl();
            $imgIndex++;
        }
        $productToUpload = [
            $action =>
                [
                    '_attribute' => [
                    ],
                    '_value' => $listingArr
                ]
        ];
        $path = $this->createDir('houzz', 'var');
        $this->xml->arrayToXml($productToUpload)->save($path['path'] . '/' . 'MPProduct.xml');
        $this->unEscapeData($path['path'] . '/' . 'MPProduct.xml');// return true;
        if($action == 'UpdateListingRequest')
            $actionUrl = self::UPDATE_LISTING_URL;
        else
            $actionUrl = self::ADD_LISTING_URL;
        $response =  $this->postRequest( $actionUrl, [ 'file' => $path['path'] . '/' . 'MPProduct.xml']);
        $paths=$path['path'] . '/' . 'MPProduct.xml';
        return $this->checkSaveResponseParse($response, $product, 'item',$paths);
    }


    public function prepareSku($sku) {
        $replaceStringArr = $this->replaceStringArr;
        foreach ($replaceStringArr as $stringToFind => $stringToReplace) {
            $sku = str_replace($stringToFind, $stringToReplace, $sku);
        }
        return $sku;
    }

}







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
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Houzz\Helper;

use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Boolean;

class Order extends \Magento\Framework\App\Helper\AbstractHelper
{
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
	 * Store Manager
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	public $storeManager;

	/**
	 * Houzz Orders Model
	 * @var \Ced\Houzz\Model\ResourceModel\HouzzOrders\CollectionFactory
	 */
	public $houzzOrder;

	/**
	 * Customer Repository
	 * @var \Magento\Customer\Api\CustomerRepositoryInterface
	 */
	public $customerRepository;

	/**
	 * Product Repository
	 * @var \Magento\Catalog\Model\ProductRepository
	 */
	public $productRepository;

	/**
	 * Message Manager
	 * @var \Magento\Framework\Message\ManagerInterface
	 */
	public $messageManager;

	/**
	 * Catalog Product Model
	 * @var \Magento\Catalog\Model\ProductFactory
	 */
	public $product;

	/**
	 * Customer Factory
	 * @var \Magento\Customer\Model\CustomerFactory
	 */
	public $customerFactory;

	/**
	 * Houzz Data Helper
	 * @var \Ced\Houzz\Helper\Data
	 */
	public $datahelper;

	/*
	 * HouzzOrders Resouce Connection
	 * @var ZendConnection
	 */
	public $connection;
	public $parser;

	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Quote\Model\QuoteFactory $quote,
		\Magento\Quote\Model\QuoteManagement $quoteManagement,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Customer\Model\CustomerFactory $customerFactory,
		\Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
		\Magento\Catalog\Model\ProductRepository $productRepository,
		\Magento\Sales\Model\Service\OrderService $orderService,
		\Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoaderFactory $creditmemoLoaderFactory,
		\Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
		\Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
		\Magento\Catalog\Model\ProductFactory $product,
		\Ced\Houzz\Helper\Data $dataHelper,
		\Magento\Framework\Xml\Parser $parser,
		\Ced\Houzz\Model\ResourceModel\HouzzOrders\CollectionFactory $houzzOrder
	) {
		$this->creditmemoLoaderFactory = $creditmemoLoaderFactory;
		$this->dataHelper=$dataHelper;
		$this->parser=$parser;
		$this->orderService = $orderService;
		$this->cartRepositoryInterface = $cartRepositoryInterface;
		$this->cartManagementInterface = $cartManagementInterface;
		$this->objectManager = $objectManager;
		$this->storeManager = $storeManager;
		$this->quote = $quote;
		$this->quoteManagement = $quoteManagement;
		$this->product = $product;
		$this->customerRepository = $customerRepository;
		$this->productRepository = $productRepository;
		$this->customerFactory = $customerFactory;
		$this->houzzOrder = $houzzOrder;
		parent::__construct ( $context );
		$this->scopeConfigManager = $this->objectManager->get ( 'Magento\Framework\App\Config\ScopeConfigInterface' );
		$this->configValueManager = $this->objectManager->get ( 'Magento\Framework\App\Config\ValueInterface' );
		$this->messageManager = $this->objectManager->get ( 'Magento\Framework\Message\ManagerInterface' );
		$this->connection = $this->objectManager->create( 'Ced\Houzz\Model\ResourceModel\HouzzOrders' )->getConnection();
		$this->logger = $this->objectManager->create('Ced\Houzz\Helper\HouzzLogger');
	}

	/**
	 * @return bool
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function fetchLatestHouzzOrders()
	{
		$date = $this->scopeConfigManager->getValue ( 'houzzconfiguration/houzzsetting/orders_fetch_startdate' );
		$storeId = $this->scopeConfigManager->getValue ( 'houzzconfiguration/houzzsetting/houzz_storeid' );
		$websiteId = $this->storeManager->getStore ()->getWebsiteId ();
		$store = $this->storeManager->getStore ($storeId);
		$this->storeManager->setCurrentStore($store);
		$websiteId = $this->storeManager->getStore ()->getWebsiteId ();
		$helper = $this->objectManager->create ( 'Ced\Houzz\Helper\Data' );
		$response = $helper->getjsonRequest( \Ced\Houzz\Helper\Data::GET_NEW_ORDER_URL);
		$response = json_decode($response, true);
		$successPOS = [];
		$errorsPOS = [];
		if (isset($response ['Orders'])) {
			foreach ($response ['Orders'] as $order) {
				$orderObject = $order;
				$email = 'customer@houzz.com';
				$customer = $this->customerFactory->create()->setWebsiteId($websiteId)->loadByEmail($email);

				if (count($order) > 0) {
					$purchaseOrderid = $order['OrderId'];
					$resultdata = $this->houzzOrder->create()
					->addFieldToFilter('purchase_order_id', $purchaseOrderid);
					if (count($resultdata->getData()) <= 0) {
						$ncustomer = $this->_assignCustomer($order, $customer, $store, $email);

						if (is_bool($ncustomer) && !$ncustomer->getId()) {
							continue;
						} else {
							$this->logger->logger("Houzz Order Fetch Debug1" , "Order ID " . $purchaseOrderid, 'Success',' All Good Here');
							$return = $this->generateQuote($store, $ncustomer, $order, $orderObject);
							$this->logger->logger("Houzz Order Fetch Debug End" , "Order ID " . $purchaseOrderid, 'Success',' All Good Here');
							if ($return) {
								$successPOS[] = $purchaseOrderid;
							} else {
								$errorsPOS[] = $purchaseOrderid;
							}

						}
					}
				}
			}

			if (count($successPOS) > 0) {
				$model = $this->objectManager->create('\Magento\AdminNotification\Model\Inbox');
				$date = date("Y-m-d H:i:s");
				$model->setData('severity', 4);
				$model->setData('date_added', $date);
				$model->setData('title', "Incoming Houzz Order");
				$model->setData('description', "Congratulation !! You have received " . count($successPOS) . " new orders from Houzz");
				$model->setData('url', "#");
				$model->setData('is_read', 0);
				$model->setData('is_remove', 0);
				$model->save();
				$this->messageManager->addSuccessMessage('The following Purchase Order Id\'s fetched Successfully - ' . implode(',', $successPOS));
			} else if (count($errorsPOS) > 0) {
				$url = $this->objectManager->create('Magento\Framework\UrlInterface')->getUrl('houzz/order/failedorders');
				$message = 'The following Purchase Order Id\'s failed to fetch - ';
				$message .= implode(",", $errorsPOS);
				$message .= ' . Please check <a href="' . $url . '">Houzz Failed Orders</a>';
				$this->messageManager
				->addError($message);
			}
		} else {
			$this->messageManager->addErrorMessage("Something Wrong in Order Fetch");
		}
		return true;
	}


	/**
	 * Validate string for null , empty and isset
	 * @param string $string
	 * @return boolean
	 */
	public function validateString($string)
	{
		$stringValidation = (isset ( $string ) && ! empty ( $string )) ? true : false;
		return $stringValidation;
	}

	/**
	 * Create Houzz customer on Magento
	 * @param array $order
	 * @param array $customer
	 * @param null $store
	 * @param string $email
	 * @return bool|\Magento\Customer\Api\Data\CustomerInterface
	 */
	public function _assignCustomer($order, $customer, $store=null, $email) {
		if (!( $customer->getId () )) {
			try {
				$cname = $order['CustomerName'];
				$customerName = explode ( ' ', $cname );
				$firstname = $customerName[0];
				unset($customerName[0]);
				$customerName = array_values($customerName) ;
				$lastname = implode(' ', $customerName);
				if (! isset ( $customerName [1] ) || $customerName [1] == '') {
					$customerName [1] = $customerName [0];
				}
				$websiteId = $this->storeManager->getStore()->getWebsiteId();
				$customer = $this->customerFactory->create();
				$customer->setWebsiteId( $websiteId );
				$customer->setEmail ( $email );
				$customer->setFirstname ( 'Houzz' );
				$customer->setLastname ( 'Customer' );
				$customer->setPassword ( "password" );
				$customer->save ();
				return $customer;
			} catch ( \Exception $e ) {

				$this->rejectOrder(NULL , $order , NULL , NULL ,$e->getMessage ());
				return false;
			}
		} else {
			return $customer;
		}
	}

	/**
	 * Generate order in Magento     *
	 * @param integer $store
	 * @param Object $ncustomer
	 * @param array $order
	 * @param Object $orderObject
	 * @return Boolean
	 */
	public function generateQuote($store, $ncustomer, $order, $orderObject)
	{
		try{
			$this->logger->logger("Houzz Order Fetch Debug2" , "Order ID " . $order['OrderId'], 'Success',' All Good Here');
			//$this->connection->beginTransaction();
			$autoReject = false;
			$itemsArray = $order['OrderItems'];
			$baseprice = '';
			$shippingcost = '';
			$tax = '';
			$quote = $this->quote->create();
			$quote->setStore($store);
			$quote->setCurrency();
			$customer = $this->customerRepository->getById($ncustomer->getId());
			$quote->assignCustomer( $customer );
			$shippingcost = 0;
			$subTotal = 0;
			$taxArray = [];
			$taxTotal = 0;
			$orderPrefix = $this->scopeConfigManager->getValue ( 'houzzconfiguration/productinfo_map/houzz_orderid_prefix' );
			foreach( $itemsArray as $item ) {
				if($item['Type'] != 'Product') {
					continue;
				}
				$tax = 0;
				$message = '';
				$sku = $item['SKU'];
				$lineNumber = $item['SKU'];
				$quantity = $item['Quantity'];
				$productObj = $this->objectManager->get('Magento\Catalog\Model\Product');
				$product = $productObj->loadByAttribute('sku',  $this->dataHelper->prepareSku( $sku ));
				if($product) {
					if ($product->getStatus () == '1') {
						$stockRegistry = $this->objectManager->get( 'Magento\CatalogInventory\Api\StockRegistryInterface' );
						/* Get stock item */
						$stock = $stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
						$stockstatus = ($stock->getQty() > 0) ? ($stock->getIsInStock() == '1' ?
							($stock->getQty () >= $item['Quantity'] ?
								true :  ' Qunatity ordered i.e. '.$item['Quantity'].' is not 
								available in your store') : ' Is set to Out of Stock') : '  has 0 Quantity';
						if ((is_bool($stockstatus) && $stockstatus))  {
							$productArray[] = [
								'id' => $product->getEntityId(),
								'qty' => $item['Quantity']
							];
							$price = $item ['Price'];
							$qty = $item['Quantity'];

							if(isset($item['Shipping']) || isset($item['Tax']) ){
								$shippingcost += ($item['Shipping'] * $qty) ;
								$tax = $tax + ($item['Tax'] * $qty);
							}
							$tax = 0;
							$taxTotal += $tax;
							$rowTotal = $price * $qty;
							$subTotal +=$rowTotal;
							$product->setPrice($price)
							->setSpecialPrice($price)
							->setBasePrice($price)
							->setOriginalCustomPrice($price)
							->setRowTotal($rowTotal)
							->setBaseRowTotal($rowTotal);
							$quote->addProduct( $product, intval( $qty ) );
							$this->logger->logger("Houzz Order Fetch Debug3" , "Order ID " . $order['OrderId'], 'Success',' All Good Here');

						} else {
							$autoReject = true;
							$this->rejectOrder($item, $order , $lineNumber , $quantity , $stockstatus);
						}
					}
					else {
						$autoReject = true;
						$this->rejectOrder($item , $order , $lineNumber , $quantity , ' SKU is Disabled in your System.');
					}
				} else {
					$autoReject = true;
					$this->rejectOrder($item , $order , $lineNumber , $quantity , ' Is not Available In your System.');
				}
				$taxArray[$sku] = $tax;
			}
			if(isset($productArray)) {
				$this->logger->logger("Houzz Order Fetch Debug4" , "Order ID " . $order['OrderId'], 'Success',' All Good Here');
				if (count($productArray) > 0 /*&& count($itemsArray) == count($productArray)*/ && !$autoReject) {
					$cname = $order['CustomerName'];
					$customerName = explode ( ' ', $cname );
					$firstname = $customerName[0];
					unset($customerName[0]);
					$customerName = array_values($customerName) ;
					$lastname = implode(' ', $customerName);

					$orderData = [
						'currency_id' => 'USD',
						'email' => 'test@cedcommerce.com',
						'shipping_address' => [
							'firstname' => $firstname,
							'lastname' => ($lastname) ? $lastname : '.',
							'street' => $order['Address']['Address'],
							'city' => $order['Address']['City'],
							'country_id' => 'US',
							'region' => $order['Address']['State'],
							'postcode' => $order['Address']['Zip'],
							'telephone' => $order['Address']['Phone'],
							'fax' => '',
							'save_in_address_book' => 1
						]
					];

					$magentoRegion = $this->objectManager->create('Magento\Directory\Model\Region')
	                    ->loadByCode($order['Address']['State'], 'US');
	                if($magentoRegion && !$magentoRegion->getRegionId()) {
	                    $magentoRegion = $this->objectManager->create('Magento\Directory\Model\Region')
	                        ->loadByName($order['Address']['State'], 'US');
	                }
	                if($magentoRegion && $magentoRegion->getRegionId()) {
	                    $regionId = $magentoRegion->getRegionId();
	                    $orderData['shipping_address']['region_id'] = $regionId;
	                }
	                
					$quote->getBillingAddress()->addData($orderData['shipping_address']);
					$shippingAddress = $quote->getShippingAddress()->addData($orderData['shipping_address']);
					$shippingAddress->setCollectShippingRates(true)->collectShippingRates()->setShippingMethod('shiphouzzcom_shiphouzzcom');
					$quote->setPaymentMethod('payhouzzcom');
					$quote->setInventoryProcessed(false);
					$quote->save();
					$quote->getPayment()->importData([
						'method' => 'payhouzzcom'
					]);
					$this->logger->logger("Houzz Order Fetch Debug5" , "Order ID " . $order['OrderId'], 'Success',' All Good Here');
					$quote->collectTotals()->save();
					$this->logger->logger("Houzz Order Fetch Debug6" , "Order ID " . $order['OrderId'], 'Success',' All Good Here');

					foreach ($quote->getAllItems() as $item) {
						$item->setDiscountAmount(0);
						$item->setBaseDiscountAmount(0);

						$sku = $item->getProduct()->getSku();
						if (isset($taxArray[$sku])) {
							$item->setTaxAmount($taxArray[$sku]);
							$item->setBaseTaxAmount($taxArray[$sku]);
						}
						$item->setOriginalCustomPrice($item->getPrice())
						->setOriginalPrice($item->getPrice())
						->save();
					}
					$quote->collectTotals()->save();
					$this->logger->logger("Houzz Order Fetch Debug7" , "Order ID " . $order['OrderId'], 'Success',' All Good Here');
					$reserveIncrementId = $quote->getReservedOrderId();
					$quote = $this->cartRepositoryInterface->get($quote->getId());
					$orderAfterQuote = $this->cartManagementInterface->submit($quote);
					$this->logger->logger("Houzz Order Fetch Debug8" , "Order ID " . $order['OrderId'], 'Success',' All Good Here');
					$orderId = $orderPrefix .$orderAfterQuote->getIncrementId();
					$orderAfterQuote->setShippingAmount($shippingcost);
					$orderAfterQuote->setTaxAmount($taxTotal);
					$orderAfterQuote->setBaseTaxAmount($taxTotal);
					$orderAfterQuote->setSubTotal($subTotal);
					$orderAfterQuote->setGrandTotal($subTotal + $shippingcost + $taxTotal);
					$orderAfterQuote->setIncrementId($orderId);
					$orderAfterQuote->save();
					$this->logger->logger("Houzz Order Fetch Debug9" , "Order ID " . $order['OrderId'], 'Success',' All Good Here');
					foreach ($orderAfterQuote->getAllItems() as $item) {
						$item->setOriginalPrice($item->getPrice())
						->setBaseOriginalPrice($item->getPrice())
						->save();
					}
					// after save order
					$createdDate = explode(" ", $order['Created']);
					$year = substr($createdDate[0], 0, 4);
					$month = substr($createdDate[0], 6, 2);
					$date = substr($createdDate[0], 10, 2);
					$date = $year . '-' . $month . '-' . $date . ' ' . $createdDate[1];
					$deliver_by = date('Y-m-d H:i:s', strtotime($date));
					$order_place = date('Y-m-d H:i:s', strtotime($date));
					$orderData = [
						'purchase_order_id' => $order['OrderId'],
						'deliver_by' => $deliver_by,
						'order_place_date' => $order_place,
						'magento_order_id' => $orderId,
						'status' => $order['Status'],
						'order_data' => serialize($order),
						'merchant_order_id' => $order['OrderId']];
						$model = $this->objectManager->create('Ced\Houzz\Model\HouzzOrders')->addData($orderData);
						$model->save();
					$this->logger->logger("Houzz Order Fetch Debug10" , "Order ID " . $order['OrderId'], 'Success',' All Good Here');
						$this->generateInvoice($orderAfterQuote);

					$this->logger->logger("Houzz Order Fetch Debug11" , "Order ID " . $order['OrderId'], 'Success',' All Good Here');

						$this->autoOrderacknowledge( $order['OrderId'], $model );
					$this->logger->logger("Houzz Order Fetch Debug12" , "Order ID " . $order['OrderId'], 'Success',' All Good Here');
						$orderMsg = "Houzz Order Number : " . $order['OrderId'];
						$orderAfterQuote = $this->objectManager->create('\Magento\Sales\Model\Order')->load($orderAfterQuote->getId());
		                $orderAfterQuote->addStatusHistoryComment($orderMsg);
		                $orderAfterQuote->save();
		            $this->logger->logger("Houzz Order Fetch Debug13" , "Order ID " . $order['OrderId'], 'Success',' All Good Here');
						//$this->connection->commit();
						return true;
					// after save order end
					}
				} else {
					//$this->connection->commit();
					return false;
				}
			} catch (\Exception $e) {
				$this->logger->logger("Houzz Order Fetch Debug14" , "Order ID " . $order['OrderId'], 'Success',' All Good Here');
				//$this->connection->rollback();
				$this->rejectOrder(NULL , $order , NULL , NULL , $e->getMessage());
				return false;
			} catch (\Error $e) {
				$this->logger->logger("Houzz Order Fetch Debug15" , "Order ID " . $order['OrderId'], 'Success',' All Good Here');
				//$this->connection->rollback();
				$this->rejectOrder(NULL , $order , NULL , NULL , $e->getMessage());
				return false;
			}
		}
	/*
	 * @Auto Order Acknowledgement Process
	 */
	public function autoOrderacknowledge($Incrementid, $ordermodel=null)
	{
		$serialize_data = unserialize( $ordermodel->getOrderData() );
		if (empty ( $serialize_data ) || count ( $serialize_data ) == 0) {
			$helper = $this->objectManager->create ( 'Ced\Houzz\Helper\Data' );
			$result = $helper->getOrder($Incrementid);
			$ord_result = $result ;
			if (empty ( $result ) || count ( $result ) == 0) {
				return 0;
			} else if($result['Status'] == 'Acknowledged') {
				return 0;
			} else
			{
				$wobj = $this->objectManager->create ( 'Ced\Houzz\Model\HouzzOrders' )->load($Incrementid,'purchase_order_id');
				$wobj->setOrderData( serialize ( $ord_result ) );
				$wobj->save();
				$serialize_data = $ord_result;
			}
		}
		$order_id = $ordermodel->getPurchaseOrderId();
		// Api call to Acknowledge Order
		if(isset($order_id)){
			$helper = $this->objectManager->create ( 'Ced\Houzz\Helper\Data' );
			$response = $helper->acknowledgeOrder($order_id);
			if (empty ( $response ) && $response == null || !$response) {
				return 0;
			} else {
				// Setting acknowleged status here

				if (count ( $ordermodel ) > 0) {
					$ordermodel->setStatus( 'Acknowledged' );
					$ordermodel->save();
				}
			}

		}
		return 0;
	}

	/*
	* @Auto Order Rejection Request
	*/
	public function rejectOrder($item = null , $result  , $lineNumber = null , $quantity = null , $message) {
		$orderData = json_encode($result);
		if(is_array($item)) {
			$message = "Product " . $item['SKU'] . $message;
		}
		$houzzOrderError = $this->objectManager->create( 'Ced\Houzz\Model\OrderImportError' ); // for error
		$houzzOrderError->load($result['OrderId'],'purchase_order_id');
		if(empty($houzzOrderError->getData())) {
			$houzzOrderError->setPurchaseOrderId( $result['OrderId'] );
			$houzzOrderError->setReferenceNumber( $result['OrderId'] );
			$houzzOrderError->setReason( date("l jS \of F Y h:i:s A").' - '.$message );
			$houzzOrderError->setOrderData($orderData);
			$houzzOrderError->save();
		} else {
			$houzzOrderError->setReason( date("l jS \of F Y h:i:s A").' - '.$message );
			$houzzOrderError->save();
		}
	}

	/*
	 * @Invoice generation Process
	 */
	public function generateInvoice($order) {
		$invoice = $this->objectManager->create (
			'Magento\Sales\Model\Service\InvoiceService' )->prepareInvoice(
				$order );
			$invoice->register();
			$invoice->save();
			$transactionSave = $this->objectManager->create (
				'Magento\Framework\DB\Transaction' )->addObject (
					$invoice )->addObject ( $invoice->getOrder () );
				$transactionSave->save ();
				$order->addStatusHistoryComment ( __ ( 'Notified customer about invoice #%1.'
					, $invoice->getId () ) )->setIsCustomerNotified ( false )->save ();
				$order->setStatus ( 'processing' )->save ();
			}

	/*
	 * @Shipment generation Process
	 */
	public function generateShipment($order,$cancelleditems) {
		$shipment = $this->_prepareShipment ( $order ,$cancelleditems);
		if ($shipment) {
			$shipment->register ();
			$shipment->getOrder ()->setIsInProcess ( true );
			try {
				$transactionSave = $this->objectManager->create (
					'Magento\Framework\DB\Transaction' )->addObject (
						$shipment )->addObject ( $shipment->getOrder () );
					$transactionSave->save ();
					$order->setStatus ( 'complete' )->save ();
				} catch ( \Exception $e ) {
					$this->messageManager->addErrorMessage ( 'Error in saving shipping:'
						. $e->getMessage() );
				}
			}
		}

	/**
	 * @param $order
	 * @param $cancelleditems
	 * @return bool
	 */
	public function _prepareShipment($order, $cancelleditems)
	{
		foreach($order->getAllItems() as $orderItems)
		{

			$qty_ordered = $orderItems->getQtyOrdered();
		}

		$shipment = $this->objectManager->get( 'Magento\Sales\Model\Order\ShipmentFactory' )->create( $order, isset ( $cancelleditems ) ? $cancelleditems : [ ], [ ] );

		if (! $shipment->getTotalQty ()) {

			return false;
		}

		return $shipment;
	}

	/**
	 * @param $order
	 * @param $cancelleditems
	 *
	 */
	public function generateCreditMemo($order,$cancelleditems)
	{
		foreach($order->getAllItems() as $orderItems)
		{
			$items_id = $orderItems->getId();
			$order_id = $orderItems->getOrderId();
		}
		$creditmemoLoader = $this->creditmemoLoaderFactory->create();
		$creditmemoLoader->setOrderId($order_id);
		foreach ($cancelleditems as $item_id=> $cancel_qty)
		{
			$creditmemo[$item_id] =['qty' => $cancel_qty];
		}

		$items = [
			'items' => $creditmemo,
			'do_offline' => '1',
			'comment_text' => 'Houzz Cancelled Orders',
			'adjustment_positive' => '0',
			'adjustment_negative' => '0'
		];
		$creditmemoLoader->setCreditmemo($items);
		$creditmemo = $creditmemoLoader->load();

		$creditmemoManagement = $this->objectManager->create(
			'Magento\Sales\Api\CreditmemoManagementInterface'
		);

		if($creditmemo){
			$creditmemo->setOfflineRequested(true);
			$creditmemoManagement->refund($creditmemo, true);
		}
	}

	/**
	 * @param $order_to_complete
	 * @param $order_cancel
	 * @param $mixed
	 * @return array
	 */
	public function getOrderFlag($order_to_complete,$order_cancel,$mixed)
	{
		$order_to_complete = isset($order_to_complete)?$order_to_complete:[];
		$order_cancel = isset($order_cancel)?$order_cancel:[];
		$mixed = isset($mixed)?$mixed:[];
		$Order_flag_array=array_merge($order_to_complete,$order_cancel,$mixed);
		$itemcount = sizeof($Order_flag_array);
		$complete = 0;
		$cancel = 0;
		$mix = 0;
		foreach ($Order_flag_array as $key => $value) {
			if($value == 'complete')
			{
				$complete++;
			}elseif($value == 'cancel')
			{
				$cancel++;
			}else
			{
				$mix++;
			}
		}
		return [
			'item_count' => $itemcount,
			'complete'=>$complete,
			'cancel'=> $cancel,
			'mix' => $mix
		];
	}
	/*
	  * @Ship by houzz save process
	  */
	public function putShipOrder($data_ship = NULL, $postData, $order_to_complete = [], $order_cancel = [], $mixed = []) {
		//For Auto ShipStation Only
		if(isset($data_ship['noCallToGenerateShipment'])) {
			$houzzmodel = $this->objectManager->create('Ced\Houzz\Model\HouzzOrders')->load ( $data_ship['shipments'][0]['purchaseOrderId'],'purchase_order_id' );
			$helper = $this->objectManager->create ( 'Ced\Houzz\Helper\Data' );
			$data = $helper->shipOrder(  $data_ship  );
			if(isset($data['ns4:errors'])) {
				$shippstatus=strpos($data['ns4:errors']['ns4:error']['ns4:description'],'SHIPPED status');
				if ( $shippstatus!= false ) {
					$houzzmodel->setStatus('Already Shipped'); //Already shipped
					return 'Alread Shipped Order Being Shipped';
				}
			}
			$responsedata['shippedData'] = $data;
			$responsedata['cancelData'] = '';
			$houzzmodel->setStatus('Complete')->setShipmentData(serialize($responsedata))->save();

			return $data;
		}
		$flag=$this->getOrderFlag($order_to_complete,$order_cancel,$mixed);
		if($flag['item_count'] == $flag['complete'])
		{
			$order_to_complete = true;
			$order_cancel = false;
			$mixed = false;
		}elseif($flag['item_count'] == $flag['cancel'])
		{
			$order_to_complete = false;
			$order_cancel = true;
			$mixed = false;
		}else{
			$order_to_complete = false;
			$order_cancel = false;
			$mixed = true;
		}
		$id = $postData ['key1'];
		$order_id = $postData['orderid'];
		$houzz_order_row = $postData ['order_table_row'];
		$items_data = $postData ['items'];
		$items_data = json_decode ( $items_data );
		/* Do not touch*/
		//$quantity_to_cancel = $items_data[0][2];
		/* Do not touch end */
		$order = $this->objectManager->get ( 'Magento\Sales\Model\Order' )->loadByIncrementId( $id );
		$cancelleditems = [];
		// Api call to complete shipment on houzz
		if($order_to_complete) {
			$helper = $this->objectManager->create ( 'Ced\Houzz\Helper\Data' );
			$data = $helper->shipOrder($data_ship);
			if(!$data) {
				$this->messageManager->addErrorMessage('Error while shipping Order on Houzz.');
				return "Success";
				//return 'Error while shipping Order on Houzz.';
			}
			$responsedata['shippedData'] = $data;
		} else if($order_cancel) {
			$helper = $this->objectManager->create ( 'Ced\Houzz\Helper\Data' );
			$cancelData = $helper->rejectOrders($order_id,$data_ship);

			if(!$cancelData) {
				$this->messageManager->addErrorMessage('Error while cancelation of Order on Houzz.');
				return "Success";
				//return 'Error while cancelation of Order on Houzz.';
			}
			foreach($order->getAllItems() as $orderItem) {
				foreach ($items_data as $val) {
					if ($orderItem->getSku() == $val[0])
						$cancelleditems[$orderItem->getId()] = $val[1];
				}
			}
			$responsedata['cancelData'] = $cancelData;
		}
		if(empty($data) && $order_to_complete) {
			$this->messageManager->addSuccessMessage('Houzz API is down , please try to generate Shipment after sometime.');
			return;
		}
		$houzzmodel = $this->objectManager->get( 'Ced\Houzz\Model\HouzzOrders' )->load( $houzz_order_row );
		$houzz_reference_id = $houzzmodel->getId();
		if (($responsedata) && ($houzz_reference_id)) {
			try {
				$this->saveHouzzShipData( $houzzmodel, $data_ship, $order_to_complete, $order_cancel,$mixed , $order, $cancelleditems,$responsedata);
				return  "Success" ;
			} catch ( \Exception $e ) {
				return $e->getMessage ();
			}
		} else {
			$err =  'Error while generating shipment on houzz.com';
			return $err;
		}
	}
	/**
	 * @Ship by houzz save process
	 */
	public function saveHouzzShipData($houzzmodel, $data_ship, $order_to_complete=null, $order_cancel=null, $mixed=null,$order, $cancelleditems , $responsedata) {
		if(!$order_cancel || $mixed){
			$houzzmodel->setStatus ( 'Complete' );
		}else{
			$houzzmodel->setStatus ( 'Cancelled' );
		}
		$houzzmodel->setShipmentData ( serialize ( $responsedata ) );
		$houzzmodel->save ();

		if(!$order_cancel)
			if (! $order->canShip()) {
				$this->messageManager->addErrorMessage(__("You can\'t create an shipment.")
			);
			}else{
				$this->generateShipment( $order , $cancelleditems);
			}

			if(!$order_to_complete || $order_cancel)
				if (!$order->canCreditmemo()) {
					$this->messageManager->addErrorMessage(__("We can\'t create credit memo for the order."));
					return false;
				}else{
					$this->generateCreditMemo($order,$cancelleditems);
				}
				$this->messageManager->addSuccessMessage( 'Your Houzz Order ' . $order->getId() . ' has been Completed.' );

			}

			public function parserArray($array){
				$arr = [];
				foreach ($array as $key => $value){
					if(in_array($key,$arr))
						continue;
					$count = count($array);
					$sku = $value['item']['sku'];
					$quantity = 1;
					$lineNumber = $value['lineNumber'];
					for ( $i = $key+1 ; $i < $count;$i++){
						if(isset($array[$i]) && ($array[$i]['item']['sku'] == $sku)){
							$quantity++;
							$lineNumber = $lineNumber.','.$array[$i]['lineNumber'];
							unset($array[$i]);
							array_push($arr,$i);
							array_values($array);
						}
					}
					$array[$key]['lineNumber'] = $lineNumber;
					$array[$key]['orderLineQuantity']['amount'] = $quantity;
				}
				return $array;
			}
		}
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
 * @category  Mageplaza
 * @package   Mageplaza_NameYourPrice
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\NameYourPrice\Test\Unit\Helper;

use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Api\TaxCalculationInterface;
use Mageplaza\NameYourPrice\Helper\Data;
use Mageplaza\NameYourPrice\Model\ResourceModel\Requests;
use Mageplaza\NameYourPrice\Model\ResourceModel\RequestsFactory;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class BargainTest
 * @package Mageplaza\NameYourPrice\Test\Unit\Block
 */
class BargainTest extends TestCase
{
    /**
     * @var Context|PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var ObjectManagerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var StoreManagerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var ProductFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_productFactory;

    /**
     * @var TransportBuilder|PHPUnit_Framework_MockObject_MockObject
     */
    protected $transportBuilder;

    /**
     * @var TaxCalculationInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxCalculation;

    /**
     * @var OrderRepositoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_orderRepository;

    /**
     * @var RequestsFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestsFactory;

    /**
     * @var PriceHelper|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_priceHelper;

    /**
     * @var Session|PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var PriceCurrencyInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_priceCurrency;

    /**
     * @var Data|PHPUnit_Framework_MockObject_MockObject
     */
    private $object;

    protected function setUp()
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()->getMock();
        $this->objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMock();
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMock();
        $this->_productFactory = $this->getMockBuilder(ProductFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->transportBuilder = $this->getMockBuilder(TransportBuilder::class)
            ->disableOriginalConstructor()->getMock();
        $this->taxCalculation = $this->getMockBuilder(TaxCalculationInterface::class)
            ->getMock();
        $this->_orderRepository = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->getMock();
        $this->_requestsFactory = $this->getMockBuilder(RequestsFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->_priceHelper = $this->getMockBuilder(PriceHelper::class)
            ->disableOriginalConstructor()->getMock();
        $this->customerSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()->getMock();
        $this->_priceCurrency = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->disableOriginalConstructor()->getMock();

        $this->object = new Data(
            $this->context,
            $this->objectManager,
            $this->storeManager,
            $this->_productFactory,
            $this->transportBuilder,
            $this->taxCalculation,
            $this->_orderRepository,
            $this->_requestsFactory,
            $this->_priceHelper,
            $this->customerSession,
            $this->_priceCurrency
        );
    }

    public function testAdminInstance()
    {
        $this->assertInstanceOf(Data::class, $this->object);
    }

    public function testGetIncrementOrderIds()
    {
        $request = $this->getMockBuilder(\Mageplaza\NameYourPrice\Model\Requests::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderIncrementIds = [1, 2, 3];
        $orderIds = [1];
        $orderId = 1;

        $requests = $this->getMockBuilder(Requests::class)
            ->setMethods(['getMatchingOrderIds'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_requestsFactory->method('create')->willReturn($requests);
        $requests->method('getMatchingOrderIds')->with($request)->willReturn($orderIds);

        $order = $this->getMockBuilder(OrderInterface::class)
            ->getMock();
        $this->_orderRepository->method('get')->with($orderId)->willReturn($order);
        $order->method('getIncrementId')->willReturn($orderIncrementIds);

        $this->assertEquals($orderIncrementIds, $this->object->getIncrementOrderIds($request));
    }
}

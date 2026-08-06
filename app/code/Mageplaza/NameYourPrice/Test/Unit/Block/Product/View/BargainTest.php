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

namespace Mageplaza\NameYourPrice\Test\Unit\Block;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Customer\Helper\View;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Mageplaza\NameYourPrice\Block\Product\View\Bargain;
use Mageplaza\NameYourPrice\Helper\Data as HelperData;
use Mageplaza\NameYourPrice\Model\Condition;
use Mageplaza\NameYourPrice\Model\ConditionFactory;
use Mageplaza\NameYourPrice\Model\RequestsFactory;
use Mageplaza\NameYourPrice\Model\ResourceModel\Requests\Collection;
use Mageplaza\NameYourPrice\Model\ResourceModel\Requests\CollectionFactory;
use Mageplaza\NameYourPrice\Model\ResourceModel\RequestsFactory as ResourceFactory;
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
     * @var HelperData|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperData;

    /**
     * @var HttpContext|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_context;

    /**
     * @var SessionFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sessionFactory;

    /**
     * @var Registry|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_registry;

    /**
     * @var ConditionFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_conditionFactory;

    /**
     * @var RequestsFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestFactory;

    /**
     * @var ResourceFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceFactory;

    /**
     * @var CollectionFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_collectionFactory;

    /**
     * @var ManagerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_messageManager;

    /**
     * @var View|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_customerView;

    /**
     * @var Bargain|PHPUnit_Framework_MockObject_MockObject
     */
    private $object;

    protected function setUp()
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()->getMock();
        $this->_helperData = $this->getMockBuilder(HelperData::class)
            ->disableOriginalConstructor()->getMock();
        $this->_context = $this->getMockBuilder(HttpContext::class)
            ->disableOriginalConstructor()->getMock();
        $this->_sessionFactory = $this->getMockBuilder(SessionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->_conditionFactory = $this->getMockBuilder(ConditionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->_requestFactory = $this->getMockBuilder(RequestsFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->_resourceFactory = $this->getMockBuilder(ResourceFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->_collectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->_messageManager = $this->getMockBuilder(ManagerInterface::class)->getMock();
        $this->_customerView = $this->getMockBuilder(View::class)
            ->disableOriginalConstructor()->getMock();
        $this->_registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()->getMock();

        $this->context->method('getRegistry')->willReturn($this->_registry);

        $this->object = new Bargain(
            $this->context,
            $this->_helperData,
            $this->_context,
            $this->_sessionFactory,
            $this->_conditionFactory,
            $this->_requestFactory,
            $this->_resourceFactory,
            $this->_collectionFactory,
            $this->_messageManager,
            $this->_customerView,
            []
        );
    }

    public function testAdminInstance()
    {
        $this->assertInstanceOf(Bargain::class, $this->object);
    }

    public function testCheckProductCondition()
    {
        $productId = 1;
        $product = $this->getMockBuilder(AbstractProduct::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_registry->method('registry')->with('product')->willReturn($product);
        $product->method('getId')->willReturn($productId);
        $conditionConfig = '';
        $this->_helperData->method('getConditionConfig')->willReturn($conditionConfig);

        $proIds = [1, 2, 3];
        $condition = $this->getMockBuilder(Condition::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_conditionFactory->method('create')->willReturn($condition);
        $condition->method('getMatchingProductIds')->with('')->willReturn($proIds);

        $this->assertEquals(true, $this->object->checkProductCondition());
    }

    public function testCheckCustomerGroups()
    {
        $config = '0,1,2,3';
        $customerGroup = '0';

        $this->_helperData->method('getCustomerGroupsConfig')->willReturn($config);

        $session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_sessionFactory->method('create')->willReturn($session);
        $session->method('isLoggedIn')->willReturn(true);

        $customerModel = $this->getMockBuilder(Customer::class)
            ->setMethods(['getCustomer', 'getGroupId'])
            ->disableOriginalConstructor()
            ->getMock();
        $session->method('getCustomer')->willReturn($customerModel);
        $customerModel->method('getGroupId')->willReturn($customerGroup);

        $this->assertEquals(true, $this->object->checkCustomerGroups());
    }

    public function testGetBargainCollection()
    {
        $email = 'example@gmail.com';
        $auth = 'customer_logged_in';
        $status = ['approved'];
        $productId = 1;
        $requestId = 1;

        $this->_context->method('getValue')->willReturn($auth);
        $session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_sessionFactory->method('create')->willReturn($session);
        $customerModel = $this->getMockBuilder(Customer::class)
            ->setMethods(['getCustomerData', 'getEmail'])
            ->disableOriginalConstructor()
            ->getMock();
        $session->method('getCustomerData')->willReturn($customerModel);
        $customerModel->method('getEmail')->willReturn($email);

        $collection = $this->getMockBuilder(Collection::class)
            ->setMethods(['addFieldToFilter'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_collectionFactory->method('create')->willReturn($collection);
        $collection->expects($this->at(0))->method('addFieldToFilter')
            ->with('status', $status)->willReturnSelf();
        $collection->expects($this->at(1))->method('addFieldToFilter')
            ->with('product_id', $productId)->willReturnSelf();
        $collection->expects($this->at(2))->method('addFieldToFilter')
            ->with('request_id', $requestId)->willReturnSelf();
        $collection->expects($this->at(3))->method('addFieldToFilter')
            ->with('customer_email', $email)->willReturnSelf();

        $this->assertEquals($collection, $this->object->getBargainCollection($status, $productId, $requestId));
    }
}

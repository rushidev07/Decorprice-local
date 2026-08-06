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

namespace Mageplaza\NameYourPrice\Test\Unit\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Math\Calculator;
use Magento\Quote\Model\Quote\Item;
use Mageplaza\NameYourPrice\Block\Product\View\Bargain;
use Mageplaza\NameYourPrice\Helper\Data as HelperData;
use Mageplaza\NameYourPrice\Model\ResourceModel\Requests;
use Mageplaza\NameYourPrice\Observer\SetPriceBargain;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class SetPriceBargainTest
 * @package Mageplaza\NameYourPrice\Test\Unit\Observer
 */
class SetPriceBargainTest extends TestCase
{
    /**
     * @var Requests|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestResource;

    /**
     * @var HelperData|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperData;

    /**
     * @var RequestInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    /**
     * @var Bargain|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_bargain;

    /**
     * @var Calculator|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_calculator;

    /**
     * @var SetPriceBargain|PHPUnit_Framework_MockObject_MockObject
     */
    private $object;

    protected function setUp()
    {
        $this->_requestResource = $this->getMockBuilder(Requests::class)->disableOriginalConstructor()->getMock();
        $this->_helperData = $this->getMockBuilder(HelperData::class)->disableOriginalConstructor()->getMock();
        $this->_request = $this->getMockBuilder(RequestInterface::class)->getMock();
        $this->_bargain = $this->getMockBuilder(Bargain::class)->disableOriginalConstructor()->getMock();
        $this->_calculator = $this->getMockBuilder(Calculator::class)->disableOriginalConstructor()->getMock();

        $this->object = new SetPriceBargain(
            $this->_requestResource,
            $this->_helperData,
            $this->_request,
            $this->_bargain,
            $this->_calculator
        );
    }

    public function testAdminInstance()
    {
        $this->assertInstanceOf(SetPriceBargain::class, $this->object);
    }

    public function testExecute()
    {
        $observer = $this->getMockBuilder(Observer::class)
            ->setMethods(['getEvent'])
            ->disableOriginalConstructor()
            ->getMock();
        $event = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $item = $this->getMockBuilder(Item::class)
            ->setMethods(
                [
                    'getHasChildren',
                    'getProduct',
                    'setAdditionalData',
                    'setOriginalCustomPrice'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $productId = 1;
        $requestId = 1;
        $verifyBundle = null;
        $status = ['approved'];
        $collectionSize = 1;
        $bargainPrice = 100;

        $observer->method('getEvent')->willReturn($event);
        $event->method('getData')->with('quote_item')->willReturn($item);
        $this->_helperData->method('isEnabled')->willReturn(true);
        $item->method('getHasChildren')->willReturn(false);

        $product = $this->getMockBuilder(DataObject::class)
            ->setMethods(['setIsSuperMode', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $item->method('getProduct')->willReturn($product);
        $product->method('getId')->willReturn($productId);

        $this->_request->expects($this->at(0))->method('getParam')
            ->with('mpb-request-id')->willReturn($requestId);
        $this->_request->expects($this->at(1))->method('getParam')
            ->with('mpb-verify-options')->willReturn($verifyBundle);
        $collection = $this->getMockBuilder(Requests\Collection::class)
            ->setMethods(['addFieldToFilter', 'getSize', 'getFirstItem'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_bargain->method('getBargainCollection')->with($status, $productId, $requestId)->willReturn($collection);
        $collection->method('getSize')->willReturn($collectionSize);

        $collection->method('getFirstItem')->willReturn($bargainPrice);
        $this->_helperData->method('isApplyDiscount')->willReturn(false);
        $item->expects($this->at(2))->method('setAdditionalData')->with($requestId)->willReturnSelf();
        $product->method('setIsSuperMode')->with(true);

        $this->object->execute($observer);
    }
}

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

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Mageplaza\NameYourPrice\Block\Product\View\Bargain;
use Mageplaza\NameYourPrice\Helper\Data as HelperData;
use Mageplaza\NameYourPrice\Model\ResourceModel\Requests\Collection;
use Mageplaza\NameYourPrice\Model\ResourceModel\Requests\CollectionFactory;
use Mageplaza\NameYourPrice\Observer\ChangeTax;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class ChangeTaxTest
 * @package Mageplaza\NameYourPrice\Test\Unit\Observer
 */
class ChangeTaxTest extends TestCase
{
    /**
     * @var HelperData|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperData;

    /**
     * @var CollectionFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_collectionFactory;

    /**
     * @var Bargain|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_bargain;

    /**
     * @var ChangeTax|PHPUnit_Framework_MockObject_MockObject
     */
    private $object;

    protected function setUp()
    {
        $this->_helperData = $this->getMockBuilder(HelperData::class)
            ->disableOriginalConstructor()->getMock();
        $this->_collectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->_bargain = $this->getMockBuilder(Bargain::class)
            ->disableOriginalConstructor()->getMock();

        $this->object = new ChangeTax(
            $this->_helperData,
            $this->_collectionFactory,
            $this->_bargain
        );
    }

    public function testAdminInstance()
    {
        $this->assertInstanceOf(ChangeTax::class, $this->object);
    }

    public function testExecute()
    {
        $this->_helperData->method('isApplyTax')->willReturn(true);
        $oldTax = 20;
        $oldBaseTax = 20;
        $tax = 10;
        $baseTax = 10;
        $productId = 1;
        $originalCustomPrice = 100;

        $observer = $this->getMockBuilder(Observer::class)
            ->setMethods(['getEvent', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $event = $this->getMockBuilder(DataObject::class)
            ->setMethods([
                'getQuote',
                'getAppliedTaxes',
                'getTotalAmount',
                'setTotalAmount',
                'getBaseTotalAmount',
                'setBaseTotalAmount'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $item = $this->getMockBuilder(Item::class)
            ->setMethods([
                'getProduct',
                'getOriginalCustomPrice',
                'getTaxAmount',
                'getBaseTaxAmount'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $observer->method('getData')->with('total')->willReturn($event);
        $observer->method('getEvent')->willReturn($event);
        $event->method('getQuote')->willReturn($quote);

        $quote->method('getAllItems')->willReturnSelf();

        $product = $this->getMockBuilder(DataObject::class)
            ->setMethods(['setIsSuperMode', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $item->method('getProduct')->willReturn($product);
        $product->method('getId')->willReturn($productId);
        $item->method('getOriginalCustomPrice')->willReturn($originalCustomPrice);
        $collection = $this->getMockBuilder(Collection::class)
            ->setMethods(['addFieldToFilter', 'getSize', 'getFirstItem'])
            ->disableOriginalConstructor()
            ->getMock();
        $collection->method('addFieldToFilter')
            ->with('product_id', $productId)->willReturnSelf();
        $collection->method('addFieldToFilter')
            ->with('bargain_price', $originalCustomPrice)->willReturnSelf();

        $collection->method('getSize')->willReturn(1);

        $item->method('getTaxAmount')->willReturn($tax);
        $item->method('getBaseTaxAmount')->willReturn($baseTax);

        $event->method('getAppliedTaxes')->willReturn(1);
        $event->method('getTotalAmount')->with('tax')->willReturn($oldTax);
        $event->expects($this->once())->method('setTotalAmount')->with('tax', $oldTax);
        $event->method('getBaseTotalAmount')->with('tax')->willReturn($oldBaseTax);
        $event->expects($this->once())->method('setBaseTotalAmount')->with('tax', $oldTax);

        $this->object->execute($observer);
    }
}

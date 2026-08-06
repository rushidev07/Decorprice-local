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

namespace Mageplaza\NameYourPrice\Test\Unit\Plugin;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Mageplaza\NameYourPrice\Block\Product\View\Bargain;
use Mageplaza\NameYourPrice\Helper\Data as HelperData;
use Mageplaza\NameYourPrice\Model\ResourceModel\Requests\Collection;
use Mageplaza\NameYourPrice\Model\ResourceModel\Requests\CollectionFactory;
use Mageplaza\NameYourPrice\Plugin\Model\SetPriceItem;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class ChangeTaxTest
 * @package Mageplaza\NameYourPrice\Test\Unit\Observer
 */
class SetPriceItemTest extends TestCase
{
    /**
     * @var CollectionFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_collectionFactory;

    /**
     * @var HelperData|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperData;

    /**
     * @var Bargain|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_bargain;

    /**
     * @var SetPriceItem|PHPUnit_Framework_MockObject_MockObject
     */
    private $object;

    protected function setUp()
    {
        $this->_collectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->_helperData = $this->getMockBuilder(HelperData::class)
            ->disableOriginalConstructor()->getMock();
        $this->_bargain = $this->getMockBuilder(Bargain::class)
            ->disableOriginalConstructor()->getMock();

        $this->object = new SetPriceItem(
            $this->_helperData,
            $this->_collectionFactory,
            $this->_bargain
        );
    }

    public function testAdminInstance()
    {
        $this->assertInstanceOf(SetPriceItem::class, $this->object);
    }

    /**
     * @param $className
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getCleanMock($className)
    {
        return $this->createMock($className);
    }

    public function testBeforeGetCalculationPriceOriginal()
    {
        $requestId = 1;
        $this->_helperData->method('isEnabled')->willReturn(true);

        $subject = $this->getMockBuilder(AbstractItem::class)
            ->disableOriginalConstructor()->getMock();

        $quote = $this->getMockBuilder(Quote::class)
            ->setMethods(['getAllItems'])
            ->disableOriginalConstructor()
            ->getMock();
        $item = $this->getMockBuilder(Item::class)
            ->setMethods(['getAdditionalData', 'setOriginalCustomPrice'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('getQuote')->willReturn($quote);
        $quote->method('getAllItems')->willReturn([$item]);

        $item->method('getAdditionalData')->willReturn($requestId);
        $collection = $this->getMockBuilder(Collection::class)
            ->setMethods(['addFieldToFilter', 'getSize'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_collectionFactory->method('create')->willReturn($collection);
        $collection->expects($this->at(0))->method('addFieldToFilter')
            ->with('request_id', $requestId)->willReturnSelf();
        $collection->expects($this->at(1))->method('addFieldToFilter')
            ->with('status', 'approved')->willReturnSelf();

        $collection->method('getSize')->willReturn(0);
        $item->method('setOriginalCustomPrice')->with(null);

        $this->object->beforeGetCalculationPriceOriginal($subject);
    }
}

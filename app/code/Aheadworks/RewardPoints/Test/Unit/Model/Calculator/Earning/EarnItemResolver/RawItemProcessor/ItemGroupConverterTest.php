<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor;

use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor\ItemGroupConverter;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemInterfaceFactory;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\DataObject\Copy as ObjectCopyService;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Test for \Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor\ItemGroupConverter
 */
class ItemGroupConverterTest extends TestCase
{
    /**
     * @var ItemGroupConverter
     */
    private $converter;

    /**
     * @var ItemInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemFactoryMock;

    /**
     * @var ObjectCopyService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectCopyServiceMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->itemFactoryMock = $this->createMock(ItemInterfaceFactory::class);
        $this->objectCopyServiceMock = $this->createMock(ObjectCopyService::class);

        $this->converter = $objectManager->getObject(
            ItemGroupConverter::class,
            [
                'itemFactory' => $this->itemFactoryMock,
                'objectCopyService' => $this->objectCopyServiceMock
            ]
        );
    }

    /**
     * Test convert method
     */
    public function testConvert()
    {
        $fieldset = 'quote_earn_item';
        $aspect = 'to_eran_item';
        $this->setProperty('fieldset', $fieldset);
        $this->setProperty('aspect', $aspect);

        $simpleMock = $this->getQuoteItemMock(10);
        $configurableParentMock = $this->getQuoteItemMock(11);
        $configurableChildMock = $this->getQuoteItemMock(12);
        $quoteItemGroups = [
            10 => [10 => $simpleMock],
            11 => [11 =>$configurableParentMock, 12 => $configurableChildMock]
        ];

        $simpleItemMock = $this->getItemMock(null);
        $configurableParentItemMock =  $this->getItemMock(null);
        $configurableChildItemMock = $this->getItemMock($configurableParentItemMock);
        $itemGroups = [
            [$simpleItemMock],
            [$configurableParentItemMock, $configurableChildItemMock]
        ];

        $this->itemFactoryMock->expects($this->exactly(3))
            ->method('create')
            ->willReturnOnConsecutiveCalls($simpleItemMock, $configurableParentItemMock, $configurableChildItemMock);

        $this->objectCopyServiceMock->expects($this->exactly(3))
            ->method('copyFieldsetToTarget')
            ->withConsecutive(
                [$fieldset, $aspect, $simpleMock, $simpleItemMock],
                [$fieldset, $aspect, $configurableParentMock, $configurableParentItemMock],
                [$fieldset, $aspect, $configurableChildMock, $configurableChildItemMock]
            )
            ->willReturnSelf();

        $this->assertEquals($itemGroups, $this->converter->convert($quoteItemGroups));
    }

    /**
     * Test convert method if item list is empty
     */
    public function testConvertEmptyItems()
    {
        $quoteItemGroups = [];
        $itemGroups = [];

        $this->itemFactoryMock->expects($this->never())
            ->method('create');

        $this->objectCopyServiceMock->expects($this->never())
            ->method('copyFieldsetToTarget');

        $this->assertEquals($itemGroups, $this->converter->convert($quoteItemGroups));
    }

    /**
     * Get quote item mock
     *
     * @param int $itemId
     * @return QuoteItem|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getQuoteItemMock($itemId)
    {
        $simpleMock = $this->createMock(QuoteItem::class);
        $simpleMock->expects($this->any())
            ->method('getItemId')
            ->willReturn($itemId);
        return $simpleMock;
    }

    /**
     * Get item mock
     *
     * @param ItemInterface|\PHPUnit_Framework_MockObject_MockObject|null $configurableParentItemMock
     * @return ItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getItemMock($configurableParentItemMock)
    {
        $configurableChildItemMock = $this->createMock(ItemInterface::class);
        if ($configurableParentItemMock != null) {
            $configurableChildItemMock->expects($this->once())
                ->method('setParentItem')
                ->with($configurableParentItemMock)
                ->willReturnSelf();
        }

        return $configurableChildItemMock;
    }

    /**
     * Set property
     *
     * @param string $propertyName
     * @param mixed $value
     * @return mixed
     * @throws \ReflectionException
     */
    private function setProperty($propertyName, $value)
    {
        $class = new \ReflectionClass($this->converter);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($this->converter, $value);

        return $this;
    }
}

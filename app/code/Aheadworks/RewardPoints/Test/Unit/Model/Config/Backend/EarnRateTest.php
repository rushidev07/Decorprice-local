<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Config\Backend;

use Aheadworks\RewardPoints\Model\Config\Backend\EarnRate;
use Aheadworks\RewardPoints\Model\ResourceModel\EarnRate as EarnRateResource;
use Aheadworks\RewardPoints\Model\ResourceModel\EarnRate\CollectionFactory;
use Aheadworks\RewardPoints\Model\ResourceModel\EarnRate\Collection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Serialize\Serializer\Serialize;

/**
 * Class Aheadworks\RewardPoints\Test\Unit\Model\Config\Backend\EarnRateTest
 */
class EarnRateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EarnRateResource
     */
    private $earnRateResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CollectionFactory
     */
    private $earnRateCollectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Collection
     */
    private $earnRateCollectionMock;

    /**
     * @var EarnRate
     */
    private $object;

    /**
     * @var Serialize
     */
    private $serializerMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->earnRateResourceMock = $this->getMockBuilder(EarnRateResource::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['saveConfigValue']
            )
            ->getMock();

        $this->earnRateCollectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['create']
            )
            ->getMock();

        $this->serializerMock = $this->getMockBuilder(Serialize::class)
            ->setMethods(
                ['serialize']
            )
            ->getMock();

        $this->earnRateCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['toConfigDataArray']
            )
            ->getMock();

        $data = [
            'earnRateResource' => $this->earnRateResourceMock,
            'earnRateCollectionFactory' => $this->earnRateCollectionFactoryMock,
            'serializer' => $this->serializerMock
        ];

        $this->object = $objectManager->getObject(EarnRate::class, $data);
    }

    /**
     * Test beforeSave method
     *
     * @dataProvider valueProvider
     * @param mixed $internalValue
     * @param string $expectedValue
     */
    public function testBeforeSave($internalValue, $expectedValue)
    {
        $this->serializerMock
            ->method('serialize')
            ->with($internalValue)
            ->willReturn($expectedValue);

        $this->object->setValue($internalValue);
        $this->object->beforeSave();

        $this->assertEquals($internalValue, $this->object->getEarnRateValue());
        $this->assertEquals($expectedValue, $this->object->getValue());
    }

    /**
     * Test afterSave method
     *
     * @dataProvider valueProvider
     */
    public function testAfterSave($expectedValue)
    {
        $this->object->setEarnRateValue($expectedValue);

        $this->earnRateResourceMock->expects($this->once())
            ->method('saveConfigValue')
            ->with($expectedValue)
            ->willReturnSelf();

        $this->object->afterSave();
    }

    /**
     * Test afterLoad method
     */
    public function testAfterLoad()
    {
        $expectedResult = [[1, 2, 3, 4], [5, 6, 7, 8]];

        $this->earnRateCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->earnRateCollectionMock);

        $this->earnRateCollectionMock->expects($this->once())
            ->method('toConfigDataArray')
            ->willReturn($expectedResult);

        $this->object->afterLoad();

        $this->assertEquals($expectedResult, $this->object->getValue());
    }

    /**
     * Test data provider
     *
     * @return array
     */
    public function valueProvider()
    {
        return [
            ['setTestConfigValue', 's:18:"setTestConfigValue";'],
            [15, 'i:15;'],
            [null, 'N;'],
            [new \stdClass(11), 'O:8:"stdClass":0:{}'],
            [[1,2,3,4], 'a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}']
        ];
    }
}

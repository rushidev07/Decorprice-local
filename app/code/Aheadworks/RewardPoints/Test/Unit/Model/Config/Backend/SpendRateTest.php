<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Config\Backend;

use Aheadworks\RewardPoints\Model\Config\Backend\SpendRate;
use Aheadworks\RewardPoints\Model\ResourceModel\SpendRate as SpendRateResource;
use Aheadworks\RewardPoints\Model\ResourceModel\SpendRate\CollectionFactory;
use Aheadworks\RewardPoints\Model\ResourceModel\SpendRate\Collection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Serialize\Serializer\Serialize;

/**
 * Class Aheadworks\RewardPoints\Test\Unit\Model\Config\Backend\SpendRateTest
 */
class SpendRateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SpendRateResource
     */
    private $spendRateResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CollectionFactory
     */
    private $spendRateCollectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Collection
     */
    private $spendRateCollectionMock;

    /**
     * @var Serialize
     */
    private $serializerMock;

    /**
     * @var SpendRate
     */
    private $object;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->spendRateResourceMock = $this->getMockBuilder(SpendRateResource::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['saveConfigValue']
            )
            ->getMock();

        $this->spendRateCollectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
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

        $this->spendRateCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['toConfigDataArray']
            )
            ->getMock();

        $data = [
            'spendRateResource' => $this->spendRateResourceMock,
            'spendRateCollectionFactory' => $this->spendRateCollectionFactoryMock,
            'serializer' => $this->serializerMock
        ];

        $this->object = $objectManager->getObject(SpendRate::class, $data);
    }

    /**
     * Test beforeSave method
     *
     * @dataProvider valueProvider
     * @param mixed $internalValue
     * @param string $expectedValue
     *
     */
    public function testBeforeSave($internalValue, $expectedValue)
    {
        $this->serializerMock
            ->method('serialize')
            ->with($internalValue)
            ->willReturn($expectedValue);

        $this->object->setValue($internalValue);
        $this->object->beforeSave();

        $this->assertEquals($internalValue, $this->object->getSpendRateValue());
        $this->assertEquals($expectedValue, $this->object->getValue());
    }

    /**
     * Test afterSave method
     *
     * @dataProvider valueProvider
     */
    public function testAfterSave($expectedValue)
    {
        $this->object->setSpendRateValue($expectedValue);

        $this->spendRateResourceMock->expects($this->once())
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

        $this->spendRateCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->spendRateCollectionMock);

        $this->spendRateCollectionMock->expects($this->once())
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

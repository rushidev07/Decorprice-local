<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\EarnRule\Action;

use Aheadworks\RewardPoints\Model\EarnRule\Action\TypePool;
use Aheadworks\RewardPoints\Model\EarnRule\Action\TypeInterface;
use Magento\Framework\Exception\ConfigurationMismatchException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\RewardPoints\Model\EarnRule\Action\TypePool
 */
class TypePoolTest extends TestCase
{
    /**
     * @var TypePool
     */
    private $typePool;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->typePool = $objectManager->getObject(TypePool::class, []);
    }

    /**
     * Test getTypes method
     *
     * @param TypeInterface[]|\PHPUnit_Framework_MockObject_MockObject[] $types
     * @dataProvider getTypesDataProvider
     * @throws \ReflectionException
     */
    public function testGetTypes($types)
    {
        $this->setProperty('types', $types);

        $this->assertEquals($types, $this->typePool->getTypes());
    }

    /**
     * @return array
     */
    public function getTypesDataProvider()
    {
        return [
            [
                'types' => []
            ],
            [
                'types' => [$this->createMock(TypeInterface::class)]
            ],
            [
                'types' => [
                    $this->createMock(TypeInterface::class),
                    $this->createMock(TypeInterface::class)
                ]
            ]
        ];
    }

    /**
     * Test getTypesCount method
     *
     * @param TypeInterface[]|\PHPUnit_Framework_MockObject_MockObject[] $types
     * @param int $result
     * @dataProvider getTypesCount
     * @throws \ReflectionException
     */
    public function testGetTypesCount($types, $result)
    {
        $this->setProperty('types', $types);

        $this->assertEquals($result, $this->typePool->getTypesCount());
    }

    /**
     * @return array
     */
    public function getTypesCount()
    {
        return [
            [
                'types' => [],
                'result' => 0
            ],
            [
                'types' => [$this->createMock(TypeInterface::class)],
                'result' => 1
            ],
            [
                'types' => [
                    $this->createMock(TypeInterface::class),
                    $this->createMock(TypeInterface::class)
                ],
                'result' => 2
            ]
        ];
    }

    /**
     * Test getTypeByCode method
     */
    public function testGetTypeByCode()
    {
        $typeMock = $this->createMock(TypeInterface::class);
        $types = [
            'type_one' => $typeMock,
        ];

        $this->setProperty('types', $types);

        $this->assertSame($typeMock, $this->typePool->getTypeByCode('type_one'));
    }

    /**
     * Test getTypeByCode method if no type with the code specified
     */
    public function testGetTypeByCodeNoCode()
    {
        $typeMock = $this->createMock(TypeInterface::class);
        $types = [
            'type_one' => $typeMock,
        ];

        $this->setProperty('types', $types);

        $this->expectException(ConfigurationMismatchException::class);

        $this->typePool->getTypeByCode('type_two');
    }

    /**
     * Test isTypeExists method
     */
    public function testIsTypeExists()
    {
        $typeMock = $this->createMock(TypeInterface::class);
        $types = [
            'type_one' => $typeMock,
        ];

        $this->setProperty('types', $types);

        $this->assertTrue($this->typePool->isTypeExists('type_one'));
    }

    /**
     * Test isTypeExists method if no type found
     */
    public function testIsTypeExistsNoType()
    {
        $typeMock = $this->createMock(TypeInterface::class);
        $types = [
            'type_one' => $typeMock,
        ];

        $this->setProperty('types', $types);

        $this->assertFalse($this->typePool->isTypeExists('type_two'));
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
        $class = new \ReflectionClass($this->typePool);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($this->typePool, $value);

        return $this;
    }
}

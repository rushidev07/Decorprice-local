<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Calculator\Earning\EarnItemResolver\ProductProcessor;

use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor\TypeProcessorPool;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor\TypeProcessorInterface;
use Magento\Framework\Exception\ConfigurationMismatchException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\DataObject;

/**
 * Test for \Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemProcessorPool
 */
class TypeProcessorPoolTest extends TestCase
{
    /**
     * @var TypeProcessorPool
     */
    private $pool;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->pool = $objectManager->getObject(TypeProcessorPool::class, []);
    }

    /**
     * Test getProcessors method
     *
     * @param TypeProcessorInterface[]|\PHPUnit_Framework_MockObject_MockObject[] processors
     * @dataProvider getProcessorsDataProvider
     * @throws \ReflectionException
     */
    public function testGetTypes($processors)
    {
        $this->setProperty('processors', $processors);

        $this->assertEquals($processors, $this->pool->getProcessors());
    }

    /**
     * @return array
     */
    public function getProcessorsDataProvider()
    {
        return [
            [
                'processors' => []
            ],
            [
                'processors' => [$this->createMock(TypeProcessorInterface::class)]
            ],
            [
                'processors' => [
                    $this->createMock(TypeProcessorInterface::class),
                    $this->createMock(TypeProcessorInterface::class)
                ]
            ]
        ];
    }

    /**
     * Test getProcessorByCode method
     *
     * @param TypeProcessorInterface[] $processors
     * @param string $code
     * @param TypeProcessorInterface|\Exception $result
     * @throws \ReflectionException
     * @dataProvider getProcessorByCodeDataProvider
     */
    public function testGetProcessorByCode($processors, $code, $result)
    {
        $this->setProperty('processors', $processors);

        if ($result instanceof \Exception) {
            try {
                $this->pool->getProcessorByCode($code);
            } catch (\Exception $e) {
                $this->assertEquals($result, $e);
            }
        } else {
            $this->assertSame($result, $this->pool->getProcessorByCode($code));
        }
    }

    /**
     * @return array
     */
    public function getProcessorByCodeDataProvider()
    {
        $processorDefaultMock = $this->createMock(TypeProcessorInterface::class);
        $processorOneMock = $this->createMock(TypeProcessorInterface::class);
        $badProcessor = $this->createMock(DataObject::class);
        $processors = [
            'default' => $processorDefaultMock,
            'processor_one' => $processorOneMock,
            'processor_bad' => $badProcessor
        ];
        return [
            [
                'processors' => $processors,
                'code' => 'processor_one',
                'result' => $processorOneMock
            ],
            [
                'processors' => $processors,
                'code' => 'unknown_code',
                'result' => $processorDefaultMock
            ],
            [
                'processors' => $processors,
                'code' => 'processor_bad',
                'result' => new ConfigurationMismatchException(
                    __('Type processor must implements %1', TypeProcessorInterface::class)
                )
            ],
        ];
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
        $class = new \ReflectionClass($this->pool);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($this->pool, $value);

        return $this;
    }
}

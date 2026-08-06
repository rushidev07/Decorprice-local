<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Calculator\Earning\EarnItemResolver;

use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemProcessorInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemProcessorPool;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\ConfigurationMismatchException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemProcessorPool
 */
class ItemProcessorPoolTest extends TestCase
{
    /**
     * @var ItemProcessorPool
     */
    private $processorPool;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->processorPool = $objectManager->getObject(ItemProcessorPool::class, []);
    }

    /**
     * Test getProcessors method
     *
     * @param ItemProcessorInterface[]|\PHPUnit_Framework_MockObject_MockObject[] $processors
     * @dataProvider getProcessorsDataProvider
     * @throws \ReflectionException
     */
    public function testGetProcessors($processors)
    {
        $this->setProperty('processors', $processors);

        $this->assertEquals($processors, $this->processorPool->getProcessors());
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
                'processors' => [$this->createMock(ItemProcessorInterface::class)]
            ],
            [
                'processors' => [
                    $this->createMock(ItemProcessorInterface::class),
                    $this->createMock(ItemProcessorInterface::class)
                ]
            ]
        ];
    }

    /**
     * Test getProcessorByCode method
     *
     * @param ItemProcessorInterface[] $processors
     * @param string $code
     * @param ItemProcessorInterface|\Exception $result
     * @throws \ReflectionException
     * @dataProvider getProcessorByCodeDataProvider
     */
    public function testGetProcessorByCode($processors, $code, $result)
    {
        $this->setProperty('processors', $processors);

        if ($result instanceof \Exception) {
            try {
                $this->processorPool->getProcessorByCode($code);
            } catch (\Exception $e) {
                $this->assertEquals($result, $e);
            }
        } else {
            $this->assertSame($result, $this->processorPool->getProcessorByCode($code));
        }
    }

    /**
     * @return array
     */
    public function getProcessorByCodeDataProvider()
    {
        $processorDefaultMock = $this->createMock(ItemProcessorInterface::class);
        $processorOneMock = $this->createMock(ItemProcessorInterface::class);
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
                    __('Item processor must implements %1', ItemProcessorInterface::class)
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
        $class = new \ReflectionClass($this->processorPool);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($this->processorPool, $value);

        return $this;
    }
}

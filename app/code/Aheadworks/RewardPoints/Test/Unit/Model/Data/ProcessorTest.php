<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Data;

use Aheadworks\RewardPoints\Model\Data\Processor;
use Aheadworks\RewardPoints\Model\Data\ProcessorInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\RewardPoints\Model\Data\Processor
 */
class ProcessorTest extends TestCase
{
    /**
     * @var Processor
     */
    private $processor;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->processor = $objectManager->getObject(Processor::class, []);
    }

    /**
     * Test process method
     */
    public function testProcess()
    {
        $data = [
            'entity_id' => 10,
            'name' => 'Sample Name',
        ];
        $dataProcessedByFirst = [
            'entity_id' => 10,
            'name' => 'Sample Name Processed First',
        ];
        $dataProcessedBySecond = [
            'entity_id' => 10,
            'name' => 'Sample Name Processed Second',
        ];

        $processorOneMock = $this->getProcessorMock($data, $dataProcessedByFirst);
        $processorTwoMock = $this->getProcessorMock(
            $dataProcessedByFirst,
            $dataProcessedBySecond
        );

        $processors = [
            'p1' => $processorOneMock,
            'p2' => $processorTwoMock,
        ];

        $this->setProperty('processors', $processors);

        $this->assertEquals($dataProcessedBySecond, $this->processor->process($data));
    }

    /**
     * Get processor mock
     *
     * @param array $data
     * @param array $processedData
     * @return ProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getProcessorMock($data, $processedData)
    {
        $processorMock = $this->createMock(ProcessorInterface::class);
        $processorMock->expects($this->once())
            ->method('process')
            ->with($data)
            ->willReturn($processedData);

        return $processorMock;
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
        $class = new \ReflectionClass($this->processor);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($this->processor, $value);

        return $this;
    }
}

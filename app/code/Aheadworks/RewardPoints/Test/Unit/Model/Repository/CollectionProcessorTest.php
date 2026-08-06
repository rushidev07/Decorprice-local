<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Repository;

use Aheadworks\RewardPoints\Model\Repository\CollectionProcessor;
use Aheadworks\RewardPoints\Model\Repository\CollectionProcessorInterface;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\DataObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\RewardPoints\Model\Repository\CollectionProcessor
 */
class CollectionProcessorTest extends TestCase
{
    /**
     * @var CollectionProcessor
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

        $this->processor = $objectManager->getObject(CollectionProcessor::class, []);
    }

    /**
     * Test process method
     *
     * @param CollectionProcessorInterface[]|\PHPUnit_Framework_MockObject_MockObject[] $processors
     * @param SearchCriteria|\PHPUnit_Framework_MockObject_MockObject $searchCriteria
     * @param AbstractCollection|\PHPUnit_Framework_MockObject_MockObject $collection
     * @throws \ReflectionException
     * @dataProvider processDataProvider
     */
    public function testProcess($processors, $searchCriteria, $collection)
    {
        $this->setProperty('processors', $processors);

        $this->assertNull($this->processor->process($searchCriteria, $collection));
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $collectionMock = $this->createMock(AbstractCollection::class);

        return [
            [
                'processors' => [],
                'searchCriteria' => $searchCriteriaMock,
                'collection' => $collectionMock
            ],
            [
                'processors' => [$this->getProcessorMock($searchCriteriaMock, $collectionMock)],
                'searchCriteria' => $searchCriteriaMock,
                'collection' => $collectionMock
            ],
            [
                'processors' => [
                    $this->getProcessorMock($searchCriteriaMock, $collectionMock),
                    $this->getProcessorMock($searchCriteriaMock, $collectionMock)
                ],
                'searchCriteria' => $searchCriteriaMock,
                'collection' => $collectionMock
            ]
        ];
    }

    /**
     * Test process method if not valid processor
     */
    public function testProcessNotValidProcessor()
    {
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $collectionMock = $this->createMock(AbstractCollection::class);

        $processorMock = $this->createMock(DataObject::class);
        $processors = [$processorMock];
        $this->setProperty('processors', $processors);

        $this->expectException(ConfigurationMismatchException::class);

        $this->assertNull($this->processor->process($searchCriteriaMock, $collectionMock));
    }

    /**
     * Get processor mock
     *
     * @param SearchCriteria|\PHPUnit_Framework_MockObject_MockObject $searchCriteriaMock
     * @param AbstractCollection|\PHPUnit_Framework_MockObject_MockObject $collectionMock
     * @return CollectionProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getProcessorMock($searchCriteriaMock, $collectionMock)
    {
        $processorMock = $this->createMock(CollectionProcessorInterface::class);
        $processorMock->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $collectionMock)
            ->willReturn(null);

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

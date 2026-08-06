<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Repository\CollectionProcessor;

use Aheadworks\RewardPoints\Model\Repository\CollectionProcessor\SortOrderProcessor;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Api\SearchCriteria;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Api\SortOrder;

/**
 * Test for \Aheadworks\RewardPoints\Model\Repository\CollectionProcessor\SortOrderProcessor
 */
class SortOrderProcessorTest extends TestCase
{
    /**
     * @var SortOrderProcessor
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

        $this->processor = $objectManager->getObject(SortOrderProcessor::class, []);
    }

    /**
     * Test process method
     */
    public function testProcess()
    {
        $field = 'field_name';
        $direction = 'asc';

        $sortOrders = [$this->getSortOrderMock($field, $direction)];

        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchCriteriaMock->expects($this->once())
            ->method('getSortOrders')
            ->willReturn($sortOrders);

        $collectionMock = $this->createMock(AbstractCollection::class);
        $collectionMock->expects($this->once())
            ->method('addOrder')
            ->with($field, $direction)
            ->willReturnSelf();

        $this->assertNull($this->processor->process($searchCriteriaMock, $collectionMock));
    }

    /**
     * Test process method if sort orders are empty
     *
     * @param $sortOrders
     * @dataProvider processNoSortOrdersDataProvider
     */
    public function testProcessNoSortOrders($sortOrders)
    {
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchCriteriaMock->expects($this->once())
            ->method('getSortOrders')
            ->willReturn($sortOrders);

        $collectionMock = $this->createMock(AbstractCollection::class);
        $collectionMock->expects($this->never())
            ->method('addOrder');

        $this->assertNull($this->processor->process($searchCriteriaMock, $collectionMock));
    }

    /**
     * @return array
     */
    public function processNoSortOrdersDataProvider()
    {
        return [
            [
                'sortOrders' => null
            ],
            [
                'sortOrders' => []
            ],
        ];
    }

    /**
     * Get sort order mock
     *
     * @param string $field
     * @param string $direction
     * @return SortOrder|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getSortOrderMock($field, $direction)
    {
        $sortOrderMock = $this->createMock(SortOrder::class);
        $sortOrderMock->expects($this->once())
            ->method('getField')
            ->willReturn($field);
        $sortOrderMock->expects($this->once())
            ->method('getDirection')
            ->willReturn($direction);

        return $sortOrderMock;
    }
}

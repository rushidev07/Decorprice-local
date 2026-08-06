<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Repository\CollectionProcessor;

use Aheadworks\RewardPoints\Model\Repository\CollectionProcessor\FilterProcessor;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Api\Search\FilterGroup;

/**
 * Test for \Aheadworks\RewardPoints\Model\Repository\CollectionProcessor\FilterProcessor
 */
class FilterProcessorTest extends TestCase
{
    /**
     * @var FilterProcessor
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

        $this->processor = $objectManager->getObject(FilterProcessor::class, []);
    }

    /**
     * Test process method
     *
     * @param FilterGroup[]|\PHPUnit_Framework_MockObject_MockObject[] $filterGroups
     * @param string[] $fields
     * @param array $conditions
     * @dataProvider processDataProvider
     */
    public function testProcess($filterGroups, $fields, $conditions)
    {
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchCriteriaMock->expects($this->once())
            ->method('getFilterGroups')
            ->willReturn($filterGroups);

        $collectionMock = $this->createMock(AbstractCollection::class);
        if (!empty($fields)) {
            $collectionMock->expects($this->once())
                ->method('addFieldToFilter')
                ->with($fields, $conditions)
                ->willReturnSelf();
        } else {
            $collectionMock->expects($this->never())
                ->method('addFieldToFilter');
        }

        $this->assertNull($this->processor->process($searchCriteriaMock, $collectionMock));
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        $filterInMock = $this->getFilterMock('in', 'field_in', 'value_in');
        $filterEqMock = $this->getFilterMock(null, 'field_eq', 'value_eq');
        return [
            [
                'filterGroups' => [$this->getFilterGroupMock(null)],
                'fields' => [],
                'conditions' => []
            ],
            [
                'filterGroups' => [$this->getFilterGroupMock([$filterInMock])],
                'fields' => ['field_in'],
                'conditions' => [['in' => 'value_in']]
            ],
            [
                'filterGroups' => [$this->getFilterGroupMock([$filterEqMock])],
                'fields' => ['field_eq'],
                'conditions' => [['eq' => 'value_eq']]
            ],
            [
                'filterGroups' => [$this->getFilterGroupMock([$filterInMock, $filterEqMock])],
                'fields' => ['field_in', 'field_eq'],
                'conditions' => [['in' => 'value_in'], ['eq' => 'value_eq']]
            ]
        ];
    }

    /**
     * Get filterGroup mock
     *
     * @param Filter[]|\PHPUnit_Framework_MockObject_MockObject[] $filters
     * @return FilterGroup|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getFilterGroupMock($filters)
    {
        $filterGroupMock = $this->createMock(FilterGroup::class);
        $filterGroupMock->expects($this->any())
            ->method('getFilters')
            ->willReturn($filters);

        return $filterGroupMock;
    }

    /**
     * Get filter mock
     *
     * @param string $conditionType
     * @param string $field
     * @param string $value
     * @return Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getFilterMock($conditionType, $field, $value)
    {
        $filterMock = $this->createMock(Filter::class);
        $filterMock->expects($this->any())
            ->method('getConditionType')
            ->willReturn($conditionType);
        $filterMock->expects($this->any())
            ->method('getField')
            ->willReturn($field);
        $filterMock->expects($this->any())
            ->method('getValue')
            ->willReturn($value);

        return $filterMock;
    }
}

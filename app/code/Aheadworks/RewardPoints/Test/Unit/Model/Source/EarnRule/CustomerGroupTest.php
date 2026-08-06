<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Source\EarnRule;

use Aheadworks\RewardPoints\Model\Source\EarnRule\CustomerGroup;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Convert\DataObject as DataObjectConverter;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\Data\GroupInterface;

/**
 * Test for \Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\Index
 */
class CustomerGroupTest extends TestCase
{
    /**
     * @var CustomerGroup
     */
    private $source;

    /**
     * @var GroupManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $groupManagementMock;

    /**
     * @var DataObjectConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectConverterMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->groupManagementMock = $this->createMock(GroupManagementInterface::class);
        $this->objectConverterMock = $this->createMock(DataObjectConverter::class);

        $this->source = $objectManager->getObject(
            CustomerGroup::class,
            [
                'groupManagement' => $this->groupManagementMock,
                'objectConverter' => $this->objectConverterMock
            ]
        );
    }

    /**
     * Test toOptionArray
     */
    public function testToOptionArray()
    {
        $loggedInGroupMock = $this->createMock(GroupInterface::class);
        $groups = [$loggedInGroupMock];
        $result = [
            ['value' => 1, 'label' => __('GENERAL')]
        ];

        $loggedInGroups = [$loggedInGroupMock];
        $this->groupManagementMock->expects($this->once())
            ->method('getLoggedInGroups')
            ->willReturn($loggedInGroups);

        $this->objectConverterMock->expects($this->once())
            ->method('toOptionArray')
            ->with($groups, 'id', 'code')
            ->willReturn($result);

        $this->assertSame($result, $this->source->toOptionArray());
    }

    /**
     * Test toOptionArray if an error occurs
     */
    public function testToOptionArrayException()
    {
        $groups = [];
        $result = [];

        $this->groupManagementMock->expects($this->once())
            ->method('getLoggedInGroups')
            ->willThrowException(new LocalizedException(__('Error!')));

        $this->objectConverterMock->expects($this->once())
            ->method('toOptionArray')
            ->with($groups, 'id', 'code')
            ->willReturn($result);

        $this->assertSame($result, $this->source->toOptionArray());
    }

    /**
     * Test getOptionLabelByValue
     */
    public function testGetOptionLabelByValue()
    {
        $loggedInGroupMock = $this->createMock(GroupInterface::class);
        $groups = [$loggedInGroupMock];
        $result = [
            ['value' => 1, 'label' => __('GENERAL')]
        ];

        $loggedInGroups = [$loggedInGroupMock];
        $this->groupManagementMock->expects($this->once())
            ->method('getLoggedInGroups')
            ->willReturn($loggedInGroups);

        $this->objectConverterMock->expects($this->once())
            ->method('toOptionArray')
            ->with($groups, 'id', 'code')
            ->willReturn($result);

        $this->assertEquals(__('GENERAL'), $this->source->getOptionLabelByValue(1));
        $this->assertNull($this->source->getOptionLabelByValue(10));
    }
}

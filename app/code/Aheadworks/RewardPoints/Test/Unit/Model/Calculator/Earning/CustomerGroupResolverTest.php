<?php
namespace Aheadworks\RewardPoints\Test\Unit\Model\Calculator\Earning;

use Aheadworks\RewardPoints\Model\Calculator\Earning\CustomerGroupResolver;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\RewardPoints\Model\Calculator\Earning\CustomerGroupResolver
 */
class CustomerGroupResolverTest extends TestCase
{
    /**
     * @var CustomerGroupResolver
     */
    private $resolver;

    /**
     * @var GroupManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $groupManagementMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->groupManagementMock = $this->createMock(GroupManagementInterface::class);

        $this->resolver = $objectManager->getObject(
            CustomerGroupResolver::class,
            [
                'groupManagement' => $this->groupManagementMock,
            ]
        );
    }

    /**
     * Test getCustomerGroupIds method
     *
     * @param GroupInterface|\PHPUnit_Framework_MockObject_MockObject $groups
     * @param int[] $result
     * @throws LocalizedException
     * @dataProvider getCustomerGroupIdsDataProvider
     */
    public function testGetCustomerGroupIds($groups, $result)
    {
        $this->groupManagementMock->expects($this->once())
            ->method('getLoggedInGroups')
            ->willReturn($groups);

        $this->assertEquals($result, $this->resolver->getCustomerGroupIds());
    }

    /**
     * @return array
     */
    public function getCustomerGroupIdsDataProvider()
    {
        return [
            [
                'groups' =>  [
                    $this->getCustomerGroupMock(10),
                    $this->getCustomerGroupMock(11)
                ],
                'result' => [10, 11]
            ],
            [
                'groups' =>  [
                    $this->getCustomerGroupMock(10)
                ],
                'result' => [10]
            ],
            [
                'groups' =>  [],
                'result' => []
            ],
        ];
    }

    /**
     * Test getAllCustomerGroupId method
     */
    public function testGetAllCustomerGroupId()
    {
        $allCustomerGroupId = 32000;
        $groupMock = $this->getCustomerGroupMock($allCustomerGroupId);

        $this->groupManagementMock->expects($this->once())
            ->method('getAllCustomersGroup')
            ->willReturn($groupMock);

        $this->assertEquals($allCustomerGroupId, $this->resolver->getAllCustomerGroupId());
    }

    /**
     * Test getAllCustomerGroupId method if an error occurs
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Error!
     */
    public function testGetAllCustomerGroupIdError()
    {
        $this->groupManagementMock->expects($this->once())
            ->method('getAllCustomersGroup')
            ->willThrowException(new LocalizedException(__('Error!')));
$this->expectException(LocalizedException::class);
        $this->resolver->getAllCustomerGroupId();
    }

    /**
     * Get customer group mock
     *
     * @param int $customerGroupId
     * @return GroupInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getCustomerGroupMock($customerGroupId)
    {
        $groupMock = $this->createMock(GroupInterface::class);
        $groupMock->expects($this->once())
            ->method('getId')
            ->willReturn($customerGroupId);

        return $groupMock;
    }
}

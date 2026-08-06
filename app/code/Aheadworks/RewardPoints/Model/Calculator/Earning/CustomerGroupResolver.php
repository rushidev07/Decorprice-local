<?php
namespace Aheadworks\RewardPoints\Model\Calculator\Earning;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupManagementInterface;

/**
 * Class CustomerGroupResolver
 * @package Aheadworks\RewardPoints\Model\Calculator\Earning\Calculator
 */
class CustomerGroupResolver
{
    /**
     * @var GroupManagementInterface
     */
    private $groupManagement;

    /**
     * @param GroupManagementInterface $groupManagement
     */
    public function __construct(
        GroupManagementInterface $groupManagement
    ) {
        $this->groupManagement = $groupManagement;
    }

    /**
     * Get customer group ids
     *
     * @return int[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerGroupIds()
    {
        /** @var GroupInterface[] $groups */
        $groups = $this->groupManagement->getLoggedInGroups();
        $groupIds = [];
        foreach ($groups as $group) {
            $groupIds[] = $group->getId();
        }

        return $groupIds;
    }

    /**
     * Get 'ALL' customer group id
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllCustomerGroupId()
    {
        /** @var GroupInterface $groups */
        $group = $this->groupManagement->getAllCustomersGroup();

        return $group->getId();
    }
}

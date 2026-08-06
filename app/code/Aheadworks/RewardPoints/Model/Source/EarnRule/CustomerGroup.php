<?php
namespace Aheadworks\RewardPoints\Model\Source\EarnRule;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Convert\DataObject as DataObjectConverter;

/**
 * Class CustomerGroup
 * @package Aheadworks\RewardPoints\Model\Source\EarnRule
 */
class CustomerGroup implements OptionSourceInterface
{
    /**
     * @var GroupManagementInterface
     */
    private $groupManagement;

    /**
     * @var DataObjectConverter
     */
    private $objectConverter;

    /**
     * @var array
     */
    private $options;

    /**
     * @param GroupManagementInterface $groupManagement
     * @param DataObjectConverter $objectConverter
     */
    public function __construct(
        GroupManagementInterface $groupManagement,
        DataObjectConverter $objectConverter
    ) {
        $this->groupManagement = $groupManagement;
        $this->objectConverter = $objectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $customerGroups = $this->getCustomerGroups();
            $this->options = $this->getOptionArray($customerGroups);
        }

        return $this->options;
    }

    /**
     * Retrieve customer groups
     *
     * @return GroupInterface[]
     */
    private function getCustomerGroups()
    {
        try {
            $customerGroups = $this->groupManagement->getLoggedInGroups();
        } catch (LocalizedException $e) {
            $customerGroups = [];
        }

        return $customerGroups;
    }

    /**
     * Retrieve option array from customer groups data
     *
     * @param GroupInterface[] $customerGroups
     * @return array
     */
    private function getOptionArray($customerGroups)
    {
        $groupsOptions = $this->objectConverter->toOptionArray(
            $customerGroups,
            'id',
            'code'
        );

        return $groupsOptions;
    }

    /**
     * Get option label by value
     *
     * @param int $value
     * @return string|null
     */
    public function getOptionLabelByValue($value)
    {
        $options = $this->toOptionArray();
        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }

        return null;
    }
}

<?php
namespace Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\PostDataProcessor;

use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Model\Data\ProcessorInterface;

/**
 * Class CustomerGroup
 * @package Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\PostDataProcessor
 */
class CustomerGroup implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($data)
    {
        $customerGroupData = [];
        if (isset($data[EarnRuleInterface::CUSTOMER_GROUP_IDS])
            && is_array($data[EarnRuleInterface::CUSTOMER_GROUP_IDS])
        ) {
            foreach ($data[EarnRuleInterface::CUSTOMER_GROUP_IDS] as $key => $value) {
                $customerGroupData[$key] = (int)$value;
            }
        }
        $data[EarnRuleInterface::CUSTOMER_GROUP_IDS] = $customerGroupData;

        return $data;
    }
}

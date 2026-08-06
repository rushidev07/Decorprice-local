<?php
namespace Aheadworks\RewardPoints\Ui\DataProvider\EarnRule\DataProcessor;

use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Model\Data\ProcessorInterface;

/**
 * Class CustomerGroup
 * @package Aheadworks\RewardPoints\Ui\DataProvider\EarnRule\DataProcessor
 */
class CustomerGroup implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($data)
    {
        if (isset($data[EarnRuleInterface::CUSTOMER_GROUP_IDS])
            && is_array($data[EarnRuleInterface::CUSTOMER_GROUP_IDS])
        ) {
            foreach ($data[EarnRuleInterface::CUSTOMER_GROUP_IDS] as $key => $value) {
                $data[EarnRuleInterface::CUSTOMER_GROUP_IDS][$key] = (string)$value;
            }
        }
        return $data;
    }
}

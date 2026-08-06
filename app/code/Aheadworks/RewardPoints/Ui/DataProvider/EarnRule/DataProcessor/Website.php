<?php
namespace Aheadworks\RewardPoints\Ui\DataProvider\EarnRule\DataProcessor;

use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Model\Data\ProcessorInterface;

/**
 * Class Website
 * @package Aheadworks\RewardPoints\Ui\DataProvider\EarnRule\DataProcessor
 */
class Website implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($data)
    {
        if (isset($data[EarnRuleInterface::WEBSITE_IDS]) && is_array($data[EarnRuleInterface::WEBSITE_IDS])) {
            foreach ($data[EarnRuleInterface::WEBSITE_IDS] as $key => $value) {
                $data[EarnRuleInterface::WEBSITE_IDS][$key] = (string)$value;
            }
        }
        return $data;
    }
}

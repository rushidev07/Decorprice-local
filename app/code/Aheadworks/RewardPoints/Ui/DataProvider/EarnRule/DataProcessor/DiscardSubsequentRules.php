<?php
namespace Aheadworks\RewardPoints\Ui\DataProvider\EarnRule\DataProcessor;

use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Model\Data\ProcessorInterface;

/**
 * Class DiscardSubsequentRules
 * @package Aheadworks\RewardPoints\Ui\DataProvider\EarnRule\DataProcessor
 */
class DiscardSubsequentRules implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($data)
    {
        if (isset($data[EarnRuleInterface::DISCARD_SUBSEQUENT_RULES])) {
            $value = (int) $data[EarnRuleInterface::DISCARD_SUBSEQUENT_RULES];
            $data[EarnRuleInterface::DISCARD_SUBSEQUENT_RULES] = (string)$value;
        }
        return $data;
    }
}

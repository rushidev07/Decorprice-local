<?php
namespace Aheadworks\RewardPoints\Ui\DataProvider\EarnRule\DataProcessor;

use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Model\Action as RuleAction;
use Aheadworks\RewardPoints\Model\Data\ProcessorInterface;
use Magento\Framework\Api\AttributeInterface;

/**
 * Class Action
 * @package Aheadworks\RewardPoints\Ui\DataProvider\EarnRule\DataProcessor
 */
class Action implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($data)
    {
        if (isset($data[EarnRuleInterface::ACTION]) && is_array($data[EarnRuleInterface::ACTION])) {
            $action = $data[EarnRuleInterface::ACTION];
            $actionData = [
                RuleAction::TYPE => $action[RuleAction::TYPE]
            ];

            if (isset($action[RuleAction::ATTRIBUTES])) {
                foreach ($action[RuleAction::ATTRIBUTES] as $attributeData) {
                    $actionData[$attributeData[AttributeInterface::ATTRIBUTE_CODE]] =
                        (string)$attributeData[AttributeInterface::VALUE];
                }
            }
            $data[EarnRuleInterface::ACTION] = $actionData;
        }
        return $data;
    }
}

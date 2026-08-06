<?php
namespace Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\PostDataProcessor;

use Aheadworks\RewardPoints\Model\Action as RuleAction;
use Aheadworks\RewardPoints\Model\Data\ProcessorInterface;
use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Model\Action\Converter as ActionConverter;
use Aheadworks\RewardPoints\Model\EarnRule\Action\TypePool as ActionTypePool;

/**
 * Class Action
 * @package Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\PostDataProcessor
 */
class Action implements ProcessorInterface
{
    /**
     * @var ActionConverter
     */
    private $actionConverter;

    /**
     * @var ActionTypePool
     */
    private $actionTypePool;

    /**
     * @param ActionConverter $actionConverter
     * @param ActionTypePool $actionTypePool
     */
    public function __construct(
        ActionConverter $actionConverter,
        ActionTypePool $actionTypePool
    ) {
        $this->actionConverter = $actionConverter;
        $this->actionTypePool = $actionTypePool;
    }

    /**
     * {@inheritdoc}
     */
    public function process($data)
    {
        if (isset($data[EarnRuleInterface::ACTION]) && is_array($data[EarnRuleInterface::ACTION])) {
            if (isset($data[EarnRuleInterface::ACTION][RuleAction::TYPE])) {
                $typeCode = $data[EarnRuleInterface::ACTION][RuleAction::TYPE];
                $actionType = $this->actionTypePool->getTypeByCode($typeCode);
                $attributesData = [];
                foreach ($actionType->getAttributeCodes() as $attributeCode) {
                    if (isset($data[EarnRuleInterface::ACTION][$attributeCode])) {
                        $attributesData[$attributeCode] = $data[EarnRuleInterface::ACTION][$attributeCode];
                    }
                }
                $actionData = [
                    RuleAction::TYPE => $typeCode,
                    RuleAction::ATTRIBUTES => $attributesData,
                ];
                $data[EarnRuleInterface::ACTION] = $this->actionConverter->arrayToDataModel($actionData);
            }
        }

        return $data;
    }
}

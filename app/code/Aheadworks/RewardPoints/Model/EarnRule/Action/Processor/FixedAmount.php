<?php
namespace Aheadworks\RewardPoints\Model\EarnRule\Action\Processor;

use Aheadworks\RewardPoints\Model\Action\AttributeProcessor;
use Aheadworks\RewardPoints\Model\EarnRule\Action\ProcessorInterface as ActionProcessorInterface;

/**
 * Class FixedAmount
 * @package Aheadworks\RewardPoints\Model\EarnRule\Action\Processor
 */
class FixedAmount implements ActionProcessorInterface
{
    /**
     * @var AttributeProcessor
     */
    private $attributeProcessor;

    /**
     * @param AttributeProcessor $attributeProcessor
     */
    public function __construct(
        AttributeProcessor $attributeProcessor
    ) {
        $this->attributeProcessor = $attributeProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function process($value, $qty, $attributes)
    {
        $amount = $this->attributeProcessor->getAttributeValueByCode('amount', $attributes);
        if (is_numeric($amount)) {
            $value += ($amount * $qty);
        }

        return $value;
    }
}

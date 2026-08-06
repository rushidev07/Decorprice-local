<?php
namespace Aheadworks\RewardPoints\Model\EarnRule\Action\Processor;

use Aheadworks\RewardPoints\Model\Action\AttributeProcessor;
use Aheadworks\RewardPoints\Model\EarnRule\Action\ProcessorInterface as ActionProcessorInterface;

/**
 * Class RateMultiplier
 * @package Aheadworks\RewardPoints\Model\EarnRule\Action\Processor
 */
class RateMultiplier implements ActionProcessorInterface
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
        $multiplier = $this->attributeProcessor->getAttributeValueByCode('multiplier', $attributes);
        if (is_numeric($multiplier)) {
            $value *= $multiplier;
        }

        return $value;
    }
}

<?php
namespace Aheadworks\RewardPoints\Model\Source\EarnRule;

use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 * @package Aheadworks\RewardPoints\Model\Source\EarnRule
 */
class Status implements OptionSourceInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [
                [
                    'value' => EarnRuleInterface::STATUS_DISABLED,
                    'label' => __('Disabled')
                ],
                [
                    'value' => EarnRuleInterface::STATUS_ENABLED,
                    'label' => __('Enabled')
                ]
            ];
        }
        return $this->options;
    }
}

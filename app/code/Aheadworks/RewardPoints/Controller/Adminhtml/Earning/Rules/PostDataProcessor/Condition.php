<?php
namespace Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\PostDataProcessor;

use Aheadworks\RewardPoints\Api\Data\ConditionInterface;
use Aheadworks\RewardPoints\Model\Data\ProcessorInterface;
use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Model\EarnRule\Condition\Rule as ConditionRule;
use Aheadworks\RewardPoints\Model\EarnRule\Condition\RuleFactory as ConditionRuleFactory;
use Aheadworks\RewardPoints\Model\EarnRule\Condition\Converter as ConditionConverter;

/**
 * Class Condition
 * @package Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\PostDataProcessor
 */
class Condition implements ProcessorInterface
{
    /**
     * @var ConditionConverter
     */
    private $conditionConverter;

    /**
     * @var ConditionRuleFactory
     */
    private $conditionRuleFactory;

    /**
     * @param ConditionConverter $conditionConverter
     * @param ConditionRuleFactory $conditionRuleFactory
     */
    public function __construct(
        ConditionConverter $conditionConverter,
        ConditionRuleFactory $conditionRuleFactory
    ) {
        $this->conditionConverter = $conditionConverter;
        $this->conditionRuleFactory = $conditionRuleFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function process($data)
    {
        $data[EarnRuleInterface::CONDITION] = $this->prepareConditionData(
            $data,
            ConditionRule::CONDITIONS_PREFIX
        );

        return $data;
    }

    /**
     * Prepare condition data
     *
     * @param array $data
     * @param string $conditionsKey
     * @return ConditionInterface|string
     */
    private function prepareConditionData(array $data, $conditionsKey)
    {
        $conditionData = [];
        if (isset($data['rule'][$conditionsKey])) {
            $conditionsArray = $this->convertFlatToRecursive($data['rule'], [$conditionsKey]);
            if (is_array($conditionsArray[$conditionsKey]['1'])) {
                $conditionData = $conditionsArray[$conditionsKey]['1'];
            }
        }

        return $this->conditionConverter->arrayToDataModel($conditionData);
    }

    /**
     * Get conditions data recursively
     *
     * @param array $data
     * @param array $allowedKeys
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * phpcs:disable Generic.Metrics.NestingLevel
     */
    private function convertFlatToRecursive(array $data, $allowedKeys = [])
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedKeys) && is_array($value)) {
                foreach ($value as $id => $data) {
                    $path = explode('--', $id);
                    $node = & $result;

                    $pathCount = count($path);
                    for ($i = 0, $l = $pathCount; $i < $l; $i++) {
                        if (!isset($node[$key][$path[$i]])) {
                            $node[$key][$path[$i]] = [];
                        }
                        $node = & $node[$key][$path[$i]];
                    }
                    foreach ($data as $k => $v) {
                        if (is_array($v)) {
                            foreach ($v as $dk => $dv) {
                                if (empty($dv)) {
                                    unset($v[$dk]);
                                }
                            }
                            if (!count($v)) {
                                continue;
                            }
                        }

                        $node[$k] = $v;
                    }
                }
            }
        }

        return $result;
    }
}

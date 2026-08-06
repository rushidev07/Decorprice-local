<?php
namespace Aheadworks\RewardPoints\Model\Comment\Admin;

use Aheadworks\RewardPoints\Model\Comment\CommentInterface;
use Aheadworks\RewardPoints\Model\Source\Transaction\EntityType as TransactionEntityType;
use Magento\Framework\Phrase\Renderer\Placeholder;
use Magento\Framework\UrlInterface;

/**
 * Class AppliedEarningRules
 * @package Aheadworks\RewardPoints\Model\Comment\Admin
 */
class AppliedEarningRules implements CommentInterface
{
    /**
     * Comment type name
     */
    const COMMENT_FOR_APPLIED_EARNING_RULES = 'comment_for_applied_earning_rules';
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $label;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Placeholder
     */
    private $placeholder;

    /**
     * @param UrlInterface $urlBuilder
     * @param Placeholder $placeholder
     * @param int|null $type
     * @param string|array|null $label
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Placeholder $placeholder,
        $type = null,
        $label = null
    ) {
        $this->type = $type;
        $this->label = $label;
        $this->urlBuilder = $urlBuilder;
        $this->placeholder = $placeholder;
    }

    /**
     *  {@inheritDoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     *  {@inheritDoc}
     */
    public function getLabel($key = null, $arguments = [])
    {
        $label = $this->label;
        if (is_array($this->label)) {
            $label = ($key && isset($this->label[$key]))
                ? $this->label[$key]
                : $label = $this->label['default'];
        }
        return __($label, $arguments);
    }

    /**
     * {@inheritDoc}
     * phpcs:disable Magento2.Performance.ForeachArrayMerge
     */
    public function renderComment($arguments = [], $key = null, $label = null, $renderingUrl = false, $frontend = false)
    {
        if (!$label) {
            $label = $this->getLabel();
        }
        $labelArguments = [];
        $rulePlaceholders = [];
        foreach ($arguments as $entityType => $entity) {
            if ($entityType == TransactionEntityType::EARN_RULE_ID) {
                if (isset($entity['entity_id'])) {
                    $ruleLabelData = $this->getRuleLabelData($entity, $renderingUrl);
                    $rulePlaceholders[] = $ruleLabelData['placeholder'];
                    $labelArguments = $ruleLabelData['arguments'];
                } else {
                    foreach ($entity as $item) {
                        $ruleLabelData = $this->getRuleLabelData($item, $renderingUrl);
                        $rulePlaceholders[] = $ruleLabelData['placeholder'];
                        $labelArguments = array_merge($labelArguments, $ruleLabelData['arguments']);
                    }
                }
            }
        }

        $label = str_replace(
            '%rule_ids',
            implode(', ', $rulePlaceholders),
            $label
        );

        return $this->placeholder->render([$label], $labelArguments);
    }

    /**
     * {@inheritDoc}
     */
    public function renderTranslatedComment(
        $arguments = [],
        $key = null,
        $label = null,
        $renderingUrl = false,
        $frontend = false
    ) {
        return $this->renderComment($arguments, $key, __($label), $renderingUrl, $frontend);
    }

    /**
     * Get rule label data
     *
     * @param array $entity
     * @param bool $renderingUrl
     * @return array
     */
    private function getRuleLabelData($entity, $renderingUrl)
    {
        $arguments = [];
        $entityId = $entity['entity_id'];
        $idName = 'rule_id_' . $entityId;
        $arguments[$idName] = '#' . $entity['entity_label'];
        $placeholder = '%' . $idName;
        if ($renderingUrl) {
            $urlName = 'rule_url_' . $entityId;
            $arguments[$urlName] = $this->getEarnRuleUrl($entityId);
            $placeholder = '<a href="%' . $urlName . '">%' . $idName . '</a>';
        }

        return [
            'placeholder' => $placeholder,
            'arguments' => $arguments,
        ];
    }

    /**
     * Retrieve earn rule url
     *
     * @param int $ruleId
     * @return string
     */
    private function getEarnRuleUrl($ruleId)
    {
        return $this->urlBuilder->getUrl('aw_reward_points/earning_rules/edit', ['id' => $ruleId]);
    }
}

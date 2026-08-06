<?php
namespace Aheadworks\RewardPoints\Model\ResourceModel\EarnRule;

use Aheadworks\RewardPoints\Model\ResourceModel\StorefrontLabelsEntity\AbstractCollection
    as StorefrontLabelsEntityAbstractCollection;
use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Model\EarnRule;
use Aheadworks\RewardPoints\Model\ResourceModel\EarnRule as EarnRuleResource;

/**
 * Class Collection
 * @package Aheadworks\RewardPoints\Model\ResourceModel\EarnRule
 * @codeCoverageIgnore
 */
class Collection extends StorefrontLabelsEntityAbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = EarnRuleResource::MAIN_TABLE_ID_FIELD_NAME;

    /**
     * @var array
     */
    private $publicFilterFields = [
        EarnRuleInterface::CUSTOMER_GROUP_IDS,
        EarnRuleInterface::WEBSITE_IDS
    ];

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(EarnRule::class, EarnRuleResource::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function getStorefrontLabelsEntityType()
    {
        return EarnRuleInterface::STOREFRONT_LABELS_ENTITY_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        $this->attachRelationTable(
            EarnRuleResource::CUSTOMER_GROUP_TABLE_NAME,
            'id',
            'rule_id',
            'customer_group_id',
            'customer_group_ids',
            [],
            [],
            true
        );
        $this->attachRelationTable(
            EarnRuleResource::WEBSITE_TABLE_NAME,
            'id',
            'rule_id',
            'website_id',
            'website_ids',
            [],
            [],
            true
        );
        return parent::_afterLoad();
    }

    /**
     * {@inheritdoc}
     */
    protected function _renderFiltersBefore()
    {
        $this->joinLinkageTable(
            EarnRuleResource::CUSTOMER_GROUP_TABLE_NAME,
            'id',
            'rule_id',
            'customer_group_ids',
            'customer_group_id'
        );
        $this->joinLinkageTable(
            EarnRuleResource::WEBSITE_TABLE_NAME,
            'id',
            'rule_id',
            'website_ids',
            'website_id'
        );
        parent::_renderFiltersBefore();
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldToFilter($field, $condition = null)
    {
        $fieldsToProcess = $this->processAddFieldToFilter($field, $condition);

        if (!empty($fieldsToProcess)) {
            return parent::addFieldToFilter($fieldsToProcess, $condition);
        }

        return $this;
    }

    /**
     * Process adding fields to filter
     *
     * @param string|array $field
     * @param null|string|array $condition
     * @return array|string
     */
    private function processAddFieldToFilter($field, $condition = null)
    {
        $fieldsToProcess = null;
        if (is_array($field)) {
            $fieldsToProcess = [];
            foreach ($field as $fieldName) {
                if ($this->isPublicFilter($fieldName)) {
                    $this->addFilter($fieldName, $condition, 'public');
                } elseif ($fieldName == EarnRuleInterface::FROM_DATE) {
                    $this->addFromDateFilter($condition);
                } elseif ($fieldName == EarnRuleInterface::TO_DATE) {
                    $this->addToDateFilter($condition);
                } else {
                    $fieldsToProcess[] = $fieldName;
                }
            }
        } else {
            if ($this->isPublicFilter($field)) {
                $this->addFilter($field, $condition, 'public');
            } else {
                $fieldsToProcess = $field;
            }
        }

        return $fieldsToProcess;
    }

    /**
     * Check if need to apply public filter instead of native logic
     *
     * @param string $fieldName
     * @return bool
     */
    private function isPublicFilter($fieldName)
    {
        return (in_array($fieldName, $this->publicFilterFields));
    }

    /**
     * Add 'FROM' date filter
     *
     * @param string $fromDate
     * @return $this
     */
    public function addFromDateFilter($fromDate)
    {
        $fromDateField = 'main_table.' . EarnRuleInterface::FROM_DATE;
        $fromCondition = '(' . $fromDateField . ' IS NULL OR ' . $fromDateField . '<= ?)';
        $this
            ->getSelect()
            ->where($fromCondition, $fromDate);

        return $this;
    }

    /**
     * Add 'TO' date filter
     *
     * @param string $toDate
     * @return $this
     */
    public function addToDateFilter($toDate)
    {
        $toDateField = 'main_table.' . EarnRuleInterface::TO_DATE;
        $toCondition = '(' . $toDateField . ' IS NULL OR ' . $toDateField . '>= ?)';
        $this
            ->getSelect()
            ->where($toCondition, $toDate);

        return $this;
    }
}

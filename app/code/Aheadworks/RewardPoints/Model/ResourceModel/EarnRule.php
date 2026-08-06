<?php
namespace Aheadworks\RewardPoints\Model\ResourceModel;

use Aheadworks\RewardPoints\Model\Indexer\EarnRule\ProductInterface as EarnRuleProductInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class EarnRule
 * @package Aheadworks\RewardPoints\Model\ResourceModel
 * @codeCoverageIgnore
 */
class EarnRule extends AbstractDb
{
    /**#@+
     * Constants defined for tables
     * used by corresponding entity
     */
    const MAIN_TABLE_ID_FIELD_NAME  = 'id';
    const MAIN_TABLE_NAME           = 'aw_rp_earn_rule';
    const WEBSITE_TABLE_NAME        = 'aw_rp_earn_rule_website';
    const CUSTOMER_GROUP_TABLE_NAME = 'aw_rp_earn_rule_customer_group';
    const PRODUCT_TABLE_NAME        = 'aw_rp_earn_rule_product';
    const PRODUCT_IDX_TABLE_NAME    = 'aw_rp_earn_rule_product_idx';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE_NAME, self::MAIN_TABLE_ID_FIELD_NAME);
    }

    /**
     * Retrieve rule ids to apply
     *
     * @param int $productId
     * @param int $customerGroupId
     * @param int $websiteId
     * @param string $currentDate
     * @return array
     */
    public function getRuleIdsToApply($productId, $customerGroupId, $websiteId, $currentDate)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(
                $this->getTable(self::PRODUCT_TABLE_NAME),
                [EarnRuleProductInterface::RULE_ID]
            )
            ->where(EarnRuleProductInterface::WEBSITE_ID . ' = ?', $websiteId)
            ->where(EarnRuleProductInterface::CUSTOMER_GROUP_ID . ' = ?', $customerGroupId)
            ->where(EarnRuleProductInterface::PRODUCT_ID . ' = ?', $productId)
            ->where(
                'ISNULL(' . EarnRuleProductInterface::FROM_DATE . ') OR '
                . EarnRuleProductInterface::FROM_DATE . ' <= ?',
                $currentDate
            )
            ->where(
                'ISNULL(' . EarnRuleProductInterface::TO_DATE . ') OR '
                . EarnRuleProductInterface::TO_DATE . ' >= ?',
                $currentDate
            )
            ->order(EarnRuleProductInterface::PRIORITY . ' ASC')
            ->order(EarnRuleProductInterface::RULE_ID . ' DESC');

        return $connection->fetchAll($select);
    }
}

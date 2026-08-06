<?php
namespace Aheadworks\RewardPoints\Model\Indexer\EarnRule;

/**
 * Interface ProductInterface
 * @package Aheadworks\RewardPoints\Model\Indexer\EarnRule
 */
interface ProductInterface
{
    /**#@+
     * Constants for keys of indexer fields.
     */
    const ID                        = 'id';
    const RULE_ID                   = 'rule_id';
    const FROM_DATE                 = 'from_date';
    const TO_DATE                   = 'to_date';
    const CUSTOMER_GROUP_ID         = 'customer_group_id';
    const WEBSITE_ID                = 'website_id';
    const PRODUCT_ID                = 'product_id';
    const PRIORITY                  = 'priority';
    /**#@-*/
}

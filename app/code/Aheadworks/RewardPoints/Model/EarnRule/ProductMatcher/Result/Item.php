<?php
namespace Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\Result;

use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Class Item
 * @package Aheadworks\RewardPoints\Model\EarnRule\ProductMatcher\Result
 * @codeCoverageIgnore
 */
class Item extends AbstractSimpleObject
{
    /**#@+
     * Constants for keys.
     */
    const PRODUCT_ID    = 'product_id';
    const WEBSITE_IDS   = 'website_ids';
    /**#@-*/

    /**
     * Get product id
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->_get(self::PRODUCT_ID);
    }

    /**
     * Set product id
     *
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * Get website ids
     *
     * @return int[]
     */
    public function getWebsiteIds()
    {
        return $this->_get(self::WEBSITE_IDS);
    }

    /**
     * Set website ids
     *
     * @param int[] $websiteIds
     * @return $this
     */
    public function setWebsiteIds($websiteIds)
    {
        return $this->setData(self::WEBSITE_IDS, $websiteIds);
    }
}

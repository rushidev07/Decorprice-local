<?php
namespace Aheadworks\RewardPoints\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface StorefrontLabelsInterface
 *
 * @package Aheadworks\RewardPoints\Api\Data
 * @api
 */
interface StorefrontLabelsInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const STORE_ID = 'store_id';
    const PRODUCT_PROMO_TEXT = 'product_promo_text';
    const CATEGORY_PROMO_TEXT = 'category_promo_text';
    /**#@-*/

    /**
     * Get store id
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get product promo text
     *
     * @return string
     */
    public function getProductPromoText();

    /**
     * Set product promo text
     *
     * @param string $productPromoText
     * @return $this
     */
    public function setProductPromoText($productPromoText);

    /**
     * Get category promo text
     *
     * @return string
     */
    public function getCategoryPromoText();

    /**
     * Set category promo text
     *
     * @param string $categoryPromoText
     * @return $this
     */
    public function setCategoryPromoText($categoryPromoText);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\RewardPoints\Api\Data\StorefrontLabelsExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\RewardPoints\Api\Data\StorefrontLabelsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\RewardPoints\Api\Data\StorefrontLabelsExtensionInterface $extensionAttributes
    );
}

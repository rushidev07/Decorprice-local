<?php
namespace Aheadworks\RewardPoints\Model\Calculator\Earning;

/**
 * Interface EarnItemInterface
 * @package Aheadworks\RewardPoints\Model\Calculator\Earning
 */
interface EarnItemInterface
{
    /**
     * Get product id
     *
     * @return int|null
     */
    public function getProductId();
    /**
     * Set product id
     *
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);

    /**
     * Get base amount
     *
     * @return float|null
     */
    public function getBaseAmount();

    /**
     * Set base amount
     *
     * @param float $baseAmount
     * @return $this
     */
    public function setBaseAmount($baseAmount);

    /**
     * Get qty
     *
     * @return float|null
     */
    public function getQty();

    /**
     * Set qty
     *
     * @param float $qty
     * @return $this
     */
    public function setQty($qty);
}

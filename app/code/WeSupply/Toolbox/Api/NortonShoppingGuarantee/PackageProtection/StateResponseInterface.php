<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @version      v1.0.0
 * @since        v1.12.11
 * @created      2025-03-24
 */

namespace WeSupply\Toolbox\Api\NortonShoppingGuarantee\PackageProtection;

/**
 * Interface StateResponseInterface
 *
 * @package WeSupply\Toolbox\Api\NortonShoppingGuarantee\PackageProtection
 */
interface StateResponseInterface
{
    /**
     * @return bool
     */
    public function getIsEpsi();

    /**
     * @param bool $isEpsi
     * @return $this
     */
    public function setIsEpsi($isEpsi);

    /**
     * @return float
     */
    public function getCartFee();

    /**
     * @param float $cartFee
     * @return $this
     */
    public function setCartFee($cartFee);
}

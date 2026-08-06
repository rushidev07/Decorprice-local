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
 * Interface SendStateInterface
 *
 * @package WeSupply\Toolbox\Api\NortonShoppingGuarantee\PackageProtection
 */
interface SendStateInterface
{
    /**
     * Send the state to update the cart.
     *
     * @param string $isEpsi
     *
     * @return StateResponseInterface
     */
    public function execute($isEpsi);
}

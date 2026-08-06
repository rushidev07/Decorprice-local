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
 * Interface GetStateInterface
 *
 * @package WeSupply\Toolbox\Api\NortonShoppingGuarantee\PackageProtection
 */
interface GetStateInterface
{
    /**
     * Retrieve the masked_id of the current cart.
     *
     * @return StateResponseInterface
     */
    public function execute();
}

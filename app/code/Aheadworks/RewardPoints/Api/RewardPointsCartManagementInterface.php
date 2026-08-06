<?php
namespace Aheadworks\RewardPoints\Api;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

/**
 * @api
 */
interface RewardPointsCartManagementInterface
{
    /**
     * Returns information for a reward point in a specified cart.
     *
     * @param  int $cartId
     * @return boolean
     * @throws NoSuchEntityException The specified cart does not exist.
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function get($cartId);

    /**
     * Adds a reward points to a specified cart.
     *
     * @param  int $cartId
     * @param  int $pointsQty
     * @return mixed
     * @throws NoSuchEntityException The specified cart does not exist.
     * @throws CouldNotSaveException The specified reward points not be added.
     */
    public function set($cartId, $pointsQty);

    /**
     * Deletes a reward points from a specified cart.
     *
     * @param  int $cartId
     * @return boolean
     * @throws NoSuchEntityException The specified cart does not exist.
     * @throws CouldNotDeleteException The specified reward points could not be deleted.
     */
    public function remove($cartId);

    /**
     * Retrieve reward points metadata for customer cart
     *
     * @param int $customerId
     * @param int $cartId
     * @return \Aheadworks\RewardPoints\Api\Data\CustomerCartMetadataInterface
     */
    public function getCustomerCartMetadata($customerId, $cartId);
}

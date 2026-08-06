<?php
namespace Aheadworks\RewardPoints\Api\Data;

/**
 * Interface ValidatableEntityInterface
 *
 * @package Aheadworks\RewardPoints\Api\Data
 * @api
 */
interface ValidatableEntityInterface
{
    /**
     * Validate entity
     *
     * @return $this
     * @throws \Magento\Framework\Validator\Exception
     */
    public function validate();
}

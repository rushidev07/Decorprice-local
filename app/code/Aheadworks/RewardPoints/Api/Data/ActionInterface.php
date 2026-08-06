<?php
namespace Aheadworks\RewardPoints\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Class ActionInterface
 * @package Aheadworks\RewardPoints\Api\Data
 * @api
 */
interface ActionInterface extends ExtensibleDataInterface
{
    /**
     * Get type
     *
     * @return string|null
     */
    public function getType();

    /**
     * Set type
     *
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * Get attributes
     *
     * @return \Magento\Framework\Api\AttributeInterface[]
     */
    public function getAttributes();

    /**
     * Set conditions
     *
     * @param \Magento\Framework\Api\AttributeInterface[] $attributes
     * @return $this
     */
    public function setAttributes($attributes);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\RewardPoints\Api\Data\ActionExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\RewardPoints\Api\Data\ActionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\RewardPoints\Api\Data\ActionExtensionInterface $extensionAttributes
    );
}

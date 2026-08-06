<?php
namespace Aheadworks\RewardPoints\Model;

use Aheadworks\RewardPoints\Api\Data\ActionInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * Class Action
 * @package Aheadworks\RewardPoints\Model
 * @codeCoverageIgnore
 */
class Action extends AbstractExtensibleObject implements ActionInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const TYPE          = 'type';
    const ATTRIBUTES    = 'attributes';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->_get(self::TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return $this->_get(self::ATTRIBUTES);
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributes($attributes)
    {
        return $this->setData(self::ATTRIBUTES, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        \Aheadworks\RewardPoints\Api\Data\ActionExtensionInterface $extensionAttributes
    ) {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}

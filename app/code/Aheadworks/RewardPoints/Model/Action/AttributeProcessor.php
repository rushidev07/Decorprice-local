<?php
namespace Aheadworks\RewardPoints\Model\Action;

use Magento\Framework\Api\AttributeInterface;

/**
 * Class AttributeProcessor
 * @package Aheadworks\RewardPoints\Model\Action
 */
class AttributeProcessor
{
    /**
     * Get attribute value by code
     *
     * @param string $code
     * @param AttributeInterface[] $attributes
     * @return mixed|null
     * @throws \Exception
     */
    public function getAttributeValueByCode($code, $attributes)
    {
        $value = null;
        /** @var AttributeInterface $attribute */
        foreach ($attributes as $attribute) {
            if ($code == $attribute->getAttributeCode()) {
                $value = $attribute->getValue();
                break;
            }
        }

        return $value;
    }
}

<?php
namespace Aheadworks\RewardPoints\Model\EarnRule;

use Magento\Framework\Validator\AbstractValidator;
use Aheadworks\RewardPoints\Model\StorefrontLabelsEntity\Validator as StorefrontLabelsEntityValidator;
use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;

/**
 * Class Validator
 *
 * @package Aheadworks\RewardPoints\Model\EarnRule
 */
class Validator extends AbstractValidator
{
    /**
     * @var StorefrontLabelsEntityValidator
     */
    private $storefrontLabelsEntityValidator;

    /**
     * @param StorefrontLabelsEntityValidator $storefrontLabelsEntityValidator
     */
    public function __construct(
        StorefrontLabelsEntityValidator $storefrontLabelsEntityValidator
    ) {
        $this->storefrontLabelsEntityValidator = $storefrontLabelsEntityValidator;
    }

    /**
     * Returns true if and only if earn rule entity meets the validation requirements
     *
     * @param EarnRuleInterface $earnRule
     * @return bool
     */
    public function isValid($earnRule)
    {
        $this->_clearMessages();

        if (!$this->storefrontLabelsEntityValidator->isValid($earnRule)) {
            $this->_addMessages($this->storefrontLabelsEntityValidator->getMessages());
            return false;
        }

        return true;
    }
}

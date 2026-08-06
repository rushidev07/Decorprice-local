<?php
namespace Aheadworks\RewardPoints\Model\Validator;

use Magento\Framework\Validator\AbstractValidator;

/**
 * Class Composite
 *
 * @package Aheadworks\RewardPoints\Model\Validator
 */
class Composite extends AbstractValidator
{
    /**
     * @var AbstractValidator[]
     */
    protected $validatorList = [];

    /**
     * @param AbstractValidator[] $validatorList
     */
    public function __construct(
        array $validatorList = []
    ) {
        $this->validatorList = $validatorList;
    }

    /**
     * @inheritdoc
     */
    public function isValid($abstractModel)
    {
        $this->_clearMessages();

        foreach ($this->validatorList as $validator) {
            if (!$validator->isValid($abstractModel)) {
                $this->_addMessages($validator->getMessages());
            }
        }

        return empty($this->getMessages());
    }
}

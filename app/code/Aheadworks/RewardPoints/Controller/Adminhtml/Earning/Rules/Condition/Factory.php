<?php
namespace Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\Condition;

use Magento\Framework\ObjectManagerInterface;
use Magento\CatalogRule\Model\Rule;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Framework\Exception\ConfigurationMismatchException;

/**
 * Class Factory
 * @package Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\Condition
 */
class Factory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $type
     * @param int $id
     * @param string $prefix
     * @param string|null $attribute
     * @param string|null $jsFormObject
     * @param string|null $formName
     * @return AbstractCondition
     * @throws \Exception
     */
    public function create($type, $id, $prefix, $attribute, $jsFormObject, $formName)
    {
        $conditionModel = $this->objectManager->create($type);

        if (!$conditionModel instanceof AbstractCondition) {
            throw new ConfigurationMismatchException(
                __('Condition must be instance of %1', AbstractCondition::class)
            );
        }

        $conditionModel
            ->setId($id)
            ->setType($type)
            ->setRule($this->objectManager->create(Rule::class))
            ->setPrefix($prefix)
            ->setJsFormObject($jsFormObject)
            ->setFormName($formName);

        if ($attribute) {
            $conditionModel->setAttribute($attribute);
        }

        return $conditionModel;
    }
}

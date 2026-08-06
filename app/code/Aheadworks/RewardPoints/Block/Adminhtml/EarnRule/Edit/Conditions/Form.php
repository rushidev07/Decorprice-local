<?php
namespace Aheadworks\RewardPoints\Block\Adminhtml\EarnRule\Edit\Conditions;

use Aheadworks\RewardPoints\Model\EarnRule\Condition\Rule as ConditionRule;
use Aheadworks\RewardPoints\Block\Adminhtml\EarnRule\Edit\Conditions\Form\DataProvider as FormDataProvider;
use Magento\Framework\Data\Form as DataForm;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\UrlInterface;
use Magento\Backend\Block\Widget\Form\Renderer\FieldsetFactory as RendererFieldsetFactory;
use Magento\Rule\Block\Conditions as ConditionsBlock;
use Magento\Rule\Model\AbstractModel;
use Magento\Rule\Model\Condition\Combine as ConditionCombine;

/**
 * Class Form
 * @package Aheadworks\RewardPoints\Block\Adminhtml\EarnRule\Edit\Conditions
 * @codeCoverageIgnore
 */
class Form
{
    /**
     * @var ConditionsBlock
     */
    private $conditionsBlock;

    /**
     * @var RendererFieldsetFactory
     */
    private $rendererFieldsetFactory;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var FormDataProvider
     */
    private $formDataProvider;

    /**
     * @param ConditionsBlock $conditionsBlock
     * @param RendererFieldsetFactory $rendererFieldsetFactory
     * @param UrlInterface $urlBuilder
     * @param FormDataProvider $formDataProvider
     */
    public function __construct(
        ConditionsBlock $conditionsBlock,
        RendererFieldsetFactory $rendererFieldsetFactory,
        UrlInterface $urlBuilder,
        FormDataProvider $formDataProvider
    ) {
        $this->conditionsBlock = $conditionsBlock;
        $this->rendererFieldsetFactory = $rendererFieldsetFactory;
        $this->urlBuilder = $urlBuilder;
        $this->formDataProvider = $formDataProvider;
    }

    /**
     * Retrieve form namespace
     *
     * @return string
     */
    public function getFormNamespace()
    {
        return 'aw_reward_points_earning_rules_form';
    }

    /**
     * Retrieve form id prefix
     *
     * @return string
     */
    public function getFormIdPrefix()
    {
        return 'rule_';
    }

    /**
     * Retrieve new child url route
     *
     * @return string
     */
    public function getNewChildUrlRoute()
    {
        return '*/*/newConditionHtml';
    }

    /**
     * Retrieve form fieldset name
     *
     * @return string
     */
    public function getFormFieldsetName()
    {
        return 'conditions_fieldset';
    }

    /**
     * Retrieve condition field name
     *
     * @return string
     */
    public function getConditionFieldName()
    {
        return ConditionRule::CONDITIONS_PREFIX;
    }

    /**
     * Retrieve js form object name
     *
     * @return string
     */
    public function getJsFormObjectName()
    {
        return $this->getFormIdPrefix() . $this->getFormFieldsetName();
    }

    /**
     * Prepare form
     *
     * @param DataForm $form
     */
    public function prepareForm($form)
    {
        $fieldset = $this->addFieldsetToForm($form);
        $this->prepareFieldset($fieldset);
    }

    /**
     * Add fieldset to specified form
     *
     * @param DataForm $form
     * @return Fieldset
     */
    private function addFieldsetToForm($form)
    {
        return $form->addFieldset($this->getFormFieldsetName(), []);
    }

    /**
     * Prepare field set for form
     *
     * @param Fieldset $fieldset
     */
    private function prepareFieldset($fieldset)
    {
        $conditionRule = $this->formDataProvider->getConditionRule();
        $fieldset->setRenderer($this->getFieldsetRenderer());
        $conditionRule->setJsFormObject($this->getJsFormObjectName());
        $this->addFieldsToFieldset($fieldset, $conditionRule);
        $this->setFormNameToRuleConditions($conditionRule->getConditions());
    }

    /**
     * Retrieve renderer for form fieldset
     *
     * @return RendererInterface
     */
    private function getFieldsetRenderer()
    {
        return $this->rendererFieldsetFactory->create()
            ->setTemplate($this->getFieldsetTemplate())
            ->setNewChildUrl(
                $this->urlBuilder->getUrl(
                    $this->getNewChildUrlRoute(),
                    [
                        'form'   => $this->getJsFormObjectName(),
                        'prefix' => ConditionRule::CONDITIONS_PREFIX,
                        'rule'   => base64_encode(ConditionRule::class),
                        'form_namespace' => $this->getFormNamespace()
                    ]
                )
            );
    }

    /**
     * Add necessary fields to form fieldset
     *
     * @param Fieldset $fieldset
     * @param AbstractModel $conditionRule
     */
    private function addFieldsToFieldset($fieldset, $conditionRule)
    {
        $fieldset
            ->setLegend(__('For all products matching the conditions below'))
            ->addField(
                $this->getConditionFieldName(),
                'text',
                [
                    'name' => $this->getConditionFieldName(),
                    'label' => __('Conditions'),
                    'title' => __('Conditions'),
                    'data-form-part' => $this->getFormNamespace()
                ]
            )
            ->setRule($conditionRule)
            ->setRenderer($this->conditionsBlock);
    }

    /**
     * Handles addition of form name to combine condition and its child conditions
     *
     * @param ConditionCombine $conditions
     * @return void
     */
    private function setFormNameToRuleConditions($conditions)
    {
        $conditions->setFormName($this->getFormNamespace());
        $conditions->setJsFormObject($this->getJsFormObjectName());
        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $conditionsArrayItem) {
                $this->setFormNameToRuleConditions($conditionsArrayItem);
            }
        }
    }

    /**
     * Retrieve fieldset template
     *
     * @return string
     */
    private function getFieldsetTemplate()
    {
        return 'Magento_CatalogRule::promo/fieldset.phtml';
    }
}

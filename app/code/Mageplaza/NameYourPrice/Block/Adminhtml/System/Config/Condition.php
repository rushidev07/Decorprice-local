<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_NameYourPrice
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\NameYourPrice\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\CatalogRule\Model\RuleFactory;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Rule\Block\Conditions;
use Mageplaza\NameYourPrice\Helper\Data as HelperData;

/**
 * Class Condition
 * @package Mageplaza\NameYourPrice\Block\Adminhtml\System\Config
 */
class Condition extends Field
{
    /**
     * @var Fieldset
     */
    protected $_rendererFieldset;

    /**
     * @var FormFactory
     */
    protected $_formFactory;

    /**
     * @var Conditions
     */
    protected $_conditions;

    /**
     * @var RuleFactory
     */
    protected $_ruleFactory;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * Condition constructor.
     *
     * @param Context $context
     * @param RuleFactory $ruleFactory
     * @param FormFactory $formFactory
     * @param Fieldset $rendererFieldset
     * @param Conditions $conditions
     * @param HelperData $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        RuleFactory $ruleFactory,
        FormFactory $formFactory,
        Fieldset $rendererFieldset,
        Conditions $conditions,
        HelperData $helperData,
        array $data = []
    ) {
        $this->_ruleFactory = $ruleFactory;
        $this->_formFactory = $formFactory;
        $this->_rendererFieldset = $rendererFieldset;
        $this->_conditions = $conditions;
        $this->_helperData = $helperData;

        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     *
     * @return mixed|string
     * @throws LocalizedException
     * @SuppressWarnings(Unused)
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $rule = $this->_ruleFactory->create();
        /** @var Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        $form->setFieldNameSuffix('rule');
        $newChildUrl = $this->getUrl(
            'catalog_rule/promo_catalog/newConditionHtml/form/rule_conditions_fieldset',
            ['form_namespace' => 'catalog_rule_form']
        );

        $values = $this->_helperData->getConfigGeneral('condition');
        $rule->setData('conditions_serialized', $values);
        $renderer = $this->_rendererFieldset->setTemplate('Mageplaza_NameYourPrice::rule/conditions.phtml')
            ->setType('page')->setNewChildUrl($newChildUrl);
        $fieldset = $form->addFieldset('conditions_fieldset', [])->setRenderer($renderer);
        $fieldset->addField('conditions', 'text', [])->setRule($rule)->setRenderer($this->_conditions);

        $rule->getConditions()->setJsFormObject('rule_conditions_fieldset');
        $this->setConditionFormName($rule->getConditions(), 'rule_conditions_fieldset');

        return $fieldset->toHtml();
    }
}

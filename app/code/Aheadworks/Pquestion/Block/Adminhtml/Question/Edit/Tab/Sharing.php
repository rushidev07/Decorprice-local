<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Block\Adminhtml\Question\Edit\Tab;

use Aheadworks\Pquestion\Model\Source\Question\Sharing\Type;
use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Class Sharing
 *
 * @package Aheadworks\Pquestion\Block\Adminhtml\Question\Edit\Tab
 */
class Sharing extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    private $systemStore;

    /**
     * @var \Magento\CatalogWidget\Block\Product\Widget\Conditions
     */
    private $conditions;

    /**
     * @var \Magento\CatalogWidget\Model\Rule
     */
    private $ruleModel;

    /**
     * @var Type
     */
    private $sharingType;

    /**
     * Sharing constructor.
     *
     * @param \Magento\CatalogWidget\Block\Product\Widget\Conditions $conditions
     * @param \Magento\Store\Model\System\Store                      $systemStore
     * @param \Magento\Backend\Block\Template\Context                $context
     * @param \Magento\Framework\Registry                            $registry
     * @param \Magento\Framework\Data\FormFactory                    $formFactory
     * @param \Magento\CatalogWidget\Model\Rule                      $ruleModel
     * @param Type                                                   $sharingType
     * @param array                                                  $data
     */
    public function __construct(
        \Magento\CatalogWidget\Block\Product\Widget\Conditions $conditions,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\CatalogWidget\Model\Rule $ruleModel,
        Type $sharingType,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->conditions = $conditions;
        $this->ruleModel = $ruleModel;
        $this->sharingType = $sharingType;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form fields
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function initForm()
    {
        $questionModel = $this->_coreRegistry->registry('current_question');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('_info_');

        $fieldset = $form->addFieldset('sharing', ['legend' => __('Sharing Options')]);
        if ($this->_storeManager->isSingleStoreMode()) {
            if (is_array($questionModel->getShowInStoreIds())) {
                $questionModel->setShowInStoreIds(implode(',', $questionModel->getShowInStoreIds()));
            }
            $fieldset->addField(
                'show_in_store_ids',
                'hidden',
                [
                    'name' => 'data[show_in_store_ids][]'
                ]
            );
        } else {
            $fieldset->addField(
                'show_in_store_ids',
                'multiselect',
                [
                    'name'     => 'data[show_in_store_ids][]',
                    'label'    => __('Display at'),
                    'title'    => __('Display at'),
                    'required' => true,
                    'values'   => $this->systemStore->getStoreValuesForForm(false, true),
                ]
            );
        }

        $fieldset->addField(
            'sharing_type',
            'radios',
            [
                'label'  => __('Display for'),
                'name'   => 'data[sharing_type]',
                'values' => $this->getSharingOptionValues()
            ]
        );

        $this->ruleModel->getConditions()->setJsFormObject($form->getHtmlIdPrefix().'sharing');
        $fieldset->addField(
            'sharing_value_' . Type::SPECIFIED_PRODUCTS_VALUE,
            'text',
            [
                'label' => __('Select Product(s)'),
            ]
        )->setRule(
            $this->ruleModel
        )->setRenderer(
            $this->conditions
        );

        $fieldset->addField(
            'sharing_value_' . Type::ORIGINAL_PRODUCT_VALUE,
            'text',
            [
                'label' => __('Select Product'),
                'after_element_js' => $this->getAutocompleteScriptHtml()
            ]
        );
        $form->setValues($questionModel->getData());
        $form
            ->getElement(
                'sharing_value_' . Type::ORIGINAL_PRODUCT_VALUE
            )
            ->setValue($questionModel->getProduct()->getName())
        ;
        $this->setForm($form);
        return $this;
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Sharing Options');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Sharing Options');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Get sharing options array
     *
     * @return mixed
     */
    private function getSharingOptionValues()
    {
        $optionValues = $this->sharingType->toOptionValues();
        foreach ($optionValues as $key => $option) {
            if ($option['value'] == Type::SPECIFIED_PRODUCTS_VALUE) {
                $optionValues[$key]['label'] = __('Selected Products (please, specify)');
            }
        }
        return $optionValues;
    }

    /**
     * Get autocomplete initializing
     *
     * @return string
     */
    private function getAutocompleteScriptHtml()
    {
        $template =  <<<HTML
<input type="hidden" name="data[product_id]" id="aw-pq-sharing-product-autocomplete-value" value="{{value}}"/>
<script type="text/javascript">
require(["jquery","AWPquestion_Autocomplete"], function(){
    jQuery('#_info_sharing_value_1').autocomplete({
        serviceUrl: "{{serviceUrl}}",
        minChars: 3,
        deferRequestBy: 1500,
        onSelect: function(suggestion) {
            $('aw-pq-sharing-product-autocomplete-value').setValue(suggestion.data);
        },
        onSearchStart: function (query) {
            jQuery("#aw_pq-spinner").show();
        },
        onSearchComplete: function(query, suggestion) {
            jQuery("#aw_pq-spinner").hide();
        }
    });
});
</script>
    <div id="aw_pq-spinner" data-role="spinner" class="admin__form-loading-mask" style="display: none;">
        <div class="spinner">
            <span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span>
        </div>
    </div>
HTML;
        $questionModel = $this->_coreRegistry->registry('current_question');
        $value = $questionModel->getData('product_id');
        $template = str_replace(
            "{{serviceUrl}}",
            $this->getUrl('productquestion/product/autocomplete'),
            $template
        );
        $template = str_replace("{{value}}", $value, $template);
        return $template;
    }
}

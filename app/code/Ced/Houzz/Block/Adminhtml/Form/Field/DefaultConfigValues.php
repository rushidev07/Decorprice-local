<?php

namespace Ced\Houzz\Block\Adminhtml\Form\Field;

class DefaultConfigValues extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var
     */
    protected $_magentoAttr;

    /**
     * Retrieve group column renderer
     *
     * @return shipping
     */
    protected function _getMagentoAttributeCodeRenderer()
    {
        if (!$this->_magentoAttr) {
            $this->_magentoAttr = $this->getLayout()->createBlock(
            //'Magento\CatalogInventory\Block\Adminhtml\Form\Field\Customergroup',
                'Ced\Houzz\Block\Adminhtml\Form\Field\HouzzAttributes',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_magentoAttr->setClass('shipping_method_select');
        }
        return $this->_magentoAttr;
    }

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'attribute_code',
            ['label' => __('Houzz Attribute'), 'renderer' => $this->_getMagentoAttributeCodeRenderer()]
        );
        $this->addColumn('default_value', ['label' => __('Default Value')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Rule');
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getMagentoAttributeCodeRenderer()->calcOptionHash($row->getData('attribute_code'))] =
            'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );


    }
}

<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    \Unirgy\RapidFlowMsi
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\RapidFlowMsi\Block\Adminhtml\Profile;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\Form as DataForm;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Unirgy\RapidFlow\Helper\Data as HelperData;
use Unirgy\RapidFlow\Model\Source;

class ImportOptions
    extends Generic
{
    /**
     * @var HelperData
     */
    protected $_helper;

    /**
     * @var Source
     */
    protected $_source;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        HelperData $rapidFlowHelper,
        Source $source,
        array $data = []
    ) {

        $this->_helper = $rapidFlowHelper;
        $this->_source = $source;
        parent::__construct($context, $registry, $formFactory, $data);
    }
    public function _prepareForm()
    {
        $source = $this->_source;

        $profile = $this->_coreRegistry->registry('profile_data');

        $form = $this->_formFactory->create();
        $this->setForm($form);

        $fieldset = $form->addFieldset('import_options_form', ['legend' => __('Import Options')]);

        /*
        $fieldset->addField('store_ids', 'multiselect', [
            'label' => __('Limit Stores to Import'),
            'name' => 'options[store_ids]',
            'values' => $source->setPath('stores')->toOptionArray(),
            'value' => $profile->getData('options/store_ids'),
            'note' => __('wherever applicable'),
        ]);
        */

        $fieldset->addField('import_row_types', 'multiselect', [
            'label' => __('Limit Row Types to Import'),
            'name' => 'options[row_types]',
            'values' => $source->setDataType($profile->getDataType())->setStripFromLabel('/^Catalog Product/')
                ->setPath('row_type')->toOptionArray(),
            'value' => $profile->getData('options/row_types'),
        ]);

        $fieldset->addField('import_reindex_type', 'select', [
            'label' => __('Reindex type'),
            'name' => 'options[import][reindex_type]',
            'values' => $source->setPath('import_reindex_type')->toOptionArray(),
            'value' => $profile->getData('options/import/reindex_type'),
        ]);

        return parent::_prepareForm();
    }
}

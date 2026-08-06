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
 * @package    \Unirgy\RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\RapidFlowPro\Block\Adminhtml\Profile\Enterprise;

use Magento\Backend\Block\Widget\Form;
use Magento\Framework\Data\Form as DataForm;
use Unirgy\RapidFlow\Model\Source;
use Unirgy\RapidFlowPro\Block\Adminhtml\Profile\BaseForm;

class ExportOptions
    extends BaseForm
{
    public function _prepareForm()
    {
        $hlp = $this->_helper;
        $source = $this->_source;

        $profile = $this->_coreRegistry->registry('profile_data');

        $form = $this->_formFactory->create();
        $this->setForm($form);

        $fieldset = $form->addFieldset('export_options_form', ['legend' => __('Export Options')]);

        $fieldset->addField('export_row_types', 'multiselect', [
            'label' => __('Row Types'),
            'name' => 'options[row_types]',
            'values' => $source->setDataType($profile->getDataType())->setStripFromLabel('/^Catalog Product/')
                ->setPath('row_type')->toOptionArray(),
            'value' => $profile->getData('options/row_types'),
        ]);

        return parent::_prepareForm();
    }
}

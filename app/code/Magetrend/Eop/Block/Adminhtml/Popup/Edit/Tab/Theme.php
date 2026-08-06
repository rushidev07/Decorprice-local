<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.3 or later
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */

namespace Magetrend\Eop\Block\Adminhtml\Popup\Edit\Tab;

use \Magento\Backend\Block\Widget\Form\Generic;
use \Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Bckend Popup Edit Popup Theme Tab Block
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Theme extends Generic implements TabInterface
{

    /**
     * @inheritdoc
     */
    //@codingStandardsIgnoreLine
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('eop_popup');

        $contentType = $this->_request->getParam('content_type');
        if (empty($contentType)) {
            $contentType = $model->getContentType();
        }

        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Content & Colors')]);
        $theme = $model->getTheme();
        if (empty($theme)) {
            $theme = 'basic';
        }
        switch ($theme) {
            case 'basic':
                if ($contentType == \Magetrend\Eop\Model\Popup::TYPE_NEWSLETTER_SUBSCRIPTION) {
                    $this->addTextLineField($fieldset, 1, __('DO YOU WANT'));
                    $this->addTextLineField($fieldset, 2, __('20% OFF'));
                    $this->addTextLineField($fieldset, 3, __('from your order?'));
                    $this->addTextLineField($fieldset, 4, __('Subscribe our newsletter and get free voucher.'));
                    $this->addColorField($fieldset, 1, '4A5EC1');
                    $this->addColorField($fieldset, 2, 'FFFFFF');
                    $this->addColorField($fieldset, 3, '384DB4');
                }

                if ($contentType == \Magetrend\Eop\Model\Popup::TYPE_YES_NO_BUTTONS) {
                    $this->addTextLineField($fieldset, 1, __('DO YOU WANT'));
                    $this->addTextLineField($fieldset, 2, __('20% OFF'));
                    $this->addTextLineField($fieldset, 3, __('from your order?'));
                    $this->addButtonsInputFields($fieldset);
                    $this->addColorField($fieldset, 1, '4A5EC1');
                    $this->addColorField($fieldset, 2, 'FFFFFF');
                    $this->addColorField($fieldset, 3, '384DB4');
                }

                if ($contentType == \Magetrend\Eop\Model\Popup::TYPE_CONTACT_FORM) {
                    $this->addTextLineField($fieldset, 1, __('Wait'));
                    $this->addTextLineField($fieldset, 2, __('tell us what you are looking for?'));
                    $this->addTextLineField(
                        $fieldset,
                        3,
                        __('If there is any missing feature, we can implement it for FREE!')
                    );
                    $this->addColorField($fieldset, 1, 'E7402F');
                    $this->addColorField($fieldset, 2, 'FFFFFF');
                    $this->addColorField($fieldset, 3, '4B4C4D');
                }
                break;
        }

        if ($model->getId()) {
            $form->setValues($model->getData());
        }
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * It will add text field to the form
     *
     * @param $fieldset
     * @param $nr
     * @param $value
     */
    public function addTextLineField($fieldset, $nr, $value)
    {
        $fieldset->addField(
            'text_'.$nr,
            'text',
            [
                'name' => 'text_'.$nr,
                'label' => __('Text Line').' '.$nr,
                'title' => __('Text Line').' '.$nr,
                'required' => false,
                'disabled' => false,
                'value' => $value
            ]
        );
    }

    /**
     * It will add color field to the form
     *
     * @param $fieldset
     * @param $nr
     * @param $value
     */
    public function addColorField($fieldset, $nr, $value)
    {
        $fieldset->addField(
            'color_'.$nr,
            'text',
            [
                'name' => 'color_'. $nr,
                'label' => __('Color').' '.$nr,
                'title' =>  __('Color').' '.$nr,
                'required' => false,
                'disabled' => false,
                'class' => 'color-picker',
                'value' => $value
            ]
        );
    }

    /**
     * It will add buttons text field to the form
     *
     * @param $fieldset
     */
    public function addButtonsInputFields($fieldset)
    {
        $fieldset->addField(
            'text_5',
            'text',
            [
                'name' => 'text_5',
                'label' => __('Button YES Text 1'),
                'title' => __('Button YES Text 1'),
                'required' => false,
                'disabled' => false,
                'value' => __('YES')
            ]
        );

        $fieldset->addField(
            'text_6',
            'text',
            [
                'name' => 'text_6',
                'label' => __('Button YES Text 2'),
                'title' => __('Button YES Text 2'),
                'required' => false,
                'disabled' => false,
                'value' => __('I want!')
            ]
        );

        $fieldset->addField(
            'text_7',
            'text',
            [
                'name' => 'text_7',
                'label' => __('Button NO Text 1'),
                'title' => __('Button NO Text 1'),
                'required' => false,
                'disabled' => false,
                'value' => __('NO')
            ]
        );

        $fieldset->addField(
            'text_8',
            'text',
            [
                'name' => 'text_8',
                'label' => __('Button NO Text 2'),
                'title' => __('Button NO Text 2'),
                'required' => false,
                'disabled' => false,
                'value' => __('Thank you...')
            ]
        );
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Content & Colors');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Content & Colors');
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
}

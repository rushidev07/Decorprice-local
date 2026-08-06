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
 * Bckend Popup Edit Social Links Tab Block
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Link extends Generic implements TabInterface
{
    /**
     * @inheritdoc
     */
    //@codingStandardsIgnoreLine
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('eop_popup');

        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Social Links')]);

        $fieldset->addField(
            'link_facebook',
            'text',
            [
                'name' => 'link_facebook',
                'label' => __('Facebook'),
                'title' => __('Facebook'),
                'required' => false,
                'disabled' => false
            ]
        );

        $fieldset->addField(
            'link_twitter',
            'text',
            [
                'name' => 'link_twitter',
                'label' => __('Twitter'),
                'title' => __('Twitter'),
                'required' => false,
                'disabled' => false
            ]
        );

        $fieldset->addField(
            'link_pinterest',
            'text',
            [
                'name' => 'link_pinterest',
                'label' => __('Pinterest'),
                'title' => __('Pinterest'),
                'required' => false,
                'disabled' => false
            ]
        );

        $fieldset->addField(
            'link_gplus',
            'text',
            [
                'name' => 'link_gplus',
                'label' => __('Google Plus'),
                'title' => __('Google Plus'),
                'required' => false,
                'disabled' => false
            ]
        );

        $fieldset->addField(
            'link_instagram',
            'text',
            [
                'name' => 'link_instagram',
                'label' => __('Instagram'),
                'title' => __('Instagram'),
                'required' => false,
                'disabled' => false
            ]
        );

        $fieldset->addField(
            'link_tumblr',
            'text',
            [
                'name' => 'link_tumblr',
                'label' => __('Tumblr'),
                'title' => __('Tumblr'),
                'required' => false,
                'disabled' => false
            ]
        );

        $fieldset->addField(
            'link_linkedin',
            'text',
            [
                'name' => 'link_linkedin',
                'label' => __('Linked In'),
                'title' => __('Linked In'),
                'required' => false,
                'disabled' => false
            ]
        );

        $fieldset->addField(
            'link_youtube',
            'text',
            [
                'name' => 'link_youtube',
                'label' => __('Youtube'),
                'title' => __('Youtube'),
                'required' => false,
                'disabled' => false
            ]
        );

        if ($model->getId()) {
            $form->setValues($model->getData());
        }
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Social Links');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Social Links');
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }
}

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
 * Bckend Popup Edit Contact Form Tab Block
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Contact extends Generic implements TabInterface
{
    /**
     * @var \Magetrend\Eop\Model\Config\Source\EmailTemplate
     */
    public $emailTemplateList;

    /**
     * Contact constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magetrend\Eop\Model\Config\Source\EmailTemplate $emailTemplateList
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magetrend\Eop\Model\Config\Source\EmailTemplate $emailTemplateList,
        array $data = []
    ) {
        $this->emailTemplateList = $emailTemplateList;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    //@codingStandardsIgnoreLine
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('eop_popup');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('page_');
        $fieldset = $form->addFieldset('contact_fieldset', ['legend' => __('Contact Settings')]);

        $fieldset->addField(
            'email_template',
            'select',
            [
                'name' => 'email_template',
                'label' =>  __('Email Template'),
                'title' =>  __('Email Template'),
                'value' => 1,
                'options' => $this->emailTemplateList->toArray(),
            ]
        );

        $fieldset->addField(
            'sender_name',
            'text',
            [
                'name' => 'sender_name',
                'label' =>  __('Sender Name'),
                'title' =>  __('Sender Name'),
                'value' => $this->_scopeConfig->getValue(\Magento\Contact\Controller\Index::XML_PATH_EMAIL_SENDER),
                'required' => true,
                'disabled' => false
            ]
        );

        $fieldset->addField(
            'send_to',
            'text',
            [
                'name' => 'send_to',
                'label' =>  __('Send Emails To'),
                'title' =>  __('Send Emails To'),
                'value' => $this->_scopeConfig->getValue(\Magento\Contact\Controller\Index::XML_PATH_EMAIL_RECIPIENT),
                'required' => true,
                'disabled' => false
            ]
        );

        $fieldset->addField(
            'subject',
            'text',
            [
                'name' => 'subject',
                'label' =>  __('Default Subject'),
                'title' =>  __('Default Subject'),
                'required' => true,
                'disabled' => false,
                'value' => __('Contact Form')

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
        return __('General Settings');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('General Settings');
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

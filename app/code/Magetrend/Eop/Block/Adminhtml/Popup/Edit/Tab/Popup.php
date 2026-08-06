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
 * Bckend Popup Edit Popup Settings Tab Block
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Popup extends Generic implements TabInterface
{
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    public $yesNo;

    /**
     * @var \Magetrend\Eop\Model\Config\Source\Theme
     */
    public $theme;

    /**
     * Popup constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Config\Model\Config\Source\Yesno $yesNo
     * @param \Magetrend\Eop\Model\Config\Source\Theme $theme
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\Yesno $yesNo,
        \Magetrend\Eop\Model\Config\Source\Theme $theme,
        array $data = []
    ) {
        $this->theme = $theme;
        $this->yesNo = $yesNo;
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

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Popup Settings')]);

        $contentType = $this->_request->getParam('content_type');
        if (empty($contentType)) {
            $model = $this->_coreRegistry->registry('eop_popup');
            $contentType = $model->getContentType();
        }

        if ($contentType == \Magetrend\Eop\Model\Popup::TYPE_NEWSLETTER_SUBSCRIPTION ||
            $contentType == \Magetrend\Eop\Model\Popup::TYPE_CONTACT_FORM ||
            $contentType == \Magetrend\Eop\Model\Popup::TYPE_YES_NO_BUTTONS
        ) {
            $fieldset->addField(
                'theme',
                'select',
                [
                    'name' => 'theme',
                    'label' =>  __('Theme'),
                    'title' =>  __('Theme'),
                    'value' => 1,
                    'options' => $this->theme->toArray(),
                ]
            );
        }

        $fieldset->addField(
            'mobile_auto_position',
            'select',
            [
                'name' => 'mobile_auto_position',
                'label' => __('Disable Auto Position on Mobile Devices'),
                'title' => __('Disable Auto Position on Mobile Devices'),
                'required' => false,
                'disabled' => false,
                'value' => 0,
                'options' => $this->yesNo->toArray(),
                'note' => __('This option should be on if your store design is not response.')
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
        return __('Popup Settings');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Popup Settings');
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
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    public function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}

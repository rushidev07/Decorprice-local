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

namespace Magetrend\Eop\Block\Adminhtml\Campaign\Edit\Tab;

use \Magento\Backend\Block\Widget\Form\Generic;
use \Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Campaign edit popup tab block class
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
     * @var \Magetrend\Eop\Model\Config\Source\Popup
     */
    public $popup;

    /**
     * @var \Magetrend\Eop\Model\Config\Source\Event
     */
    public $eventSource;

    /**
     * @var \Magetrend\Eop\Model\Config\Source\Device
     */
    public $deviceSource;

    /**
     * Popup constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Config\Model\Config\Source\Yesno $yesNo
     * @param \Magetrend\Eop\Model\Config\Source\Popup $popup
     * @param \Magetrend\Eop\Model\Config\Source\Event $event
     * @param \Magetrend\Eop\Model\Config\Source\Device $device
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\Yesno $yesNo,
        \Magetrend\Eop\Model\Config\Source\Popup $popup,
        \Magetrend\Eop\Model\Config\Source\Event $event,
        \Magetrend\Eop\Model\Config\Source\Device $device,
        array $data = []
    ) {
        $this->popup = $popup;
        $this->yesNo = $yesNo;
        $this->eventSource = $event;
        $this->deviceSource = $device;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    //@codingStandardsIgnoreLine
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('eop_campaign');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Popup Settings')]);

        $fieldset->addField(
            'popup_id',
            'select',
            [
                'name' => 'popup_id',
                'label' =>  __('Popup'),
                'title' =>  __('Popup'),
                'value' => '',
                'required' => true,
                'options' => $this->popup->toArray(),
            ]
        );

        $fieldset->addField(
            'show_device',
            'select',
            [
                'name' => 'show_device',
                'label' => __('Visible on Devices'),
                'title' => __('Visible on Devices'),
                'required' => true,
                'disabled' => false,
                'value' => '0',
                'options' => $this->deviceSource->toArray(),

            ]
        );

        $fieldset->addField(
            'show_event',
            'select',
            [
                'name' => 'show_event',
                'label' => __('Show on'),
                'title' => __('Show on'),
                'required' => true,
                'disabled' => false,
                'value' => '0',
                'options' => $this->eventSource->toArray(),
            ]
        );

        $fieldset->addField(
            'delay_time',
            'text',
            [
                'name' => 'delay_time',
                'label' => __('Trigger Delay Time'),
                'title' => __('Trigger Delay Time'),
                'required' => false,
                'disabled' => false,
                'note' => __('Delay time is only for "Show on Page Load" trigger'),
                'value' => '0'
            ]
        );

        $fieldset->addField(
            'layer_close',
            'select',
            [
                'name' => 'layer_close',
                'label' =>  __('Close after Click on Popup Layer'),
                'title' =>  __('Close after Click on Popup Layer'),
                'value' => 1,
                'options' => $this->yesNo->toArray(),
            ]
        );

        $fieldset->addField(
            'cookie_lifetime',
            'text',
            [
                'name' => 'cookie_lifetime',
                'label' => __('Show again after X Days'),
                'title' => __('Show again after X days'),
                'required' => false,
                'disabled' => false,
                'value' => '365'
            ]
        );

        $fieldset->addField(
            'show_in_last_tab',
            'select',
            [
                'name' => 'show_in_last_tab',
                'label' => __('Show in the Last Tab'),
                'title' => __('Show in the Last Tab'),
                'required' => false,
                'disabled' => false,
                'value' => '0',
                'options' => $this->yesNo->toArray(),
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

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
 * Campaign edit general tab block class
 *
 * @category MageTrend 
 * @package  Magetend/Eop 
 * @author   Edvinas Stulpinas <edwin@magetrend.com> 
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0) 
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class General extends Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    public $systemStore;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    public $yesNo;

    /**
     * @var \Magetrend\Eop\Model\Config\Source\Page
     */
    public $pageSource;

    /**
     * General constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Config\Model\Config\Source\Yesno $yesno
     * @param \Magetrend\Eop\Model\Config\Source\Page $pageSource
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Config\Model\Config\Source\Yesno $yesno,
        \Magetrend\Eop\Model\Config\Source\Page $pageSource,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->yesNo = $yesno;
        $this->pageSource = $pageSource;
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
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General Settings')]);

        if ($model->getId()) {
            $fieldset->addField(
                'campaign_id',
                'hidden',
                ['name' => 'campaign_id', 'value' => $model->getId()]
            );
        }
       
        $fieldset->addField(
            'is_active',
            'select',
            [
                'name' => 'is_active',
                'label' =>  __('Is Active'),
                'title' =>  __('Is Active'),
                'value' => 0,
                'options' => $this->yesNo->toArray(),
            ]
        );

        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Campaign Name'),
                'title' => __('Campaign Name'),
                'required' => true,
                'disabled' => false
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormat(
            \IntlDateFormatter::SHORT
        );

        $fieldset->addField(
            'start_date',
            'date',
            [
                'name' => 'start_date',
                'label' => __('Start Date'),
                'title' => __('Start Date'),
                'required' => false,
                'disabled' => false,
                'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                'date_format' => $dateFormat
            ]
        );

        $fieldset->addField(
            'end_date',
            'date',
            [
                'name' => 'end_date',
                'label' => __('End Date'),
                'title' => __('End Date'),
                'required' => false,
                'disabled' => false,
                'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                'date_format' => $dateFormat
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $fieldset->addField(
                'store_ids',
                'multiselect',
                [
                    'name' => 'store_ids[]',
                    'label' => __('Show in Stores'),
                    'title' => __('Show in Stores'),
                    'required' => true,
                    'values' => $this->systemStore->getStoreValuesForForm(false, true),
                    'value' => 0,
                ]
            );
        }

        $fieldset->addField(
            'page_ids',
            'multiselect',
            [
                'name' => 'page_ids[]',
                'label' => __('Show in Pages'),
                'title' => __('Show in Pages'),
                'required' => true,
                'values' => $this->pageSource->toOptionArray(),
                'value' => 'all',
            ]
        );

        $fieldset->addField(
            'params',
            'textarea',
            [
                'name' => 'params',
                'label' => __('Required Params'),
                'title' => __('Required Params'),
                'note' => __(
                    'Show popup if match flowing params. E.g.
                     utm_campaign=magetrend&utm_medium=default. Use "&" for separation.'
                ),
                'required' => false,
                'disabled' => false
            ]
        );

        if ($model->getId()) {
            $data = $model->getData();
            $data['page_ids'] = $model->getPageIdsAsArray();
            $data['store_ids'] = $model->getStoreIdsAsArray();
            $data['campaign_id'] = $model->getId();
            $form->setValues($data);
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

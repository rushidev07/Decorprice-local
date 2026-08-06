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
namespace Magetrend\Eop\Block\Adminhtml\Campaign\Edit;

/**
 * Campaign edit tabs block class
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    //@codingStandardsIgnoreLine
    protected function _construct()
    {
        parent::_construct();
        $this->setId('campaign_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Campaign Information'));
    }

    /**
     * @inheritdoc
     */
    //@codingStandardsIgnoreLine
    protected function _beforeToHtml()
    {
        $this->addTab(
            'general_section',
            [
                'label' => __('General Settings'),
                'title' => __('General Settings'),
                'active' => true,
                'content' => $this->getLayout()->createBlock(
                    'Magetrend\Eop\Block\Adminhtml\Campaign\Edit\Tab\General'
                )->toHtml()
            ]
        );

        $this->addTab(
            'popup_section',
            [
                'label' => __('Popup Settings'),
                'title' => __('Popup Settings'),
                'active' => false,
                'content' => $this->getLayout()->createBlock(
                    'Magetrend\Eop\Block\Adminhtml\Campaign\Edit\Tab\Popup'
                )->toHtml()
            ]
        );

        $this->addTab(
            'condition_section',
            [
                'label' => __('Conditions'),
                'title' => __('Conditions'),
                'content' => $this->getLayout()->createBlock(
                    'Magetrend\Eop\Block\Adminhtml\Campaign\Edit\Tab\Conditions'
                )->toHtml()
            ]
        );

        return parent::_beforeToHtml();
    }
}

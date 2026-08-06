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

namespace Magetrend\Eop\Block\Adminhtml\Popup\Edit;

/**
 * Bckend Popup Edit Tabs Block
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    public $coreRegistry;

    /**
     * Tabs constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    // @codingStandardsIgnoreStart
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        // @codingStandardsIgnoreEnd
        $this->coreRegistry = $registry;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    /**
     * @return void
     */
    //@codingStandardsIgnoreLine
    protected function _construct()
    {
        parent::_construct();
        $this->setId('popup_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Popup Information'));
    }

    /**
     * @inheritdoc
     */
    //@codingStandardsIgnoreLine
    protected function _beforeToHtml()
    {
        $contentType = $this->_request->getParam('content_type');
        $id = $this->_request->getParam('id');
        $isNew = !$id?true:false;

        if (empty($contentType)) {
            $model = $this->coreRegistry->registry('eop_popup');
            $contentType = $model->getContentType();
        }

        if ($contentType == \Magetrend\Eop\Model\Popup::TYPE_NEWSLETTER_SUBSCRIPTION) {
            $this->addGeneralTab();
            $this->addPopupTab();
            $this->addCouponTab();
            if (!$isNew) {
                $this->addFieldTab();
            }
            $this->addThemeTab();
        }

        if ($contentType == \Magetrend\Eop\Model\Popup::TYPE_STATIC_BLOCK) {
            $this->addGeneralTab();
            $this->addPopupTab();
        }

        if ($contentType == \Magetrend\Eop\Model\Popup::TYPE_YES_NO_BUTTONS) {
            $this->addGeneralTab();
            $this->addPopupTab();
            $this->addThemeTab();
            $this->addCouponTab();
        }

        if ($contentType == \Magetrend\Eop\Model\Popup::TYPE_CONTACT_FORM) {
            $this->addGeneralTab();
            $this->addPopupTab();
            $this->addContactTab();
            if (!$isNew) {
                $this->addFieldTab();
            }
            $this->addThemeTab();
        }

        return parent::_beforeToHtml();
    }

    public function addGeneralTab()
    {
        $this->addTab(
            'general_section',
            [
                'label' => __('General Settings'),
                'title' => __('General Settings'),
                'active' => true,
                'content' => $this->getLayout()->createBlock(
                    'Magetrend\Eop\Block\Adminhtml\Popup\Edit\Tab\General'
                )->toHtml()
            ]
        );
    }

    public function addPopupTab()
    {
        $this->addTab(
            'popup_section',
            [
                'label' => __('Popup Settings'),
                'title' => __('Popup Settings'),
                'content' => $this->getLayout()->createBlock(
                    'Magetrend\Eop\Block\Adminhtml\Popup\Edit\Tab\Popup'
                )->toHtml()
            ]
        );
    }

    public function addFieldTab()
    {

        $this->addTab(
            'field_section',
            [
                'label' => __('Additional Fields'),
                'title' => __('Additional Fields'),
                'content' => $this->getLayout()->createBlock(
                    'Magetrend\Eop\Block\Adminhtml\Popup\Edit\Tab\Field'
                )->toHtml()
            ]
        );
    }

    public function addThemeTab()
    {
        $this->addTab(
            'theme_section',
            [
                'label' => __('Theme'),
                'title' => __('Theme'),
                'content' => $this->getLayout()->createBlock(
                    'Magetrend\Eop\Block\Adminhtml\Popup\Edit\Tab\Theme'
                )->toHtml()
            ]
        );
    }

    public function addLinkTab()
    {
        $this->addTab(
            'link_section',
            [
                'label' => __('Social Links'),
                'title' => __('Social Links'),
                'content' => $this->getLayout()->createBlock(
                    'Magetrend\Eop\Block\Adminhtml\Popup\Edit\Tab\Link'
                )->toHtml()
            ]
        );
    }

    public function addCouponTab()
    {
        $this->addTab(
            'coupon_section',
            [
                'label' => __('Discount Coupon'),
                'title' => __('Discount Coupon'),
                'content' => $this->getLayout()->createBlock(
                    'Magetrend\Eop\Block\Adminhtml\Popup\Edit\Tab\Coupon'
                )->toHtml()
            ]
        );
    }

    public function addCmsTab()
    {
        $this->addTab(
            'cms_section',
            [
                'label' => __('Static Block Settings'),
                'title' => __('Static Block Settings'),
                'content' => $this->getLayout()->createBlock(
                    'Magetrend\Eop\Block\Adminhtml\Popup\Edit\Tab\Cms'
                )->toHtml()
            ]
        );
    }

    public function addContactTab()
    {
        $this->addTab(
            'contact_section',
            [
                'label' => __('Contact Form Settings'),
                'title' => __('Contact Form Settings'),
                'content' => $this->getLayout()->createBlock(
                    'Magetrend\Eop\Block\Adminhtml\Popup\Edit\Tab\Contact'
                )->toHtml()
            ]
        );
    }
}

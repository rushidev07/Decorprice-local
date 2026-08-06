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

namespace Magetrend\Eop\Block;

use Magetrend\Eop\Helper;

/**
 * Popup block class
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Popup extends \Magento\Framework\View\Element\Template
{

    /**
     * General module helper
     *
     * @var Helper\Data|null
     */
    public $helper = null;

    /**
     * Current Popup
     * @var null|\Magetrend\Eop\Model\Popup
     */
    public $popup = null;

    /**
     * Current campaign
     * @var null|\Magetrend\Eop\Model\Campaign
     */
    public $campaign = null;

    /**
     * @var \Magetrend\Eop\Model\Campaign
     */
    public $campaignModel;

    /**
     * Popup constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Helper\Data $helper
     * @param \Magetrend\Eop\Model\Campaign $campaign
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magetrend\Eop\Helper\Data $helper,
        \Magetrend\Eop\Model\Campaign $campaign,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->campaignModel = $campaign;
        parent::__construct($context, $data);
    }

    /**
     * It returns config for js script in json format
     * @return string
     */
    public function getConfigJs()
    {
        return json_encode($this->getConfig());
    }

    /**
     * It returns configuration for js script
     * @return array
     */
    public function getConfig()
    {
        $campaign = $this->getCampaign();

        $popup = $this->getPopup();
        $config = [
            'actionUrl'             => $this->getActionUrl(),
            'translate'             => $this->helper->getTranslation(),
            'layerClose'            => $campaign->getLayerClose()?true:false,
            'showInLast'            => $campaign->getShowInLastTab(),
            'cookieName'            => $campaign->getCookieName(),
            'showOnLoadCookieName'  => $campaign->getCookieName().'_onload',
            'cookieLifeTime'        => $this->getCookieLifeTime(),
            'campaignId'            => $this->getCampaign()->getId(),
            'pageUrl'               => $this->helper->getCurrentPageUrl(),
            'contentType'           => $popup->getContentType(),
            'showDevice'            => $campaign->getShowDevice(),
            'showEvent'             => $campaign->getShowEvent(),
            'delayTime'             => $campaign->getDelayTime(),
            'rest'                  => $this->getUrl('rest/V1/eop/'.$this->getCampaign()->getId().'/canShow'),
            'pageId'                => $this->helper->getCurrentPageId(),
            'validateConditions'    => $this->getCampaign()->hasConditions()?true:false,
        ];

        if (empty($config['showDevice'])) {
            $config['showDevice'] = \Magetrend\Eop\Model\Config\Source\Device::DEVICE_ALL;
        }

        if (empty($config['showEvent'])) {
            $config['showEvent'] = \Magetrend\Eop\Model\Config\Source\Event::TRIGGER_ALL;
        }

        return $config;
    }

    /**
     * Is popup available to show
     * @return bool
     */
    public function isActivePopup()
    {
        //is it active in system configuration
        if (!$this->helper->isActive()) {
            return false;
        }

        //is some campaigns available
        if (!$this->getPopup()) {
            return false;
        }

        return true;
    }

    /**
     * It returns current campaign popup object
     * @return \Magetrend\Eop\Model\Popup
     */
    public function getPopup()
    {
        if ($this->popup == null) {
            $campaign = $this->getCampaign();
            if ($campaign) {
                $this->popup = $campaign->getPopup();
            }
        }
        return $this->popup;
    }

    /**
     * It returns current campaign object
     * @return \Magetrend\Eop\Model\Campaign
     */
    public function getCampaign()
    {
        if ($this->campaign == null) {
            $this->campaign = $this->campaignModel->getCurrentCampaign();
        }
        return $this->campaign;
    }

    /**
     * It returns static cms block html code
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStaticBlockHtml()
    {
        $id = $this->getPopup()->getStaticBlockId();
        if (!is_numeric($id)) {
            $this->helper->log('Static block is not assigned to popup. Popup::getStaticBlockHtml');
            return '';
        }

        $html = $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId($id)->toHtml();
        return $html;
    }

    /**
     * Returns popup content html
     *
     * @return string
     */
    public function getPopupContentHtml()
    {
        return $this->getChildHtml($this->getPopup()->getTheme());
    }

    /**
     * Returns cookie lifetime
     *
     * @return float|int
     */
    public function getCookieLifeTime()
    {
        $campaign = $this->getCampaign();
        if (!is_numeric($campaign->getCookieLifetime())) {
            return 365;
        }
        return $campaign->getCookieLifetime();
    }

    /**
     * Returns cookie name
     *
     * @return string
     */
    public function getCookieName()
    {
        $cookiePrefix = $this->helper->getCookiePrefix();
        return $cookiePrefix;
    }

    /**
     * It returns ajax request url
     *
     * @return mixed
     */
    public function getActionUrl()
    {
        $popup = $this->getPopup();
        $urlPath = '';
        switch ($popup->getContentType()) {
            case \Magetrend\Eop\Model\Popup::TYPE_NEWSLETTER_SUBSCRIPTION:
                $urlPath = 'eop/popup/subscribe';
                break;
            case \Magetrend\Eop\Model\Popup::TYPE_CONTACT_FORM:
                $urlPath = 'eop/popup/contact';
                break;
            case \Magetrend\Eop\Model\Popup::TYPE_YES_NO_BUTTONS:
                $urlPath = 'eop/popup/coupon';
                break;
        }

        if (empty($urlPath)) {
            return '';
        }
        return str_replace(['http:', 'https:'], '', $this->getUrl($urlPath));
    }
}

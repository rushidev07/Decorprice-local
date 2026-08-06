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

namespace Magetrend\Eop\Helper;

/**
 * Module general helper class
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_EXIT_OFFER_GENERAL_IS_ACTIVE = 'eop/general/is_active';

    const XML_PATH_TRANSLATE = 'eop/translate';

    const XML_PATH_COOKIE_NAME = 'eop/general/cookie_name';

    const XML_PATH_GOD_MODE = 'eop/general/dev';

    const XML_PATH_EOP_DEFAULT_IS_ACTIVE = 'eop/default/is_active';

    const XML_PATH_EOP_DEFAULT_CAMPAIGN = 'eop/default/campaign';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;
    
    /**
     * @var \Magento\Catalog\Helper\Data
     */
    public $catalogHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    public $cookieManager;

    /**
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry;

    /**
     * @var \Magento\Cms\Model\Page
     */
    public $cmsPage;

    /**
     * @var \Magetrend\Eop\Model\CampaignFactory
     */
    public $campaignFactory;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManagerInterface
     * @param \Magento\Catalog\Helper\Data $catalogHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Cms\Model\Page $page
     * @param \Magetrend\Eop\Model\CampaignFactory $campaignFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManagerInterface,
        \Magento\Catalog\Helper\Data $catalogHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Cms\Model\Page $page,
        \Magetrend\Eop\Model\CampaignFactory $campaignFactory
    ) {
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManagerInterface;
        $this->catalogHelper = $catalogHelper;
        $this->cookieManager = $cookieManagerInterface;
        $this->coreRegistry = $registry;
        $this->cmsPage = $page;
        $this->campaignFactory = $campaignFactory;
        parent::__construct($context);
    }

    /**
     * Is popup active
     * @param null $store
     * @return bool
     */
    public function isActive($store = null)
    {
        if ($this->scopeConfig->getValue(
            self::XML_PATH_EXIT_OFFER_GENERAL_IS_ACTIVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        )) {
            return true;
        }
        return false;
    }

    /**
     * Returns translated text
     * @param $keyWord
     * @return mixed
     */
    public function translate($keyWord)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TRANSLATE . '/' . $keyWord,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns popup text's from config
     * @return mixed
     */
    public function getTranslation()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TRANSLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns cookie name prefix
     * @return mixed
     */
    public function getCookiePrefix()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_COOKIE_NAME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is development mode active
     * @return bool
     */
    public function getIsDevelopmentMode()
    {
        $development = $this->scopeConfig->getValue(
            self::XML_PATH_GOD_MODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $development == 1;
    }

    /**
     * Returns current page id
     *
     * @return string
     */
    public function getCurrentPageId()
    {
        $pageId = 'all';
        if ($this->_getRequest()->getRouteName() == 'cms') {
            $pageId = 'cms_page_' . $this->cmsPage->getId();
        } else {
            $product = $this->catalogHelper->getProduct();
            if ($product && $product->getId()) {
                $pageId = 'product_page';
            } else {
                $category = $this->catalogHelper->getCategory();
                if ($category && $category->getId()) {
                    $pageId = 'category_page';
                } else {
                    $request = $this->_getRequest();
                    $module = $request->getModuleName();
                    $controller = $request->getControllerName();
                    $action = $request->getActionName();

                    if ($module == 'checkout' && $controller == 'cart' && $action == 'index') {
                        $pageId = 'cart_page';
                    } elseif ($module == 'checkout' && $controller == 'index' && $action == 'index') {
                        $pageId = 'checkout_page';
                    }
                }
            }
        }

        return $pageId;
    }

    /**
     * Returns cookie name which will indicate subscribed user
     * @return string
     */
    public function getSubscriberCookieName()
    {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->getCookiePrefix() . '_s' . $storeId . '_1';
    }

    /**
     * Returns cookie name which will indicate user who got coupon code
     * @return string
     */
    public function getYesNoButtonCookieName()
    {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->getCookiePrefix() . '_b' . $storeId . '_1';
    }

    /**
     * is user subscriber or not
     * @return bool
     */
    public function isSubscriber()
    {
        $cookieName = $this->getSubscriberCookieName();
        if (!$this->cookieManager->getCookie($cookieName, false)) {
            return false;
        }

        return true;
    }

    /**
     * Log message
     *
     * @param $message
     * @return bool
     */
    public function log($message)
    {
        $this->_logger->info($message);
        return true;
    }

    /**
     * Returns user ip
     * @return string
     */
    public function getUserIp()
    {
        return $this->_remoteAddress;
    }

    /**
     * Returns current page url
     * @return string
     */
    public function getCurrentPageUrl()
    {
        $urlInterface = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
        $url = $urlInterface->getCurrentUrl();
        return $url;
    }

    /**
     * Returns additional fields data as string
     * @param \Magetrend\Eop\Model\Popup $popup
     * @param array $postData
     * @return string
     */
    public function formatFormRequest($popup, $postData)
    {
        $additionalFields = $popup->getAdditionalFields();
        $request = '';
        if (!empty($additionalFields)) {
            foreach ($additionalFields as $field) {
                if (!isset($postData[$field['name']]) || empty($postData[$field['name']])) {
                    continue;
                }
                $request .= '<b>'.$field['label'] . '</b>: '.$postData[$field['name']].' <br>';
            }
        }
        return $request;
    }

    /**
     * Is active default subscription
     * @param null $store
     * @return bool
     */
    public function isActiveDefault($store = null)
    {
        if ($this->coreRegistry->registry('eop_disable') == 1) {
            return false;
        };

        if ($this->scopeConfig->getValue(
            self::XML_PATH_EOP_DEFAULT_IS_ACTIVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        )) {
            return true;
        }
        return false;
    }

    /**
     * Returns default campaign id
     * @param null $store
     * @return bool
     */
    public function getDefaultCampaignId($store = null)
    {
        if ($this->scopeConfig->getValue(
            self::XML_PATH_EOP_DEFAULT_CAMPAIGN,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        )) {
            return true;
        }
        return false;
    }

    /**
     * Returns status of guest subscription availability
     *
     * @return bool
     */
    public function getAllowGuestSubscription()
    {
        return $this->scopeConfig->getValue(
            \Magento\Newsletter\Model\Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) == 1;
    }

    /**
     * Returns popup by campaign ID
     *
     * @param $campaignId
     * @return \Magetrend\Eop\Model\Popup|bool
     */
    public function getPopup($campaignId)
    {
        $campaign = $this->getCampaign($campaignId);
        if (!$campaign) {
            return false;
        }

        return $campaign->getPopup();
    }

    /**
     * Returns campaign by id
     *
     * @param $campaignId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCampaign($campaignId)
    {
        if (!is_numeric($campaignId)) {
            return false;
        }
        $campaign = $this->campaignFactory->create()
            ->load($campaignId);
        if (!$campaign->getId()) {
            return false;
        }
        return $campaign;
    }
}

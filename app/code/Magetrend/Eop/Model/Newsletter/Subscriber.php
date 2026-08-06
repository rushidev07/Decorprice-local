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

namespace Magetrend\Eop\Model\Newsletter;

/**
 * Actions with subscriber
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Subscriber
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    public $request;

    /**
     * @var \Magetrend\Eop\Model\Popup
     */
    private $popup = null;

    /**
     * @var \Magetrend\Eop\Model\Campaign
     */
    public $campaign;

    /**
     * @var \Magetrend\Eop\Helper\Data
     */
    public $moduleHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    public $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    public $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $date;

    /**
     * Save additional subscribers data
     */
    public $subscriberDataRegistry = null;

    /**
     * Subscriber constructor.
     * @param \Magetrend\Eop\Model\Campaign $campaign
     * @param \Magento\Framework\App\RequestInterface $requestInterface
     * @param \Magetrend\Eop\Helper\Data $helper
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magetrend\Eop\Model\Campaign $campaign,
        \Magento\Framework\App\RequestInterface $requestInterface,
        \Magetrend\Eop\Helper\Data $helper,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Registry $registry
    ) {
        $this->request = $requestInterface;
        $this->campaign = $campaign;
        $this->moduleHelper = $helper;
        $this->coreRegistry = $registry;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->date = $date;
    }

    /**
     * Save additional subscriber data and generate discount code
     *
     * @param \Magento\Newsletter\Model\Subscriber
     * @return bool
     */
    public function beforeSubscribe(\Magento\Framework\DataObject $subscriber)
    {
        if (!$this->moduleHelper->isActive()) {
            return false;
        }
        //create cookie and don't show popup again
        $this->rememberSubscriber();

        $campaignId = $this->request->getParam('campaign_id');
        if (!is_numeric($campaignId) && !$this->moduleHelper->isActiveDefault()) {
            //subscriber is not from popup and default subscription is disabled
            return false;
        }

        $this->saveAdditionalData($subscriber);
        $this->createDiscountCoupon($subscriber);
        $subscriber->setCreatedAt($this->date->date('Y-m-d H:i:s'));
        return true;
    }

    /**
     *  Generate discount code for customer
     *
     * @param \Magento\Newsletter\Model\Subscriber
     * @return bool
     */
    public function beforeSubscribeCustomerById(\Magento\Framework\DataObject $subscriber)
    {
        if (!$this->moduleHelper->isActive()) {
            return false;
        }
        //create cookie and don't show popup again
        $this->rememberSubscriber();

        if (!$this->moduleHelper->isActiveDefault()) {
            //subscriber is not from popup and default subscription is disabled
            return false;
        }
        $this->createDiscountCoupon($subscriber);
        $subscriber->setCreatedAt($this->date->date('Y-m-d H:i:s'));
        return true;
    }

    /**
     * Save additional subscriber data
     *
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveAdditionalData(\Magento\Framework\DataObject $subscriber)
    {
        $additionalData = [];
        $popup = $this->getPopup();

        $additionalFields = $popup->getAdditionalFields();
        if (!empty($additionalFields)) {
            foreach ($additionalFields as $field) {
                $value = $this->request->getParam($field['name']);
                if (!empty($value) && $value != $field['label']) {
                    $additionalData[$field['name']] = $value;
                    $subscriber->setData('subscriber_'.$field['name'], $value);
                }
            }
        }
        $this->coreRegistry->register('eop_additional_data', $additionalData);
    }

    /**
     * Generate new discount code for subscriber
     *
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @return true
     */
    public function createDiscountCoupon(\Magento\Framework\DataObject $subscriber)
    {
        $popup = $this->getPopup();

        if (!$popup->getCouponIsActive()) {
            return false;
        }

        $code = $popup->getUniqueDiscountCode();
        $subscriber->setData(
            \Magetrend\Eop\Model\Popup::DISCOUNT_CODE_FIELD,
            $code
        );

        $this->coreRegistry->register('eop_coupon_code', $code);
        return true;
    }

    /**
     * Get Popup Object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magetrend\Eop\Model\Popup
     */
    public function getPopup()
    {
        if ($this->popup == null) {
            $this->popup = $this->getCampaign()->getPopup();
            if (!$this->popup->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Popup is no longer available'));
            }
        }
        return $this->popup;
    }

    /**
     * Get Campaign Object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magetrend\Eop\Model\Campaign
     */
    public function getCampaign()
    {
        if (!$this->campaign->getId()) {
            $campaignId = $this->request->getParam('campaign_id');

            if (!is_numeric($campaignId)) {
                $campaignId = $this->moduleHelper->getDefaultCampaignId();
            }

            $this->campaign->load($campaignId);
            if (!$this->campaign->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Campaign is no longer available'));
            }
        }
        return $this->campaign;
    }

    /**
     * Save information to cookie
     */
    public function rememberSubscriber()
    {
        $cookieName = $this->moduleHelper->getSubscriberCookieName();
        $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setHttpOnly(false)
            ->setDuration(2147483647)
            ->setPath('/');
        $this->cookieManager->setPublicCookie($cookieName, 1, $cookieMetadata);
    }
}

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

namespace Magetrend\Eop\Model\Validator\Popup;

use Magento\Customer\Model\Session;

/**
 * Popup subsbscribe action validator
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */
class Subscribe
{
    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    public $customerAccountManagement;

    /**
     * @var \Magetrend\Eop\Helper\Data
     */
    public $helper;

    /**
     * @var \Magento\Customer\Model\Url
     */
    public $customerUrl;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var Session
     */
    public $customerSession;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    public $subscriberFactory;

    /**
     * Subscribe validator constructor.
     *
     * @param \Magetrend\Eop\Helper\Data $helper
     * @param \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Session $session
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     */
    public function __construct(
        \Magetrend\Eop\Helper\Data $helper,
        \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Session $session,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
    ) {
        $this->storeManager = $storeManager;
        $this->customerUrl = $customerUrl;
        $this->helper = $helper;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerSession = $session;
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * Validates that the email address isn't being used by a different account.
     *
     * @param string $email
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function validateEmailAvailable($email)
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        if (($this->customerSession->getCustomerDataObject()->getEmail() !== $email
                && !$this->customerAccountManagement->isEmailAvailable($email, $websiteId))
            || $this->isSubscribed($email, $websiteId)
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($this->helper->translate('email_already_exist'))
            );
        }
    }

    /**
     * Validates that if the current user is a guest, that they can subscribe to a newsletter.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function validateGuestSubscription()
    {
        if (!$this->helper->getAllowGuestSubscription()
            && !$this->customerSession->isLoggedIn()
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(__(
                $this->helper->translate('only_for_customer'),
                $this->customerUrl->getRegisterUrl()
            ));
        }
    }

    /**
     * Validates the format of the email address
     *
     * @param string $email
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function validateEmailFormat($email)
    {
        if (!\Zend_Validate::is($email, 'EmailAddress')) {
            throw new \Magento\Framework\Exception\LocalizedException(__(
                $this->helper->translate('error_email_not_valid')
            ));
        }
    }

    /**
     * Check is email already subscribed
     * @param $email
     * @param $websiteId
     * @return bool
     */
    public function isSubscribed($email, $websiteId)
    {
        $subscriber = $this->subscriberFactory
            ->create()
            ->setWebsiteId($websiteId)
            ->loadByEmail($email);
        if (!$subscriber->getId()) {
            return false;
        }
        return true;
    }
}

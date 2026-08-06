<?php
namespace Aheadworks\RewardPoints\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Aheadworks\RewardPoints\Model\Config
 */
class Config
{
    /**#@+
     * Constants for config path.
     */
    const XML_PATH_SHIPPING_INCLUDES_TAX = 'tax/calculation/shipping_includes_tax';
    const XML_PATH_DEFAULT_CUSTOMER_GROUP_ID = 'customer/create_account/default_group';
    const XML_PATH_AW_REWARDPOINTS_POINTS_EARNING_CALCULATION =
        'aw_rewardpoints/calculation/points_earning_calculation';
    const XML_PATH_AW_REWARDPOINTS_IS_APPLYING_POINTS_TO_SHIPPING =
        'aw_rewardpoints/calculation/is_applying_points_to_shipping';
    const XML_PATH_AW_REWARDPOINTS_LIFETIME_SALES_START_DATE = 'aw_rewardpoints/calculation/lifetime_sales_start_date';
    const XML_PATH_AW_REWARDPOINTS_IS_REFUND_AUTOMATICALLY = 'aw_rewardpoints/calculation/is_refund_automatically';
    const XML_PATH_AW_REWARDPOINTS_EXPIRE_REWARD_POINTS = 'aw_rewardpoints/calculation/expire_reward_points';
    const XML_PATH_AW_REWARDPOINTS_IS_REIMBURSE_REFUND_POINTS =
        'aw_rewardpoints/calculation/is_reimburse_refund_points';
    const XML_PATH_AW_REWARDPOINTS_IS_ENABLE_APPLYING_POINTS_ON_SUBSCRIPTION =
        'aw_rewardpoints/calculation/is_enable_applying_points_on_subscription';
    const XML_PATH_AW_REWARDPOINTS_IS_CANCEL_EARNED_POINTS_REFUND_ORDER =
        'aw_rewardpoints/calculation/is_cancel_earned_points_refund_order';
    const XML_PATH_AW_REWARDPOINTS_SHARE_COVERED = 'aw_rewardpoints/calculation/share_covered';
    const XML_PATH_AW_REWARDPOINTS_ONCE_MIN_BALANCE = 'aw_rewardpoints/calculation/once_min_balance';
    const XML_PATH_AW_REWARDPOINTS_AWARDED_REGISTRATION = 'aw_rewardpoints/awarded/registration';
    const XML_PATH_AW_REWARDPOINTS_AWARDED_REVIEW = 'aw_rewardpoints/awarded/product_review';
    const XML_PATH_AW_REWARDPOINTS_AWARDED_REVIEW_DAILYLIMIT = 'aw_rewardpoints/awarded/product_review_daily_limit';
    const XML_PATH_AW_REWARDPOINTS_AWARDED_SHARE = 'aw_rewardpoints/awarded/sharing';
    const XML_PATH_AW_REWARDPOINTS_AWARDED_SHARE_DAILY_LIMIT = 'aw_rewardpoints/awarded/sharing_daily_limit';
    const XML_PATH_AW_REWARDPOINTS_AWARDED_SHARE_MONTHLY_LIMIT = 'aw_rewardpoints/awarded/sharing_monthly_limit';
    const XML_PATH_AW_REWARDPOINTS_AWARDED_REVIEW_ISPRODUCT_OWNER = 'aw_rewardpoints/awarded/is_product_review_owner';
    const XML_PATH_AW_REWARDPOINTS_AWARDED_NEWSLETTER_SIGNUP = 'aw_rewardpoints/awarded/newsletter_signup';
    const XML_PATH_AW_REWARDPOINTS_AWARDED_CUSTOMER_BIRTHDAY = 'aw_rewardpoints/awarded/customer_birthday';
    const XML_PATH_AW_REWARDPOINTS_AWARDED_CUSTOMER_BIRTHDAY_IN_ADVANCE_DAYS =
        'aw_rewardpoints/awarded/customer_birthday_in_advance_days';
    const XML_PATH_AW_REWARDPOINTS_AWARDED_CUSTOMER_BIRTHDAY_LIMIT = 'aw_rewardpoints/awarded/customer_birthday_limit';
    const XML_PATH_AW_REWARDPOINTS_FRONTEND_IS_TOP_LINK = 'aw_rewardpoints/frontend/is_points_balance_top_link';
    const XML_PATH_AW_REWARDPOINTS_FRONTEND_IS_HIDE_IF_BALANCE_EMPTY =
        'aw_rewardpoints/frontend/is_hide_if_rewardpoints_balance_empty';
    const XML_PATH_AW_REWARDPOINTS_FRONTEND_IS_DISPLAY_INVITATION_TO_NEWSLETTER =
        'aw_rewardpoints/frontend/is_display_invitation_to_newsletter';
    const XML_PATH_AW_REWARDPOINTS_FRONTEND_IS_DISPLAY_INVITATION_TO_REGISTER =
        'aw_rewardpoints/frontend/is_display_invitation_to_register';
    const XML_PATH_AW_REWARDPOINTS_IS_DISPLAY_DISCOUNT_INFO = 'aw_rewardpoints/frontend/is_display_discount_info';
    const XML_PATH_AW_REWARDPOINTS_IS_DISPLAY_SHARE_LINKS = 'aw_rewardpoints/frontend/is_display_social_button';
    const XML_PATH_AW_REWARDPOINTS_FRONTED_EXPLAINER_PAGE = 'aw_rewardpoints/frontend/rewardpoints_program_page';
    const XML_PATH_AW_REWARDPOINTS_CATEGORY_PRODUCT_PROMO_TEXT
        = 'aw_rewardpoints/frontend/category_product_promo_text';
    const XML_PATH_AW_REWARDPOINTS_PRODUCT_PROMO_TEXT_REGISTERED
        = 'aw_rewardpoints/frontend/product_promo_text_registered';
    const XML_PATH_AW_REWARDPOINTS_PRODUCT_PROMO_TEXT_NOT_LOGGED_IN
        = 'aw_rewardpoints/frontend/product_promo_text_not_logged_in';
    const XML_PATH_AW_REWARDPOINTS_SENDER_IDENTITY =
        'aw_rewardpoints/notifications/email_sender';
    const XML_PATH_AW_REWARDPOINTS_BALANCE_UPDATE_TEMPLATE_IDENTITY =
        'aw_rewardpoints/notifications/balance_update_template';
    const XML_PATH_AW_REWARDPOINTS_BALANCE_UPDATE_ACTIONS =
        'aw_rewardpoints/notifications/balance_update_actions';
    const XML_PATH_AW_REWARDPOINTS_SUBSCRIBE_CUSTOMERS_TO_NOTIFICATIONS_BY_DEFAULT =
        'aw_rewardpoints/notifications/is_subscribe_customers_to_notifications_by_default';
    const XML_PATH_AW_REWARDPOINTS_EXPIRATION_REMINDER_TEMPLATE_IDENTITY =
        'aw_rewardpoints/notifications/expiration_reminder_template';
    const XML_PATH_AW_REWARDPOINTS_EXPIRATION_REMINDER_DAYS =
        'aw_rewardpoints/notifications/expiration_reminder_days';
    /**#@-*/

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if shipping prices include tax
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isShippingPriceIncludesTax($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_SHIPPING_INCLUDES_TAX,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve config value for Points earning calculation
     *
     * @param  int|null $websiteId
     * @return int
     */
    public function getPointsEarningCalculation($websiteId = null)
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_AW_REWARDPOINTS_POINTS_EARNING_CALCULATION,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Retrieve config value for Allow applying points to shipping amount
     *
     * @param  int|null $websiteId
     * @return boolean
     */
    public function isApplyingPointsToShipping($websiteId = null)
    {
        return (boolean) $this->scopeConfig->isSetFlag(
            self::XML_PATH_AW_REWARDPOINTS_IS_APPLYING_POINTS_TO_SHIPPING,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Retrieve config value for lifetime sales start date
     *
     * @return string
     */
    public function getLifetimeSalesStartDate()
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_AW_REWARDPOINTS_LIFETIME_SALES_START_DATE);
    }

    /**
     * Retrieve config value for Refund to Reward Points Automatically
     *
     * @return boolean
     */
    public function isRewardPointsRefundAutomatically()
    {
        return (boolean) $this->scopeConfig->isSetFlag(
            self::XML_PATH_AW_REWARDPOINTS_IS_REFUND_AUTOMATICALLY
        );
    }

    /**
     * Retrieve config value for calculation expire days
     *
     * @param  int|null $websiteId
     * @return int
     */
    public function getCalculationExpireRewardPoints($websiteId = null)
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_AW_REWARDPOINTS_EXPIRE_REWARD_POINTS,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Retrieve config value for Reimburse Points Spent on Refunded Order
     *
     * @param  int|null $websiteId
     * @return boolean
     */
    public function isReimburseRefundPoints($websiteId = null)
    {
        return (boolean) $this->scopeConfig->isSetFlag(
            self::XML_PATH_AW_REWARDPOINTS_IS_REIMBURSE_REFUND_POINTS,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Retrieve config value for Share of purchase that could be covered by points, %
     *
     * @param  int|null $websiteId
     * @return int
     */
    public function getShareCoveredValue($websiteId = null)
    {
        $shareCoveredValue = (int) $this->scopeConfig->getValue(
            self::XML_PATH_AW_REWARDPOINTS_SHARE_COVERED,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
        return max(0, $shareCoveredValue);
    }

    /**
     * Retrieve config value for Points could be used once the point balance is over
     *
     * @param  int|null $websiteId
     * @return int
     */
    public function getOnceMinBalance($websiteId = null)
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_AW_REWARDPOINTS_ONCE_MIN_BALANCE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Retrieve config value for Enable applying points on subscription products
     *
     * @param  int|null $websiteId
     * @return boolean
     */
    public function isEnableApplyingPointsOnSubscription($websiteId = null)
    {
        return (boolean) $this->scopeConfig->isSetFlag(
            self::XML_PATH_AW_REWARDPOINTS_IS_ENABLE_APPLYING_POINTS_ON_SUBSCRIPTION,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Retrieve config value for Cancel points earned on refunded order
     *
     * @param  int|null $websiteId
     * @return boolean
     */
    public function isCancelEarnedPointsRefundOrder($websiteId = null)
    {
        return (boolean) $this->scopeConfig->isSetFlag(
            self::XML_PATH_AW_REWARDPOINTS_IS_CANCEL_EARNED_POINTS_REFUND_ORDER,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Retrieve config value for awarded points for registration
     *
     * @return int
     */
    public function getAwardedPointsForRegistration()
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_AW_REWARDPOINTS_AWARDED_REGISTRATION,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Retrieve config value for awarded points for review
     *
     * @param  int|null $websiteId
     * @return int
     */
    public function getAwardedPointsForReview($websiteId = null)
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_AW_REWARDPOINTS_AWARDED_REVIEW,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Retrieve config value for daily limit points for review
     *
     * @param  int|null $websiteId
     * @return int
     */
    public function getDailyLimitPointsForReview($websiteId = null)
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_AW_REWARDPOINTS_AWARDED_REVIEW_DAILYLIMIT,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Retrieve config value for awarded points for share
     *
     * @return int
     */
    public function getAwardedPointsForShare()
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_AW_REWARDPOINTS_AWARDED_SHARE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Retrieve config value for daily limit points for share
     *
     * @return int
     */
    public function getDailyLimitPointsForShare()
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_AW_REWARDPOINTS_AWARDED_SHARE_DAILY_LIMIT,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Retrieve config value for monthly limit points for share
     *
     * @return int
     */
    public function getMonthlyLimitPointsForShare()
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_AW_REWARDPOINTS_AWARDED_SHARE_MONTHLY_LIMIT,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Is review allowed for product's owner
     *
     * @return boolean
     */
    public function isProductReviewOwner()
    {
        return (boolean) $this->scopeConfig->isSetFlag(
            self::XML_PATH_AW_REWARDPOINTS_AWARDED_REVIEW_ISPRODUCT_OWNER,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Retrieve config value for awarded points for newsletter signup
     *
     * @return int
     */
    public function getAwardedPointsForNewsletterSignup()
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_AW_REWARDPOINTS_AWARDED_NEWSLETTER_SIGNUP,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Retrieve config value for awarded points for customer birthday
     *
     * @param int|null $websiteId
     * @return int
     */
    public function getAwardedPointsForCustomerBirthday($websiteId = null)
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_AW_REWARDPOINTS_AWARDED_CUSTOMER_BIRTHDAY,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Retrieve config value for customer birthday in advance days
     *
     * @return int
     */
    public function getCustomerBirthdayInAdvanceDays()
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_AW_REWARDPOINTS_AWARDED_CUSTOMER_BIRTHDAY_IN_ADVANCE_DAYS,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Retrieve config value for customer birthday limit
     *
     * @param int|null $websiteId
     * @return string
     */
    public function getCustomerBirthdayLimit($websiteId = null)
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_AW_REWARDPOINTS_AWARDED_CUSTOMER_BIRTHDAY_LIMIT,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Retrieve config value for Is Points Balance Top Link enable on Frontend
     *
     * @return boolean
     */
    public function isPointsBalanceTopLinkAtFrontend()
    {
        return (boolean) $this->scopeConfig->isSetFlag(
            self::XML_PATH_AW_REWARDPOINTS_FRONTEND_IS_TOP_LINK,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Retrieve config value for Hide the top-link if Reward Points balance is empty
     *
     * @return boolean
     */
    public function isHideIfRewardPointsBalanceEmpty()
    {
        return (boolean) $this->scopeConfig->isSetFlag(
            self::XML_PATH_AW_REWARDPOINTS_FRONTEND_IS_HIDE_IF_BALANCE_EMPTY,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Retrieve config value for Display block with discount information on Frontend
     *
     * @return boolean
     */
    public function isDisplayDiscountInfoBlock()
    {
        return (boolean) $this->scopeConfig->isSetFlag(
            self::XML_PATH_AW_REWARDPOINTS_IS_DISPLAY_DISCOUNT_INFO,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Retrieve config value for Display social sharing buttons at product page on Frontend
     *
     * @return boolean
     */
    public function isDisplayShareLinks()
    {
        return (boolean) $this->scopeConfig->isSetFlag(
            self::XML_PATH_AW_REWARDPOINTS_IS_DISPLAY_SHARE_LINKS,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Retrieve config value for Reward program explainer page on Frontend
     *
     * @param null|int $storeId
     * @return string
     */
    public function getFrontendExplainerPage($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AW_REWARDPOINTS_FRONTED_EXPLAINER_PAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve config value for product promo text on categories
     *
     * @param null|int $storeId
     * @return string
     */
    public function getCategoryProductPromoText($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AW_REWARDPOINTS_CATEGORY_PRODUCT_PROMO_TEXT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve config value for product promo text for registered customers
     *
     * @param null|int $storeId
     * @return string
     */
    public function getProductPromoTextForRegisteredCustomers($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AW_REWARDPOINTS_PRODUCT_PROMO_TEXT_REGISTERED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve config value for product promo text for not logged in visitors
     *
     * @param null|int $storeId
     * @return string
     */
    public function getProductPromoTextForNotLoggedInVisitors($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AW_REWARDPOINTS_PRODUCT_PROMO_TEXT_NOT_LOGGED_IN,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve config value for Display invitation to newsletter subscription on the registration page
     *
     * @param null|int $websiteId
     * @return string
     */
    public function getFrontendIsDisplayInvitationToNewsletter($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AW_REWARDPOINTS_FRONTEND_IS_DISPLAY_INVITATION_TO_NEWSLETTER,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Retrieve config value for Display an invitation to register
     *
     * @param null|int $websiteId
     * @return string
     */
    public function getFrontendIsDisplayInvitationToRegister($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AW_REWARDPOINTS_FRONTEND_IS_DISPLAY_INVITATION_TO_REGISTER,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Get email sender
     *
     * @param null|int $websiteId
     * @return string
     */
    public function getEmailSender($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AW_REWARDPOINTS_SENDER_IDENTITY,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Retrieve Subscribe customers to Reward Points notifications by default
     *
     * @param null|int $websiteId
     * @return boolean
     */
    public function isSubscribeCustomersToNotificationsByDefault($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AW_REWARDPOINTS_SUBSCRIBE_CUSTOMERS_TO_NOTIFICATIONS_BY_DEFAULT,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Get email sender name
     *
     * @param null|int $websiteId
     * @return string
     */
    public function getEmailSenderName($websiteId = null)
    {
        $sender = $this->getEmailSender($websiteId);

        return $this->scopeConfig->getValue(
            'trans_email/ident_' . $sender . '/name',
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Get balance update email template
     *
     * @param null|int $storeId
     * @return string
     */
    public function getBalanceUpdateEmailTemplate($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AW_REWARDPOINTS_BALANCE_UPDATE_TEMPLATE_IDENTITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get balance update actions
     *
     * @param null|int $websiteId
     * @return string
     */
    public function getBalanceUpdateActions($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AW_REWARDPOINTS_BALANCE_UPDATE_ACTIONS,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Get expiration reminder email template
     *
     * @param null|int $storeId
     * @return string
     */
    public function getExpirationReminderEmailTemplate($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AW_REWARDPOINTS_EXPIRATION_REMINDER_TEMPLATE_IDENTITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve config value for Expiration reminder timing, days
     *
     * @param null|int $websiteId
     * @return string
     */
    public function getExpirationReminderDays($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AW_REWARDPOINTS_EXPIRATION_REMINDER_DAYS,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Get default customer group id for guest
     *
     * @param null|int $storeId
     * @return int
     */
    public function getDefaultCustomerGroupIdForGuest($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_CUSTOMER_GROUP_ID,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}

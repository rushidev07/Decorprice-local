<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Helper;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    const GENERAL_ALLOW_GUEST_TO_ASK_QUESTION
        = 'aw_pquestion/general/allow_guest_to_ask_question';
    const GENERAL_ALLOW_CUSTOMER_TO_ADD_ANSWER_FROM_PRODUCT_PAGE
        = 'aw_pquestion/general/allow_customer_to_add_answer_from_product_page';
    const GENERAL_REQUIRE_MODERATE_CUSTOMER_ANSWER
        = 'aw_pquestion/general/require_moderate_customer_answer';
    const GENERAL_ALLOW_GUEST_RATE_HELPFULNESS
        = 'aw_pquestion/general/allow_guest_rate_helpfulness';
    const GENERAL_ALLOW_SUBSCRIBE_TO_NOTIFICATION_AUTOMATICALLY
        = 'aw_pquestion/general/allow_subscribe_to_notification_automatically';
    const GENERAL_ALLOW_CUSTOMER_DEFINED_QUESTION_VISIBILITY
        = 'aw_pquestion/general/allow_customer_defined_question_visibility';

    const INTERFACE_NUMBER_QUESTIONS_TO_DISPLAY
        = 'aw_pquestion/interface/number_questions_to_display';
    const INTERFACE_NUMBER_ANSWERS_TO_DISPLAY
        = 'aw_pquestion/interface/number_answers_to_display';
    const INTERFACE_ALLOW_DISPLAY_URL_AS_LINK
        = 'aw_pquestion/interface/allow_display_url_as_link';
    const INTERFACE_DEFAULT_QUESTIONS_SORT_BY
        = 'aw_pquestion/interface/default_questions_sort_by';
    const INTERFACE_DEFAULT_SORT_ORDER
        = 'aw_pquestion/interface/default_sort_order';

    const NOTIFICATION_SEND_NOTIFICATION_NEW_QUESTION_TO
        = 'aw_pquestion/notification/send_notification_new_question_to';
    const NOTIFICATION_EMAIL_SENDER
        = 'aw_pquestion/notification/email_sender';
    const NOTIFICATION_NEW_QUESTION_TO_ADMIN_TEMPLATE
        = 'aw_pquestion/notification/new_question_to_admin_template';
    const NOTIFICATION_NEW_ANSWER_TO_ADMIN_TEMPLATE
        = 'aw_pquestion/notification/new_answer_to_admin_template';
    const NOTIFICATION_NEW_REPLY_ON_QUESTION_TO_CUSTOMER_TEMPLATE
        = 'aw_pquestion/notification/new_reply_on_question_to_customer_template';
    const NOTIFICATION_QUESTION_STATUS_CHANGE_TO_CUSTOMER_TEMPLATE
        = 'aw_pquestion/notification/question_status_change_to_customer_template';
    const NOTIFICATION_ANSWER_STATUS_CHANGE_TO_CUSTOMER_TEMPLATE
        = 'aw_pquestion/notification/answer_status_change_to_customer_template';
    const NOTIFICATION_AUTO_RESPONDER_QUESTION_TEMPLATE
        = 'aw_pquestion/notification/auto_responder_question_template';
    const NOTIFICATION_AUTO_RESPONDER_ANSWER_TEMPLATE
        = 'aw_pquestion/notification/auto_responder_answer_template';
    const NOTIFICATION_STORED_EMAILS_LIFETIME
        = 'aw_pquestion/notification/stored_emails_lifetime';

    /**
     * @param null $store
     * @return bool
     */
    public function isAllowGuestToAskQuestion($store = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::GENERAL_ALLOW_GUEST_TO_ASK_QUESTION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getAllowCustomerToAddAnswer($store = null)
    {
        return $this->scopeConfig->getValue(
            self::GENERAL_ALLOW_CUSTOMER_TO_ADD_ANSWER_FROM_PRODUCT_PAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isRequireModerateCustomerAnswer($store = null)
    {
        return !(bool)$this->scopeConfig->getValue(
            self::GENERAL_REQUIRE_MODERATE_CUSTOMER_ANSWER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isAllowGuestRateHelpfulness($store = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::GENERAL_ALLOW_GUEST_RATE_HELPFULNESS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isAllowSubscribeToNotificationAutomatically($store = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::GENERAL_ALLOW_SUBSCRIBE_TO_NOTIFICATION_AUTOMATICALLY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isAllowCustomerDefinedQuestionVisibility($store = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::GENERAL_ALLOW_CUSTOMER_DEFINED_QUESTION_VISIBILITY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return int
     */
    public function getNumberQuestionsToDisplay($store = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::INTERFACE_NUMBER_QUESTIONS_TO_DISPLAY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return int
     */
    public function getNumberAnswersToDisplay($store = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::INTERFACE_NUMBER_ANSWERS_TO_DISPLAY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isAllowDisplayUrlAsLink($store = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::INTERFACE_ALLOW_DISPLAY_URL_AS_LINK,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getDefaultQuestionsSortBy($store = null)
    {
        return $this->scopeConfig->getValue(
            self::INTERFACE_DEFAULT_QUESTIONS_SORT_BY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getDefaultSortOrder($store = null)
    {
        return $this->scopeConfig->getValue(
            self::INTERFACE_DEFAULT_SORT_ORDER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getSendNewQuestionTo($store = null)
    {
        return $this->scopeConfig->getValue(
            self::NOTIFICATION_SEND_NOTIFICATION_NEW_QUESTION_TO,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getEmailSender($store = null)
    {
        return $this->scopeConfig->getValue(
            self::NOTIFICATION_EMAIL_SENDER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getNewQuestionToAdminTemplate($store = null)
    {
        return $this->scopeConfig->getValue(
            self::NOTIFICATION_NEW_QUESTION_TO_ADMIN_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getNewAnswerToAdminTemplate($store = null)
    {
        return $this->scopeConfig->getValue(
            self::NOTIFICATION_NEW_ANSWER_TO_ADMIN_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getNewReplyOnQuestionToCustomerTemplate($store = null)
    {
        return $this->scopeConfig->getValue(
            self::NOTIFICATION_NEW_REPLY_ON_QUESTION_TO_CUSTOMER_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getQuestionStatusChangeToCustomerTemplate($store = null)
    {
        return $this->scopeConfig->getValue(
            self::NOTIFICATION_QUESTION_STATUS_CHANGE_TO_CUSTOMER_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getStatusChangeToCustomerTemplate($store = null)
    {
        return $this->scopeConfig->getValue(
            self::NOTIFICATION_ANSWER_STATUS_CHANGE_TO_CUSTOMER_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getAutoResponderQuestionTemplate($store = null)
    {
        return $this->scopeConfig->getValue(
            self::NOTIFICATION_AUTO_RESPONDER_QUESTION_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getAutoResponderAnswerTemplate($store = null)
    {
        return $this->scopeConfig->getValue(
            self::NOTIFICATION_AUTO_RESPONDER_ANSWER_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return int|null
     */
    public function getStoredEmailsLifetime($store = null)
    {
        $value = (int)$this->scopeConfig->getValue(
            self::NOTIFICATION_STORED_EMAILS_LIFETIME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        if ($value < 1) {
            return null;
        }
        return $value;
    }
}

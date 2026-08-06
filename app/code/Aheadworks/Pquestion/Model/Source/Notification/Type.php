<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Model\Source\Notification;

class Type
{
    const ANSWER_STATUS_CHANGE_TO_CUSTOMER    = 'aw_pq_answer_status_change_to_customer';
    const QUESTION_AUTO_RESPONDER             = 'aw_pq_question_auto_responder';
    const ANSWER_AUTO_RESPONDER               = 'aw_pq_answer_auto_responder';
    const NEW_ANSWER_TO_ADMIN                 = 'aw_pq_new_answer_to_admin';
    const NEW_QUESTION_TO_ADMIN               = 'aw_pq_new_question_to_admin';
    const NEW_REPLY_ON_QUESTION_TO_CUSTOMER   = 'aw_pq_new_reply_on_question_to_customer';
    const QUESTION_STATUS_CHANGE_TO_CUSTOMER  = 'aw_pq_question_status_change_to_customer';

    const GROUP_FOR_CUSTOMER_NOTIFICATION_ABOUT_MY_QUESTIONS_UPDATES_VALUE = 1;
    const GROUP_FOR_CUSTOMER_NOTIFICATION_ABOUT_MY_ANSWERS_UPDATES_VALUE = 2;

    /**
     * @var array
     */
    public static $groupMapForCustomer = [
        self::GROUP_FOR_CUSTOMER_NOTIFICATION_ABOUT_MY_QUESTIONS_UPDATES_VALUE => [
            self::QUESTION_STATUS_CHANGE_TO_CUSTOMER,
            self::QUESTION_AUTO_RESPONDER,
            self::NEW_REPLY_ON_QUESTION_TO_CUSTOMER,
        ],
        self::GROUP_FOR_CUSTOMER_NOTIFICATION_ABOUT_MY_ANSWERS_UPDATES_VALUE => [
            self::ANSWER_STATUS_CHANGE_TO_CUSTOMER,
            self::ANSWER_AUTO_RESPONDER
        ]
    ];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Aheadworks\Pquestion\Helper\Config
     */
    protected $_configHelper;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Aheadworks\Pquestion\Helper\Config $configHelper
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Aheadworks\Pquestion\Helper\Config $configHelper
    ) {
        $this->_storeManager = $storeManager;
        $this->_configHelper = $configHelper;
    }

    /**
     * @param null $store
     * @return array
     */
    public function getAllTypesDataAsArray($store = null)
    {
        return [
            self::ANSWER_STATUS_CHANGE_TO_CUSTOMER   => [
                'template' => $this->_configHelper->getStatusChangeToCustomerTemplate($store),
                'send_now' => true
            ],
            self::QUESTION_AUTO_RESPONDER            => [
                'template' => $this->_configHelper->getAutoResponderQuestionTemplate($store),
                'send_now' => true
            ],
            self::ANSWER_AUTO_RESPONDER              => [
                'template' => $this->_configHelper->getAutoResponderAnswerTemplate($store),
                'send_now' => true
            ],
            self::NEW_ANSWER_TO_ADMIN                => [
                'template' => $this->_configHelper->getNewAnswerToAdminTemplate($store),
                'send_now' => true
            ],
            self::NEW_QUESTION_TO_ADMIN              => [
                'template' => $this->_configHelper->getNewQuestionToAdminTemplate($store),
                'send_now' => true
            ],
            self::NEW_REPLY_ON_QUESTION_TO_CUSTOMER  => [
                'template' => $this->_configHelper->getNewReplyOnQuestionToCustomerTemplate($store),
                'send_now' => true
            ],
            self::QUESTION_STATUS_CHANGE_TO_CUSTOMER => [
                'template' => $this->_configHelper->getQuestionStatusChangeToCustomerTemplate($store),
                'send_now' => true
            ],
        ];
    }

    /**
     * @return array
     */
    public function getAllGroupForCustomerDataAsArray()
    {
        return [
            self::GROUP_FOR_CUSTOMER_NOTIFICATION_ABOUT_MY_QUESTIONS_UPDATES_VALUE => [
                'label' => __('Question update notifications'),
            ],
            self::GROUP_FOR_CUSTOMER_NOTIFICATION_ABOUT_MY_ANSWERS_UPDATES_VALUE => [
                'label' => __('Answer update notifications'),
            ],
        ];
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getSender($store = null)
    {
        return $this->_configHelper->getEmailSender($store);
    }

    /**
     * @param null $store
     * @return int|null
     */
    public function getStoredEmailsLifetime($store = null)
    {
        return $this->_configHelper->getStoredEmailsLifetime($store);
    }

    /**
     * @param int|null $websiteId = null
     *
     * @return bool
     */
    public function isCustomerSubscribedByDefault($websiteId = null)
    {
        return $this->_configHelper->isAllowSubscribeToNotificationAutomatically(
            $this->_storeManager->getWebsite($websiteId)->getDefaultStore()
        );
    }
}

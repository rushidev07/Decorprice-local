<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Model\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class AnswerSaveAfter
 * @package Aheadworks\Pquestion\Model\Observer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AnswerSaveAfter implements ObserverInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Aheadworks\Pquestion\Helper\Config
     */
    protected $_configHelper;

    /**
     * @var \Aheadworks\Pquestion\Model\Notification
     */
    protected $_notificationModel;

    /**
     * @var \Aheadworks\Pquestion\Helper\Notification
     */
    protected $_notificationHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $_filterManager;

    /**
     * @var \Aheadworks\Pquestion\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Aheadworks\Pquestion\Model\Source\Question\Status
     */
    protected $_sourceQuestionStatus;

    /**
     * @var \Magento\Framework\TranslateInterface
     */
    protected $_translator;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * AnswerSaveAfter constructor.
     *
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Aheadworks\Pquestion\Helper\Config                $configHelper
     * @param \Aheadworks\Pquestion\Model\Notification           $notificationModel
     * @param \Aheadworks\Pquestion\Helper\Notification          $notificationHelper
     * @param \Psr\Log\LoggerInterface                           $logger
     * @param \Magento\Framework\Filter\FilterManager            $filterManager
     * @param \Aheadworks\Pquestion\Helper\Data                  $helper
     * @param \Aheadworks\Pquestion\Model\Source\Question\Status $sourceQuestionStatus
     * @param \Magento\Framework\TranslateInterface              $translator
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Aheadworks\Pquestion\Helper\Config $configHelper,
        \Aheadworks\Pquestion\Model\Notification $notificationModel,
        \Aheadworks\Pquestion\Helper\Notification $notificationHelper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Aheadworks\Pquestion\Helper\Data $helper,
        \Aheadworks\Pquestion\Model\Source\Question\Status $sourceQuestionStatus,
        \Magento\Framework\TranslateInterface $translator,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_storeManager = $storeManager;
        $this->_configHelper = $configHelper;
        $this->_notificationModel = $notificationModel;
        $this->_notificationHelper = $notificationHelper;
        $this->_logger = $logger;
        $this->_filterManager = $filterManager;
        $this->_helper = $helper;
        $this->_sourceQuestionStatus = $sourceQuestionStatus;
        $this->_translator = $translator;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Aheadworks\Pquestion\Model\Answer $answer */
        $answer = $observer->getAnswer();
        $storeId = $answer->getQuestion()->getStoreId();
        $store = $this->_storeManager->getStore($storeId);
        if ($answer->isObjectNew() && !$answer->getIsAdmin()) {
            $emailValidator = new \Zend_Validate_EmailAddress;
            if ($this->_configHelper->getSendNewQuestionTo()
                && $emailValidator->isValid($this->_configHelper->getSendNewQuestionTo())
            ) {
                try {
                    //new answer to admin
                    $this->_notificationModel->addToQueue(
                        __('Administrator')->render(),
                        $this->_configHelper->getSendNewQuestionTo(),
                        \Aheadworks\Pquestion\Model\Source\Notification\Type::NEW_ANSWER_TO_ADMIN,
                        $this->_getNewAnswerAdminVariables($answer),
                        $storeId
                    );
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                }
            }

            $_isCanNotify = $this->_notificationHelper->isCanNotifyCustomer(
                $answer->getAuthorEmail(),
                \Aheadworks\Pquestion\Model\Source\Notification\Type::ANSWER_AUTO_RESPONDER,
                $store->getWebsiteId()
            );
            if ($_isCanNotify) {
                try {
                    //auto responder new answer to answer owner
                    $this->_notificationModel->addToQueue(
                        $answer->getAuthorName(),
                        $answer->getAuthorEmail(),
                        \Aheadworks\Pquestion\Model\Source\Notification\Type::ANSWER_AUTO_RESPONDER,
                        $this->_getNewAnswerCustomerVariables($answer),
                        $storeId
                    );
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                }
            }
        }

        if ($answer->getOrigData('status') != $answer->getStatus()) {
            $_isCanNotify = $this->_notificationHelper->isCanNotifyCustomer(
                $answer->getQuestion()->getAuthorEmail(),
                \Aheadworks\Pquestion\Model\Source\Notification\Type::NEW_REPLY_ON_QUESTION_TO_CUSTOMER,
                $store->getWebsiteId()
            );
            if ($answer->getStatus() == \Aheadworks\Pquestion\Model\Source\Question\Status::APPROVED_VALUE
                && $_isCanNotify && !$answer->getQuestion()->getIsAdmin()
            ) {
                try {
                    //new reply on question to question owner
                    $this->_notificationModel->addToQueue(
                        $answer->getQuestion()->getAuthorName(),
                        $answer->getQuestion()->getAuthorEmail(),
                        \Aheadworks\Pquestion\Model\Source\Notification\Type::NEW_REPLY_ON_QUESTION_TO_CUSTOMER,
                        $this->_getReplyOnQuestionCustomerVariables($answer),
                        $storeId
                    );
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                }
            }

            $_isCanNotify = $this->_notificationHelper->isCanNotifyCustomer(
                $answer->getAuthorEmail(),
                \Aheadworks\Pquestion\Model\Source\Notification\Type::ANSWER_STATUS_CHANGE_TO_CUSTOMER,
                $store->getWebsiteId()
            );

            if ($_isCanNotify && !$answer->getIsAdmin()
                && (
                    ($answer->isObjectNew()
                        && $answer->getStatus() == \Aheadworks\Pquestion\Model\Source\Question\Status::APPROVED_VALUE
                    )
                    || !$answer->isObjectNew()
                )
            ) {
                try {
                    //answer status change notification to answer owner
                    $this->_notificationModel->addToQueue(
                        $answer->getAuthorName(),
                        $answer->getAuthorEmail(),
                        \Aheadworks\Pquestion\Model\Source\Notification\Type::ANSWER_STATUS_CHANGE_TO_CUSTOMER,
                        $this->_getAnswerStatusChangeCustomerVariables($answer),
                        $storeId
                    );
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                }
            }
        }
    }

    /**
     * @param \Aheadworks\Pquestion\Model\Answer $answer
     * @return array
     */
    protected function _getNewAnswerAdminVariables(\Aheadworks\Pquestion\Model\Answer $answer)
    {
        return array_merge(
            [
                'answer_text' => $answer->getContent()
            ],
            $this->_getNewQuestionAdminVariables($answer->getQuestion())
        );
    }

    /**
     * @param \Aheadworks\Pquestion\Model\Answer $answer
     * @return array
     */
    protected function _getNewAnswerCustomerVariables(\Aheadworks\Pquestion\Model\Answer $answer)
    {
        return array_merge(
            [
                'answer_text'   => $answer->getContent(),
                'customer_name' => $answer->getAuthorName(),
                'moderate'      => $this->_configHelper->isRequireModerateCustomerAnswer(
                    $answer->getQuestion()->getStoreId()
                ),
            ],
            $this->_getCommonVariables($answer->getQuestion())
        );
    }

    /**
     * @param \Aheadworks\Pquestion\Model\Answer $answer
     * @return array
     */
    protected function _getReplyOnQuestionCustomerVariables(\Aheadworks\Pquestion\Model\Answer $answer)
    {
        return array_merge(
            [
                'answer_text'   => $answer->getContent(),
                'customer_name' => $answer->getQuestion()->getAuthorName(),
            ],
            $this->_getCommonVariables($answer->getQuestion())
        );
    }

    /**
     * @param \Aheadworks\Pquestion\Model\Answer $answer
     * @return array
     */
    protected function _getAnswerStatusChangeCustomerVariables(\Aheadworks\Pquestion\Model\Answer $answer)
    {
        $statusLabel = $this->_getStatusLabel($answer);
        $_variables = array_merge(
            [
                'is_approved' => (
                    $answer->getStatus() == \Aheadworks\Pquestion\Model\Source\Question\Status::APPROVED_VALUE
                ),
                'is_registered'     => (bool)$answer->getCustomerId(),
                'new_answer_status' => $statusLabel,
                'customer_name' => $answer->getAuthorName(),
            ],
            $this->_helper->getPointsEmailVariables()
        );
        if (!$answer->getCustomerId()) {
            $_variables['points_amount'] = 0;
        }
        return array_merge($_variables, $this->_getNewAnswerCustomerVariables($answer));
    }

    /**
     * @param mixed $entity
     * @return null|string
     */
    protected function _getStatusLabel($entity)
    {
        if ($entity instanceof \Aheadworks\Pquestion\Model\Answer) {
            $storeId = $entity->getQuestion()->getStoreId();
        } else {
            $storeId = $entity->getStoreId();
        }
        if ($storeId) {
            $this->_translator->setLocale(
                $this->_scopeConfig->getValue(
                    'general/locale/code',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId
                )
            );
            $this->_translator->loadData('frontend', true);
        }
        return $this->_sourceQuestionStatus->getOptionByValue($entity->getStatus());
    }

    /**
     * @param \Aheadworks\Pquestion\Model\Question $question
     * @return array
     */
    protected function _getCommonVariables(\Aheadworks\Pquestion\Model\Question $question)
    {
        return [
            'product_url'   => $question->getProduct()->getProductUrl(),
            'product_name'  => $this->_filterManager->stripTags($question->getProduct()->getName()),
            'question_text' => $question->getContent(),
        ];
    }

    /**
     * @param \Aheadworks\Pquestion\Model\Question $question
     * @return array
     */
    protected function _getNewQuestionAdminVariables(\Aheadworks\Pquestion\Model\Question $question)
    {
        $backendUrl = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Backend\Model\Url::class)
        ;
        return [
            'product_url'             => $question->getProduct()->getProductUrl(),
            'product_name'            => $this->_filterManager->stripTags($question->getProduct()->getName()),
            'question_initiator_name' => $question->getAuthorName(),
            'question_text'           => $question->getContent(),
            'backend_question_page'   => $backendUrl->getUrl(
                'productquestion/question/edit',
                ['id' => $question->getId()]
            )
        ];
    }
}

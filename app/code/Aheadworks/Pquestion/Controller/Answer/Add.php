<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Controller\Answer;

class Add extends \Aheadworks\Pquestion\Controller\Answer
{
    /**
     * @var \Aheadworks\Pquestion\Helper\Notification
     */
    protected $_notificationHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Aheadworks\Pquestion\Helper\Notification $notificationHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param \Aheadworks\Pquestion\Helper\Config $configHelper
     * @param \Aheadworks\Pquestion\Model\Question $question
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Aheadworks\Pquestion\Helper\Notification $notificationHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Aheadworks\Pquestion\Helper\Config $configHelper,
        \Aheadworks\Pquestion\Model\Question $question,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        parent::__construct(
            $customerSession,
            $customer,
            $coreDate,
            $configHelper,
            $question,
            $session,
            $formKeyValidator,
            $context,
            $dateTime
        );
        $this->_notificationHelper = $notificationHelper;
        $this->_storeManager = $storeManager;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->_validateFormKey()) {
            return $resultRedirect->setRefererUrl();
        }

        try {
            $answerModel = $this->_initAnswer();
            $answerModel->save();
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
            return $resultRedirect->setRefererUrl();
        }

        $isSubscribed = $this->_notificationHelper->isCanNotifyCustomer(
            $answerModel->getAuthorEmail(),
            \Aheadworks\Pquestion\Model\Source\Notification\Type::ANSWER_AUTO_RESPONDER
        );
        if ($this->_configHelper->isRequireModerateCustomerAnswer()) {
            if ($isSubscribed) {
                if ($answerModel->getCustomerId()) {
                    $successMessage = "Your answer has been received. You will be notified on the answer status change."
                        . " You can track all your questions and answers <a href='%1'>here</a>.";
                    $this->messageManager->addSuccess(
                        __(
                            $successMessage,
                            $this->_url->getUrl(
                                'productquestion/customer/index',
                                ['_secure' => $this->_storeManager->getStore(true)->isCurrentlySecure()]
                            )
                        )
                    );
                } else {
                    $this->messageManager->addSuccess(
                        __('Your answer has been received. You will be notified on answer status change.')
                    );
                }
            } else {
                if ($answerModel->getCustomerId()) {
                    $successMessage = "Your answer has been received. " .
                        "You can track all your questions and the answers given <a href='%1'>here</a>";
                    $this->messageManager->addSuccess(
                        __(
                            $successMessage,
                            $this->_url->getUrl(
                                'productquestion/customer/index',
                                ['_secure' => $this->_storeManager->getStore(true)->isCurrentlySecure()]
                            )
                        )
                    );
                } else {
                    $this->messageManager->addSuccess(__('Your answer has been received.'));
                }
            }
        } else {
            if ($answerModel->getCustomerId()) {
                $this->messageManager->addSuccess(
                    __(
                        "Answer has been added successfully."
                        . " You can track all your questions and answers <a href='%1'>here</a>.",
                        $this->_url->getUrl(
                            'productquestion/customer/index',
                            ['_secure' => $this->_storeManager->getStore(true)->isCurrentlySecure()]
                        )
                    )
                );
            } else {
                $this->messageManager->addSuccess(__('Answer has been added successfully'));
            }
        }
        return $resultRedirect->setRefererUrl();
    }
}

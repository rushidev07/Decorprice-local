<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Controller\Question;

use Aheadworks\Pquestion\Controller\Question;
use Magento\Framework\Registry;
use Aheadworks\Pquestion\Model\QuestionFactory as QuestionModelFactory;
use Aheadworks\Pquestion\Helper\Notification;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Customer;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Aheadworks\Pquestion\Helper\Config;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\App\Action\Context;
use Aheadworks\Pquestion\Model\Source\Notification\Type;

class Add extends Question
{
    /**
     * @var Notification
     */
    protected $_notificationHelper;

    /**
     * @param Notification $notificationHelper
     * @param Session $customerSession
     * @param Customer $customer
     * @param DateTime $coreDate
     * @param Config $configHelper
     * @param SessionManagerInterface $session
     * @param StoreManagerInterface $storeManager
     * @param Product $product
     * @param Validator $formKeyValidator
     * @param Context $context
     * @param DateTime $dateTime
     * @param QuestionModelFactory $questionFactory
     * @param Registry $registry
     */
    public function __construct(
        Notification $notificationHelper,
        Session $customerSession,
        Customer $customer,
        DateTime $coreDate,
        Config $configHelper,
        SessionManagerInterface $session,
        StoreManagerInterface $storeManager,
        Product $product,
        Validator $formKeyValidator,
        Context $context,
        DateTime $dateTime,
        QuestionModelFactory $questionFactory,
        Registry $registry
    ) {
        parent::__construct(
            $customerSession,
            $customer,
            $coreDate,
            $configHelper,
            $session,
            $storeManager,
            $product,
            $formKeyValidator,
            $context,
            $dateTime,
            $questionFactory,
            $registry
        );
        $this->_notificationHelper = $notificationHelper;
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
            $questionModel = $this->_initQuestion();
            $questionModel->save();
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
            return $resultRedirect->setRefererUrl();
        }

        $_isSubscribed = $this->_notificationHelper->isCanNotifyCustomer(
            $questionModel->getAuthorEmail(),
            Type::QUESTION_AUTO_RESPONDER
        );
        if (!$_isSubscribed) {
            if ($questionModel->getCustomerId()) {
                $successMessage = "Your question has been received."
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
                $this->messageManager->addSuccess(__('Your question has been received.'));
            }
        } else {
            if ($questionModel->getCustomerId()) {
                $successMessage = "Your question has been received. A notification will be sent once the answer "
                    . "is published. You can see all your questions and answers <a href='%1'>here</a>";
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
                    __('Your question has been received. A notification will be sent once the answer is published.')
                );
            }
        }

        return $resultRedirect->setRefererUrl();
    }
}

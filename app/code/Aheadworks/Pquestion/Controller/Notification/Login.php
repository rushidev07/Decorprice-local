<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Controller\Notification;

use Aheadworks\Pquestion\Helper\Notification;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Framework\Controller\ResultFactory;

class Login extends \Aheadworks\Pquestion\Controller\Notification
{
    /**
     * @var Notification
     */
    protected $_notificationHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var Customer
     */
    protected $_customer;

    /**
     * @param Notification $notificationHelper
     * @param StoreManagerInterface $storeManager
     * @param Session $customerSession
     * @param Customer $customer
     * @param Context $context
     */
    public function __construct(
        Notification $notificationHelper,
        StoreManagerInterface $storeManager,
        Session $customerSession,
        Customer $customer,
        Context $context
    ) {
        parent::__construct($context);
        $this->_notificationHelper = $notificationHelper;
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        $this->_customer = $customer;
    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        /** @var Forward $resultForward */
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);

        $key = $this->getRequest()->getParam('key', null);
        if (null === $key) {
            return $resultForward->forward('noRoute');
        }
        $key = $this->_notificationHelper->decrypt($key);
        list($email, $redirectUrl, $storeId) = explode('|', $key ? $key : '||');
        if (empty($email) || empty($redirectUrl) || empty($storeId)) {
            return $resultForward->forward('noRoute');
        }
        if (!$this->_customerSession->isLoggedIn()) {
            $store = $this->_storeManager->getStore($storeId);
            $this->_customer->setWebsiteId($store->getWebsiteId())->loadByEmail($email);
            if (null !== $this->_customer->getId()) {
                $this->_customerSession->setCustomerAsLoggedIn($this->_customer);
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setUrl($redirectUrl);
    }
}

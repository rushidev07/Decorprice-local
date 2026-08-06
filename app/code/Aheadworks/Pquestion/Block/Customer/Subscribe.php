<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Block\Customer;

class Subscribe extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Aheadworks\Pquestion\Helper\Notification
     */
    protected $_notificationHelper;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Aheadworks\Pquestion\Helper\Notification $notificationHelper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Aheadworks\Pquestion\Helper\Notification $notificationHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_customerSession = $customerSession;
        $this->_notificationHelper = $notificationHelper;
    }

    /**
     * @return array;
     */
    public function getNotificationTypes()
    {
        return $this->_notificationHelper->getNotificationListForCustomer(
            (int)$this->_customerSession->getCustomerId()
        );
    }

    /**
     * @return string
     */
    public function getActionUrl()
    {
        return $this->getUrl('productquestion/customer/subscribe', ['_secure' => $this->_request->isSecure()]);
    }
}

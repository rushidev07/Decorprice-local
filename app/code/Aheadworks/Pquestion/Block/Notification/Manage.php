<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Block\Notification;

class Manage extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Aheadworks\Pquestion\Helper\Notification
     */
    protected $_notificationHelper;

    /**
     * @param \Magento\Framework\Registry $registry
     * @param \Aheadworks\Pquestion\Helper\Notification $notificationHelper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Aheadworks\Pquestion\Helper\Notification $notificationHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;
        $this->_notificationHelper = $notificationHelper;
    }

    /**
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl(
            "productquestion/notification/unsubscribePost",
            ['key' => $this->_request->getParam('key', '')]
        );
    }

    /**
     * @return array;
     */
    public function getNotificationTypes()
    {
        return $this->_notificationHelper->getNotificationListForCustomer(
            $this->getEmail()
        );
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->_coreRegistry->registry('current_email');
    }
}

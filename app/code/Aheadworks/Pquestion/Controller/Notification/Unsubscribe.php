<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Controller\Notification;

class Unsubscribe extends \Aheadworks\Pquestion\Controller\Notification
{
    /**
     * @var \Aheadworks\Pquestion\Helper\Notification
     */
    protected $_notificationHelper;

    /**
     * @param \Aheadworks\Pquestion\Helper\Notification $notificationHelper
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Aheadworks\Pquestion\Helper\Notification $notificationHelper,
        \Magento\Framework\App\Action\Context $context
    ) {
        parent::__construct($context);
        $this->_notificationHelper = $notificationHelper;
    }

    /**
     * @return $this|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
        $resultForward = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD);

        $key = $this->getRequest()->getParam('key', null);
        if (null === $key) {
            return $resultForward->forward('noRoute');
        }
        $key = $this->_notificationHelper->decrypt($key);
        list($email, $websiteId) = explode(',', $key ? $key : ',');
        if (empty($email) || empty($websiteId)) {
            return $resultForward->forward('noRoute');
        }
        $this->_objectManager->get(\Magento\Framework\Registry::class)
            ->register('current_email', $email)
        ;
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        return $resultPage;
    }
}

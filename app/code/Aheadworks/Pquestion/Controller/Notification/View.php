<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Controller\Notification;

class View extends \Aheadworks\Pquestion\Controller\Notification
{
    /**
     * @var \Aheadworks\Pquestion\Helper\Notification
     */
    protected $_notificationHelper;

    /**
     * @var \Aheadworks\Pquestion\Model\ResourceModel\Notification\Queue
     */
    protected $_queueResource;

    /**
     * @param \Aheadworks\Pquestion\Helper\Notification $notificationHelper
     * @param \Aheadworks\Pquestion\Model\ResourceModel\Notification\Queue $queueResource
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Aheadworks\Pquestion\Helper\Notification $notificationHelper,
        \Aheadworks\Pquestion\Model\ResourceModel\Notification\Queue $queueResource,
        \Magento\Framework\App\Action\Context $context
    ) {
        parent::__construct($context);
        $this->_notificationHelper = $notificationHelper;
        $this->_queueResource = $queueResource;
    }

    /**
     * @return $this|\Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $key = $this->getRequest()->getParam('key', null);
        if (null !== $key) {
            $key = $this->_notificationHelper->decrypt($key);
            list($email, $queueId) = explode(',', $key ? $key : ',');
            if (!empty($email) && !empty($queueId)) {
                $queueModel = $this->_queueResource->getStoredEmail($email, $queueId);
                if (null !== $queueModel->getId()) {
                    /** @var \Magento\Framework\View\Result\Page $resultPage */
                    $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
                    $block = $resultPage->getLayout()->getBlock('content')->setBody($queueModel->getBody());
                    /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
                    $resultRaw = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
                    $resultRaw->setContents($block->toHtml());
                    return $resultRaw;
                }
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
        $resultForward = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD);
        return $resultForward->forward('noRoute');
    }
}

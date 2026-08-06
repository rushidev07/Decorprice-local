<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Model\Notification;

use Magento\Framework\DataObject\IdentityInterface;

class Queue extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'aw_pquestion_notification_queue';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Aheadworks\Pquestion\Model\ResourceModel\Notification\Queue::class);
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return $this
     */
    public function send()
    {
        $isDisabled = $this->_scopeConfig->isSetFlag(
            \Magento\Email\Model\Template::XML_PATH_SYSTEM_SMTP_DISABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($isDisabled) {
            return $this;
        }
        $mail = new \Zend_Mail('utf-8');
        $mail->setBodyHtml($this->getBody());
        $mail
            ->setFrom($this->getSenderEmail(), $this->getSenderName())
            ->addTo($this->getRecipientEmail(), $this->getRecipientName())
            ->setSubject($this->getSubject())
        ;
        try {
            $mail->send();
            $_today = new \Zend_Date();
            $this->setSentAt(
                $_today->toString(\Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT)
            )->save();
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
        return $this;
    }
}

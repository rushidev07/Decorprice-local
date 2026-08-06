<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    Newsletter Popup Pro from M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Eop\Plugin\Newsletter\Model;

class SubscriptionManagerPlugin
{
    /**
     * @var \Magetrend\Eop\Model\Newsletter\Subscriber
     */
    public $subscriberModel;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    public $dataObjectFactory;

    /**
     * SubscriberPlugin constructor.
     * @param \Magetrend\Eop\Model\Newsletter\Subscriber $subscriber
     */
    public function __construct(
        \Magetrend\Eop\Model\Newsletter\Subscriber $subscriber,
        \Magento\Framework\DataObjectFactory $dataObjectFactory
    ) {
        $this->subscriberModel = $subscriber;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Prepare additional subscriber data
     * @param $subscriberManager
     * @param string $email
     * @param int $storeId
     * @return array
     */
    public function beforeSubscribe($subscriberManager, $email, $storeId)
    {
        $this->subscriberModel->subscriberDataRegistry = $this->dataObjectFactory->create();
        $this->subscriberModel->beforeSubscribe($this->subscriberModel->subscriberDataRegistry, $email);

        return [$email, $storeId];
    }

    /**
     * Prepare additional subscriber data
     * @param $subscriberManager
     * @param int $customerId
     * @param int $storeId
     * @return array
     */
    public function beforeSubscribeCustomer($subscriberManager, $customerId, $storeId)
    {
        $this->subscriberModel->subscriberDataRegistry = $this->dataObjectFactory->create();
        $this->subscriberModel->beforeSubscribeCustomerById(
            $this->subscriberModel->subscriberDataRegistry,
            $customerId
        );
        return [$customerId, $storeId];
    }
}

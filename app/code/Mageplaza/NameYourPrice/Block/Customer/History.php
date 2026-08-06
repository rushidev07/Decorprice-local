<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_NameYourPrice
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\NameYourPrice\Block\Customer;

use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Theme\Block\Html\Pager;
use Mageplaza\NameYourPrice\Model\ResourceModel\Requests\Collection;
use Mageplaza\NameYourPrice\Model\ResourceModel\Requests\CollectionFactory;

/**
 * Class History
 * @package Mageplaza\NameYourPrice\Block\Customer
 */
class History extends Template
{
    /**
     * @var string
     */
    protected $_template = 'customer/history.phtml';

    /**
     * @var Collection
     */
    protected $requests;

    /**
     * @var SessionFactory
     */
    protected $_sessionFactory;

    /**
     * @var CollectionFactory
     */
    protected $_requestsColFactory;

    /**
     * History constructor.
     *
     * @param Template\Context $context
     * @param SessionFactory $sessionFactory
     * @param CollectionFactory $requestsColFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        SessionFactory $sessionFactory,
        CollectionFactory $requestsColFactory,
        array $data = []
    ) {
        $this->_sessionFactory = $sessionFactory;
        $this->_requestsColFactory = $requestsColFactory;

        parent::__construct($context, $data);
    }

    /**
     * @return Collection|mixed
     */
    public function getBargainRequests()
    {
        if (!$this->requests) {
            $customer = $this->_sessionFactory->create();
            $email = $customer->getCustomerData()->getEmail();
            $this->requests = $this->_requestsColFactory->create()->addFieldToSelect(
                '*'
            )->addFieldToFilter('customer_email', $email)->setOrder('submitted_date', 'desc');
        }

        return $this->requests;
    }

    /**
     * @return $this|Template
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->getBargainRequests()) {
            $pager = $this->getLayout()->createBlock(Pager::class, 'sales.order.history.pager')
                ->setCollection($this->getBargainRequests());
            $this->setChild('pager', $pager);
            $this->getBargainRequests()->load();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @param int $requestId
     *
     * @return string
     */
    public function getSeeDetailUrl($requestId)
    {
        return $this->getUrl('mppricebargain/customer/detail', ['request_id' => $requestId]);
    }
}

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

namespace Mageplaza\NameYourPrice\Block;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Customer\Helper\View;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Message\ManagerInterface;
use Mageplaza\NameYourPrice\Helper\Data as HelperData;
use Mageplaza\NameYourPrice\Model\ConditionFactory;
use Mageplaza\NameYourPrice\Model\RequestsFactory;
use Mageplaza\NameYourPrice\Model\ResourceModel\Requests\Collection;
use Mageplaza\NameYourPrice\Model\ResourceModel\Requests\CollectionFactory;
use Mageplaza\NameYourPrice\Model\ResourceModel\RequestsFactory as ResourceFactory;

/**
 * Class Bargain
 * @package Mageplaza\NameYourPrice\Block
 */
class Bargain extends AbstractProduct
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var HttpContext
     */
    protected $_context;

    /**
     * @var SessionFactory
     */
    protected $_sessionFactory;

    /**
     * @var ConditionFactory
     */
    protected $_conditionFactory;

    /**
     * @var RequestsFactory
     */
    protected $_requestFactory;

    /**
     * @var ResourceFactory
     */
    protected $_resourceFactory;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var View
     */
    protected $_customerView;


    protected $resourceConnection;

    /**
     * Bargain constructor.
     *
     * @param Context $context
     * @param HelperData $helperData
     * @param HttpContext $httpContext
     * @param SessionFactory $sessionFactory
     * @param ConditionFactory $conditionFactory
     * @param RequestsFactory $requestsFactory
     * @param ResourceFactory $resourceFactory
     * @param CollectionFactory $collectionFactory
     * @param ManagerInterface $messageManager
     * @param View $customerView
     * @param ResourceConnection $resourceConnection
     * @param array $data
     */
    public function __construct(
        Context $context,
        HelperData $helperData,
        HttpContext $httpContext,
        SessionFactory $sessionFactory,
        ConditionFactory $conditionFactory,
        RequestsFactory $requestsFactory,
        ResourceFactory $resourceFactory,
        CollectionFactory $collectionFactory,
        ManagerInterface $messageManager,
        View $customerView,
        ResourceConnection $resourceConnection,
        array $data = []
    ) {
        $this->_helperData        = $helperData;
        $this->_context           = $httpContext;
        $this->_sessionFactory    = $sessionFactory;
        $this->_conditionFactory  = $conditionFactory;
        $this->_requestFactory    = $requestsFactory;
        $this->_resourceFactory   = $resourceFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_messageManager    = $messageManager;
        $this->_customerView      = $customerView;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context, $data);
    }

    /**
     * @param $fieldName
     *
     * @return bool
     */
    public function checkAdditionalInfo($fieldName)
    {
        $fields = explode(',', $this->_helperData->getAdditional());

        return in_array($fieldName, $fields, true);
    }

    /**
     * @return mixed|null
     */
    public function isLogin()
    {
        return $this->_context->getValue(CustomerContext::CONTEXT_AUTH);
    }

    /**
     * Auto close status bargain
     */
    public function autoCloseBargain()
    {
        $collections = $this->_collectionFactory->create()
            ->addFieldToFilter('status', HelperData::STATUS_APPROVED)
            ->addFieldToFilter('time_use', ['neq' => 'NULL'])
            ->addFieldToFilter('time_use', ['lteq' => date('Y-m-d h:i:s')]);

        if ($collections && $collections->getSize() > 0) {
            /** @var Collection[] $collections */
            foreach ($collections as $collection) {
                $collection->setStatus(HelperData::STATUS_CLOSED)->save();
            }
        }
    }

    /**
     * @return string|null
     */
    public function getEmailCustomer()
    {
        if ($this->isLogin()) {
            $customer = $this->_sessionFactory->create();

            return $customer->getCustomerData()->getEmail();
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isBargainProduct()
    {
        return $this->checkProductCondition() && $this->checkCustomerGroups();
    }

    /**
     * @return bool
     */
    public function checkProductCondition()
    {
        $productId = $this->getProduct()->getId();
        $condition = $this->_helperData->getConditionConfig();
        $curentCondition = json_decode($condition);
        if(!isset($curentCondition->conditions)){
            return true;
        }
        $productIds = $this->_conditionFactory->create()->getMatchingProductIds($condition, $productId);
        return in_array($productId, $productIds, true);
    }
    /**
     * @return bool
     */
    public function checkCustomerGroups()
    {
        $config        = $this->_helperData->getCustomerGroupsConfig();
        $customerGroup = 0;

        if ($this->_sessionFactory->create()->isLoggedIn()) {
            $customerGroup = $this->_sessionFactory->create()->getCustomer()->getGroupId();
        }

        return in_array($customerGroup, explode(',', $config));
    }
}

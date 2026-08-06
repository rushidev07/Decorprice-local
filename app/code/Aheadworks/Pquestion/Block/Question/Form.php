<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Block\Question;

class Form extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Aheadworks\Pquestion\Helper\Config
     */
    protected $_configHelper;

    /**
     * @var \Aheadworks\Pquestion\Helper\Request
     */
    protected $_requestHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $_formKey;

    /**
     * @param \Magento\Framework\Registry $registry
     * @param \Aheadworks\Pquestion\Helper\Config $configHelper
     * @param \Aheadworks\Pquestion\Helper\Request $requestHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Aheadworks\Pquestion\Helper\Config $configHelper,
        \Aheadworks\Pquestion\Helper\Request $requestHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\Product $product,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_registry = $registry;
        $this->_configHelper = $configHelper;
        $this->_requestHelper = $requestHelper;
        $this->_customerSession = $customerSession;
        $this->_product = $product;
        $this->_customer = $customer;
        $this->_formKey = $formKey;
    }

    /**
     * @return string
     */
    public function getAddQuestionUrl()
    {
        return $this->getUrl(
            'productquestion/question/add',
            ['_secure' => $this->_request->isSecure()]
        );
    }

    /**
     * @return $this|mixed
     */
    public function getProduct()
    {
        if ($this->_registry->registry('product')) {
            return $this->_registry->registry('product');
        }
        return $this->_product->load(
            $this->_requestHelper->getRewriteProductId()
        );
    }

    /**
     * @return bool
     */
    public function canSpecifyVisibility()
    {
        return $this->_configHelper->isAllowCustomerDefinedQuestionVisibility();
    }

    /**
     * @return string
     */
    public function getCustomerName()
    {
        $this->_customer->load(
            $this->_customerSession->getCustomerId()
        );
        return $this->_customer->getName();
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->_customerSession->isLoggedIn();
    }

    /**
     * @return string
     */
    public function getSessionFormKey()
    {
        return $this->_formKey->getFormKey();
    }
}

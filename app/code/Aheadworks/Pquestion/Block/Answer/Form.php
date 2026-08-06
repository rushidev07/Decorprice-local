<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Block\Answer;

class Form extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Aheadworks\Pquestion\Helper\Config
     */
    protected $_configHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

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
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Aheadworks\Pquestion\Helper\Config $configHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;
        $this->_configHelper = $configHelper;
        $this->_customerSession = $customerSession;
        $this->_moduleManager = $moduleManager;
        $this->_customer = $customer;
        $this->_formKey = $formKey;
    }

    /**
     * @return string
     */
    public function getAddAnswerUrl()
    {
        return $this->getUrl(
            'productquestion/answer/add',
            ['_secure' => $this->_request->isSecure()]
        );
    }

    /**
     * @return bool
     */
    public function isCanShowEmailAddress()
    {
        return !$this->_customerSession->isLoggedIn();
    }

    /**
     * @return array
     */
    public function getAllInfoMessages()
    {
        $list = [];
        if ($this->_configHelper->isRequireModerateCustomerAnswer()) {
            $list[] = __('All answers will be displayed after moderation.');
        }
        return $list;
    }

    /**
     * @return int
     */
    public function getPointsForAnswer()
    {
        if (!$this->_moduleManager->isEnabled('Aheadworks_Points')
            || !$this->_customerSession->isLoggedIn()
        ) {
            return 0;
        }
        $pointsConfigHelper = \Magento\Framework\App\ObjectManager::getInstance()
            ->create('Aheadworks\Points\Helper\Config')
        ;
        return $pointsConfigHelper->getPointsForAnsweringProductQuestion();
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
     * @return string
     */
    public function getSessionFormKey()
    {
        return $this->_formKey->getFormKey();
    }

    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->_coreRegistry->registry('product')->getId();
    }
}

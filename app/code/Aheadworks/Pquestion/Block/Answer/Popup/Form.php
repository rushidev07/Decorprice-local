<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Block\Answer\Popup;

class Form extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $_formKey;

    /**
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;
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

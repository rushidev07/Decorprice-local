<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Block\Question;

class AjaxContainer extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\PageCache\Model\Config
     */
    protected $_pageCacheConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Aheadworks\Pquestion\Helper\Request
     */
    protected $_requestHelper;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $_formKey;

    /**
     * @param \Magento\PageCache\Model\Config $pageCacheConfig
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\Product $product
     * @param \Aheadworks\Pquestion\Helper\Request $requestHelper
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\PageCache\Model\Config $pageCacheConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Product $product,
        \Aheadworks\Pquestion\Helper\Request $requestHelper,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->_pageCacheConfig = $pageCacheConfig;
        $this->_registry = $registry;
        $this->_product = $product;
        $this->_requestHelper = $requestHelper;
        $this->_formKey = $formKey;
        parent::__construct($context, $data);
        $this->setTitle(__('Product Questions'));
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_pageCacheConfig->isEnabled()) {
            return $this->getChildHtml();
        }
        return parent::_toHtml();
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('productquestion/question/qlist', [
            'product' => $this->getProduct()->getId(),
            '_secure' => $this->_request->isSecure()
        ]);
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
     * @return string
     */
    public function getFormKey()
    {
        return  $this->_formKey->getFormKey();
    }
}

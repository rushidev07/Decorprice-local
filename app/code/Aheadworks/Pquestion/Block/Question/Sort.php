<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Pquestion\Block\Question;

class Sort extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_orderParam  = 'orderby';

    /**
     * @var string
     */
    protected $_dirParam    = 'dir';

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
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Aheadworks\Pquestion\Model\Source\Question\Sorting
     */
    protected $_sourceSorting;

    /**
     * @var \Aheadworks\Pquestion\Model\Source\Question\Sorting\Dir
     */
    protected $_sourceDir;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $_formKey;

    /**
     * @param \Magento\Framework\Registry $registry
     * @param \Aheadworks\Pquestion\Helper\Config $configHelper
     * @param \Aheadworks\Pquestion\Helper\Request $requestHelper
     * @param \Magento\Catalog\Model\Product $product
     * @param \Aheadworks\Pquestion\Model\Source\Question\Sorting $sourceSorting
     * @param \Aheadworks\Pquestion\Model\Source\Question\Sorting\Dir $sourceDir
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Aheadworks\Pquestion\Helper\Config $configHelper,
        \Aheadworks\Pquestion\Helper\Request $requestHelper,
        \Magento\Catalog\Model\Product $product,
        \Aheadworks\Pquestion\Model\Source\Question\Sorting $sourceSorting,
        \Aheadworks\Pquestion\Model\Source\Question\Sorting\Dir $sourceDir,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_registry = $registry;
        $this->_configHelper = $configHelper;
        $this->_requestHelper = $requestHelper;
        $this->_product = $product;
        $this->_sourceSorting = $sourceSorting;
        $this->_sourceDir = $sourceDir;
        $this->_formKey = $formKey;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->_sourceSorting->toOptionArray();
    }

    /**
     * @return string
     */
    public function getCurrentOrder()
    {
        return $this->getRequest()->getParam(
            $this->_orderParam,
            $this->_configHelper->getDefaultQuestionsSortBy()
        );
    }

    /**
     * @return string
     */
    public function getCurrentDir()
    {
        return $this->getRequest()->getParam(
            $this->_dirParam,
            $this->_configHelper->getDefaultSortOrder()
        );
    }

    /**
     * @return string
     */
    public function getTargetDir()
    {
        return \Zend_Json::encode(
            $this->_sourceDir->getInvertedValue($this->getCurrentDir())
        );
    }

    /**
     * @return string
     */
    public function getSortUrl()
    {
        return \Zend_Json::encode(
            $this->getUrl(
                'productquestion/question/qlist',
                ['_secure' => $this->_request->isSecure()]
            )
        );
    }

    /**
     * @param string $dir
     *
     * @return string
     */
    public function getImageUrl($dir)
    {
        return $this->getViewFileUrl('Aheadworks_Pquestion::image/sort_' . strtolower($dir) . '_arrow.gif');
    }

    /**
     * @return string
     */
    public function getImages()
    {
        $asc = \Aheadworks\Pquestion\Model\Source\Question\Sorting\Dir::ASC_VALUE;
        $desc = \Aheadworks\Pquestion\Model\Source\Question\Sorting\Dir::DESC_VALUE;
        return \Zend_Json::encode(
            [
                $asc => $this->getImageUrl($asc),
                $desc => $this->getImageUrl($desc)
            ]
        );
    }

    /**
     * @return string
     */
    public function getOverlayImage()
    {
        return \Zend_Json::encode(
            $this->getViewFileUrl('Aheadworks_Pquestion::image/ajax-loader.gif')
        );
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if ($this->_registry->registry('product')) {
            $product = $this->_registry->registry('product');
        } else {
            $product = $this->_product->load(
                $this->_requestHelper->getRewriteProductId()
            );
        }
        return $product instanceof \Magento\Catalog\Model\Product ? $product->getId() : $product;
    }

    /**
     * @return string
     */
    public function getSessionFormKey()
    {
        return $this->_formKey->getFormKey();
    }
}

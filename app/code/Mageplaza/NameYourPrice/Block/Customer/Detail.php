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

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Mageplaza\NameYourPrice\Helper\Data as HelperData;
use Mageplaza\NameYourPrice\Model\Requests;
use Mageplaza\NameYourPrice\Model\RequestsFactory;

/**
 * Class Detail
 * @package Mageplaza\NameYourPrice\Block\Customer
 */
class Detail extends AbstractProduct
{
    /**
     * @var string
     */
    protected $_template = 'customer/detail.phtml';

    /**
     * @var RequestsFactory
     */
    protected $_requestsFactory;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var Configurable
     */
    protected $_productConfigurable;

    /**
     * Detail constructor.
     *
     * @param Context $context
     * @param RequestsFactory $requestsFactory
     * @param HelperData $helperData
     * @param Configurable $productConfigurable
     * @param array $data
     */
    public function __construct(
        Context $context,
        RequestsFactory $requestsFactory,
        HelperData $helperData,
        Configurable $productConfigurable,
        array $data = []
    ) {
        $this->_requestsFactory = $requestsFactory;
        $this->_helperData = $helperData;
        $this->_productConfigurable = $productConfigurable;

        parent::__construct($context, $data);
    }

    /**
     * @return Requests
     */
    public function getBargainRequest()
    {
        $requestId = $this->getRequestId();

        return $this->_requestsFactory->create()->load($requestId);
    }

    /**
     * @param int $productId
     *
     * @return Product
     */
    public function getProductById($productId)
    {
        $parentProductId = $this->_productConfigurable->getParentIdsByChild($productId);
        if (isset($parentProductId[0])) {
            $productId = $parentProductId[0];
        }

        return $this->_helperData->getProductById($productId);
    }
}

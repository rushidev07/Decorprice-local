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

namespace Mageplaza\NameYourPrice\Plugin\Controller\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Controller\Cart\Add;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\NameYourPrice\Block\Product\View\Bargain;
use Mageplaza\NameYourPrice\Helper\Data as HelperData;
use Mageplaza\NameYourPrice\Model\ResourceModel\Requests;

/**
 * Class AddToCart
 * @package Mageplaza\NameYourPrice\Controller\Cart
 */
class AddToCart extends Add
{
    /**
     * @var Requests
     */
    protected $_requestResource;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var Bargain
     */
    protected $_bargain;

    /**
     * AddToCart constructor.
     *
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param ProductRepositoryInterface $productRepository
     * @param HelperData $helperData
     * @param Requests $requestResource
     * @param Bargain $bargain
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Session $checkoutSession,
        StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository,
        HelperData $helperData,
        Requests $requestResource,
        Bargain $bargain
    ) {
        $this->_helperData = $helperData;
        $this->_requestResource = $requestResource;
        $this->_bargain = $bargain;

        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart,
            $productRepository
        );
    }

    /**
     * @param Add $subject
     * @param callable $proceed
     *
     * @return Redirect
     * @SuppressWarnings(Unused)
     */
    public function aroundExecute(Add $subject, callable $proceed)
    {
        if (!$this->_helperData->isEnabled() || !$this->_helperData->isBargainQty()) {
            return $proceed();
        }

        $childId = $this->_request->getParam('mpb-child-product');
        $productId = $childId ?: $this->_request->getParam('product');
        $qty = $this->_request->getParam('qty');
        $requestId = $this->_request->getParam('mpb-request-id');

        $collection = $this->_bargain->getBargainCollection(
            [HelperData::STATUS_APPROVED],
            $productId
        );

        if ($collection && $collection->getSize() > 0) {
            $requestId = $collection->getFirstItem()['request_id'];
        }

        $collection = $this->_bargain->getBargainCollection(
            [HelperData::STATUS_APPROVED],
            $productId,
            $requestId
        );

        if ($collection && $collection->getSize() > 0) {
            $qtyBargain = (int)$collection->getFirstItem()['bargain_qty'];
            if ($qtyBargain === 1 && $qty === null) {
                return $proceed();
            }
            if ($qty < $qtyBargain) {
                $this->messageManager->addErrorMessage(
                    __('The quantity must be equal to or greater than %1 items.', $qtyBargain)
                );

                return $this->goBack();
            }
        }

        return $proceed();
    }
}

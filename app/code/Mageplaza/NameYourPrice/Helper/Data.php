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

namespace Mageplaza\NameYourPrice\Helper;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\NameYourPrice\Model\Requests;
use Mageplaza\NameYourPrice\Model\ResourceModel\RequestsFactory;

/**
 * Class Data
 * @package Mageplaza\NameYourPrice\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'mppricebargain';
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECT = 'reject';
    const STATUS_REJECT_BY_CUSTOMER = 'customer_reject';
    const STATUS_CLOSED = 'closed';

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var RequestsFactory
     */
    protected $_requestsFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param ProductFactory $productFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param RequestsFactory $requestsFactory
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        ProductFactory $productFactory,
        OrderRepositoryInterface $orderRepository,
        RequestsFactory $requestsFactory,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->_productFactory = $productFactory;
        $this->_orderRepository = $orderRepository;
        $this->_requestsFactory = $requestsFactory;
        $this->_priceCurrency = $priceCurrency;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param Requests $request
     *
     * @return array
     */
    public function getIncrementOrderIds($request)
    {
        $orderIncrementIds = [];

        /** @var array $orderIds */
        $orderIds = $this->_requestsFactory->create()->getMatchingOrderIds($request);
        foreach ($orderIds as $orderId) {
            $order = $this->_orderRepository->get($orderId);
            $orderIncrementIds[] = $order->getIncrementId();
        }

        return $orderIncrementIds;
    }

    /**
     * @param int $productId
     *
     * @return Product
     */
    public function getProductById($productId)
    {
        return $this->_productFactory->create()->load($productId);
    }

    /**
     * @param int $productId
     *
     * @return int
     */
    public function getProductPrice($productId)
    {
        if ($productId) {
            /** @var Product $product */
            $product = $this->getProductById($productId);

            return $product->getPrice();
        }

        return 0;
    }

    /**
     * @param $price
     *
     * @return int
     */
    public function getFormatPrice($price)
    {
        $formatPrice = 0;
        try {
            $formatPrice = $this->storeManager->getStore()->getCurrentCurrency()->format($price);
        } catch (Exception $e) {
            $e->getMessage();
        }

        return $formatPrice;
    }

    /**
     * @param $amount
     * @param bool $includeContainer
     * @param null $scope
     * @param null $currency
     * @param int $precision
     *
     * @return float
     */
    public function formatPrice(
        $amount,
        $includeContainer = true,
        $scope = null,
        $currency = null,
        $precision = PriceCurrencyInterface::DEFAULT_PRECISION
    ) {
        return $this->_priceCurrency->format($amount, $includeContainer, $precision, $scope, $currency);
    }

    /**
     * convert float|int price
     *
     * @param $amount
     * @param null $scope
     * @param null $currency
     *
     * @return float
     */
    public function convert($amount, $scope = null, $currency = null)
    {
        return $this->_priceCurrency->convert($amount, $scope, $currency);
    }

    /**
     * @param $amount
     * @param bool $format
     * @param bool $includeContainer
     * @param null $scope
     *
     * @return float|string
     */
    public function convertPrice($amount, $format = true, $includeContainer = true, $scope = null)
    {
        return $format
            ? $this->_priceCurrency->convertAndFormat(
                $amount,
                $includeContainer,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                $scope
            )
            : $this->_priceCurrency->convert($amount, $scope);
    }

    /**
     * @param $productId
     *
     * @return array
     */
    public function getProductInfo($productId)
    {
        /** @var Product $product */
        $product = $this->_productFactory->create()->load($productId);

        return [
            'type' => $product->getTypeId(),
            'name' => $product->getName(),
            'sku' => $product->getSku(),
            'price' => $product->getPrice(),
        ];
    }

    /**
     * @param string $label
     *
     * @return Phrase|string
     *
     * todo: Use label from Status model
     */
    public function getLabelStatus($label)
    {
        switch ($label) {
            case self::STATUS_APPROVED:
                $label = __('Approved');
                break;
            case self::STATUS_REJECT:
                $label = __('Rejected by Admin');
                break;
            case self::STATUS_CLOSED:
                $label = __('Closed');
                break;
            case self::STATUS_PENDING:
                $label = __('Pending');
                break;
            case self::STATUS_REJECT_BY_CUSTOMER:
                $label = __('Cancelled by Customer');
                break;
            default:
                $label = '';
                break;
        }

        return $label;
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getCurrency()
    {
        return $this->storeManager->getStore()->getCurrentCurrency()->getCurrencySymbol();
    }

    /**
     * @param null $scopeId
     *
     * @return mixed
     */
    public function getCustomerGroupsConfig($scopeId = null)
    {
        return $this->getConfigGeneral('customer_groups', $scopeId);
    }

    /**
     * @param null $scopeId
     *
     * @return mixed
     */
    public function getConditionConfig($scopeId = null)
    {
        return $this->getConfigGeneral('condition', $scopeId);
    }

    /**
     * @param null $scopeId
     *
     * @return mixed
     */
    public function getMinPriceType($scopeId = null)
    {
        return $this->getConfigGeneral('price_type', $scopeId);
    }

    /**
     * @param null $scopeId
     *
     * @return int
     */
    public function getMinPriceValue($scopeId = null)
    {
        return $this->getConfigGeneral('price_value', $scopeId) ?: 0;
    }

    /**
     * @param null $scopeId
     *
     * @return bool
     */
    public function isApplyTax($scopeId = null)
    {
        return $this->isEnabled($scopeId) && $this->getConfigGeneral('is_tax', $scopeId);
    }

    /**
     * @param null $scopeId
     *
     * @return mixed
     */
    public function isApplyDiscount($scopeId = null)
    {
        return $this->getConfigGeneral('allow_discount', $scopeId);
    }

    /**
     * @param null $scopeId
     *
     * @return mixed
     */
    public function getLimitTime($scopeId = null)
    {
        return $this->getConfigGeneral('limit_time', $scopeId);
    }

    /**
     * @param null $scopeId
     *
     * @return bool
     */
    public function isBargainQty($scopeId = null)
    {
        return (bool)$this->getConfigGeneral('bargain_qty', $scopeId);
    }

    /**
     * @param $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getDisplayConfig($code, $storeId = null)
    {
        return $this->getModuleConfig('display/' . $code, $storeId);
    }

    /**
     * @param null $scopeId
     *
     * @return bool
     */
    public function isUsePopup($scopeId = null)
    {
        return (bool)$this->getDisplayConfig('is_popup', $scopeId);
    }

    /**
     * @param null $scopeId
     *
     * @return mixed
     */
    public function getAdditional($scopeId = null)
    {
        return $this->getDisplayConfig('fields', $scopeId);
    }

    /**
     * @param null $scopeId
     *
     * @return Phrase
     */
    public function getButtonLabel($scopeId = null)
    {
        $label = $this->getDisplayConfig('button_label', $scopeId);

        return $label ?: __('Price Bargain');
    }
}

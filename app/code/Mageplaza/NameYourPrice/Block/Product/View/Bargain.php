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

namespace Mageplaza\NameYourPrice\Block\Product\View;

use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\NameYourPrice\Block\Bargain as RequestBargain;
use Mageplaza\NameYourPrice\Helper\Data as HelperData;
use Mageplaza\NameYourPrice\Model\Config\Source\PriceType;
use Mageplaza\NameYourPrice\Model\Requests;
use Mageplaza\NameYourPrice\Model\ResourceModel\Requests\Collection;

/**
 * Class Bargain
 * @package Mageplaza\NameYourPrice\Block\Product\View
 */
class Bargain extends RequestBargain
{
    /**
     * @return string
     */
    public function getDataForm()
    {
        $requestId = $this->getRequestId();

        $isSetOption = false;
        $swatchOpsEls = '';
        if ($requestId) {
            $requestBargain = $this->_requestFactory->create()->load($requestId);
            if ($requestBargain->getStatus() === HelperData::STATUS_APPROVED) {
                $isSetOption = true;
            }
            $swatchOpsEls = $requestBargain->getOptionsConfigurable();
        }
        
        $params = [
            'productId' => $this->getProduct()->getId(),
            'urlCancel' => $this->getUrl('mppricebargain/index/cancel'),
            'isPopup' => $this->_helperData->isUsePopup(),
            'isLogin' => $this->isLogin(),
            'yourBargainUrl' => $this->getYourBargainUrl(),
            'productIdsBargained' => $this->getProductIdsBargained(),
            'minPriceType' => $this->_helperData->getMinPriceType(),
            'minPriceValue' => $this->_helperData->getMinPriceValue(),
            'productType' => $this->getProduct()->getTypeId(),
            'isSetOptions' => $isSetOption,
            'requestId' => $requestId,
            'swatchOpsEls' => $swatchOpsEls,
            'bundleData' => $this->getBundleData()
        ];

        return HelperData::jsonEncode($params);
    }

    /**
     * @return mixed
     */
    public function getRequestId()
    {
        $requestId = $this->getRequest()->getParam('request_id');
        $collection = $this->getBargainCollection(
            [HelperData::STATUS_APPROVED],
            $this->getProduct()->getId()
        );

        if ($collection && $collection->getSize() > 0) {
            $requestId = $collection->getFirstItem()['request_id'];
        }

        return $requestId;
    }

    /**
     * @return string|null
     */
    public function getBundleData()
    {
        if ($this->getProduct()->getTypeId() !== Type::TYPE_BUNDLE) {
            return null;
        }

        $requestId = $this->_request->getParam('request_id');
        $collection = $this->getBargainCollection(
            [HelperData::STATUS_APPROVED],
            $this->getProduct()->getId(),
            $requestId
        );

        if ($collection && $collection->getSize()) {
            $requests = $collection->getFirstItem();
            $data = [
                'qty' => HelperData::jsonDecode($requests['options_bundle'])['qty'],
                'price' => $this->_helperData->formatPrice($requests['bargain_price']),
                'serializeData' => $requests['serialize_bundle']
            ];

            return HelperData::jsonEncode($data);
        }

        return null;
    }

    /**
     * @return string
     */
    public function getYourBargainUrl()
    {
        return $this->getUrl('mppricebargain/customer/request', ['_current' => true]);
    }

    /**
     * @param int $requestId
     *
     * @return Requests
     */
    public function getRequestBargainById($requestId)
    {
        return $this->_requestFactory->create()->load($requestId);
    }

    /**
     * @return array
     */
    public function getProductIdsBargained()
    {
        $productIds = [];
        $status = [HelperData::STATUS_PENDING, HelperData::STATUS_APPROVED];
        $requestId = $this->_request->getParam('request_id');

        $collections = $this->getBargainCollection($status, null, $requestId);

        if ($collections && $collections->getSize() > 0) {
            foreach ($collections as $collection) {
                $productIds[] = $collection['product_id'];
            }
        }

        return $productIds;
    }

    /**
     * @return string|null
     */
    public function getNameCustomer()
    {
        if ($this->isLogin()) {
            $customer = $this->_sessionFactory->create();
            $customerData = $customer->getCustomerData();

            return $this->_customerView->getCustomerName($customerData);
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getCustomerPhone()
    {
        if ($this->isLogin()) {
            $phone = null;
            $customer = $this->_sessionFactory->create();
            $customerData = $customer->getCustomer();

            if ($customerData->getDefaultShippingAddress()) {
                $phone = $customerData->getDefaultShippingAddress()->getTelephone();
            }

            return $phone ?: null;
        }

        return null;
    }

    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getRequestUrl($storeId = null)
    {
        return $this->getUrl('mppricebargain/index/request', $storeId);
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @return float|int|mixed
     */
    public function getMinPrice()
    {
        $type = $this->_helperData->getMinPriceType();
        $value = $this->_helperData->getMinPriceValue();
        $productPrice = $this->_helperData->getProductPrice($this->getProduct()->getId());
        $price = $this->_helperData->convert($productPrice);

        if ($type === PriceType::FIXED) {
            return $value;
        }

        return $price - ($price * $value / 100);
    }

    /**
     * @param array $status
     * @param null $productId
     * @param null $requestId
     *
     * @return Collection|mixed|null
     */
    public function getBargainCollection($status, $productId = null, $requestId = null)
    {
        $email = $this->getEmailCustomer();

        if (!$requestId && !$email) {
            return null;
        }

        $collections = $this->_collectionFactory->create()->addFieldToFilter('status', $status);
        if ($productId) {
            $collections->addFieldToFilter('product_id', $productId);
        }

        if ($requestId) {
            $collections->addFieldToFilter('request_id', $requestId);
        }
        if ($email) {
            $collections->addFieldToFilter('customer_email', $email);
        }

        return $collections->setPageSize(1);
    }
}

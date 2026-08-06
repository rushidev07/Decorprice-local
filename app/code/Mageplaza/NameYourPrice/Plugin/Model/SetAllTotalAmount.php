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

namespace Mageplaza\NameYourPrice\Plugin\Model;

use Magento\Checkout\Model\Cart;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Item;
use Mageplaza\NameYourPrice\Helper\Data as HelperData;
use Mageplaza\NameYourPrice\Model\ResourceModel\Requests\CollectionFactory;

/**
 * Class SetAllTotalAmount
 * @package Mageplaza\NameYourPrice\Plugin\Model
 */
class SetAllTotalAmount
{
    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var Cart
     */
    protected $_cart;

    /**
     * SetAllTotalAmount constructor.
     *
     * @param HelperData $helperData
     * @param CollectionFactory $collectionFactory
     * @param Cart $cart
     */
    public function __construct(
        HelperData $helperData,
        CollectionFactory $collectionFactory,
        Cart $cart
    ) {
        $this->_helperData = $helperData;
        $this->_collectionFactory = $collectionFactory;
        $this->_cart = $cart;
    }

    /**
     * @param Total $subject
     * @param callable $proceed
     *
     * @return array
     * @SuppressWarnings(Unused)
     */
    public function aroundGetAllTotalAmounts(Total $subject, callable $proceed)
    {
        if (!$this->_helperData->isEnabled() || !$this->_helperData->isApplyTax()) {
            return $proceed();
        }

        $tax = 0;
        $total = array_sum($proceed());

        /** @var $item Item */
        foreach ($this->_cart->getQuote()->getAllItems() as $item) {
            $requestId = $item->getAdditionalData();
            $collection = $this->_collectionFactory->create()
                ->addFieldToFilter('request_id', $requestId)
                ->addFieldToFilter('status', HelperData::STATUS_APPROVED);
            if ($collection && $collection->getSize() > 0) {
                if ($item->getHasChildren() && $item->getProductType() !== 'configurable') {
                    continue;
                }

                $tax += $item->getTaxAmount();
            }
        }

        return [$total - $tax];
    }
}

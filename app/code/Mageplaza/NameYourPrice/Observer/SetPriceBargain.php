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

namespace Mageplaza\NameYourPrice\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Math\Calculator;
use Magento\Quote\Model\Quote\Item;
use Mageplaza\NameYourPrice\Block\Product\View\Bargain;
use Mageplaza\NameYourPrice\Helper\Data as HelperData;
use Mageplaza\NameYourPrice\Model\ResourceModel\Requests;

/**
 * Class SetDiscountPriceDealInCart
 * @package Mageplaza\DailyDeal\Observer
 */
class SetPriceBargain implements ObserverInterface
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
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var Bargain
     */
    protected $_bargain;

    /**
     * @var Calculator
     */
    protected $_calculator;

    /**
     * SetPriceBargain constructor.
     *
     * @param Requests $requestResource
     * @param HelperData $helperData
     * @param RequestInterface $request
     * @param Bargain $bargain
     * @param Calculator $calculator
     */
    public function __construct(
        Requests $requestResource,
        HelperData $helperData,
        RequestInterface $request,
        Bargain $bargain,
        Calculator $calculator
    ) {
        $this->_requestResource = $requestResource;
        $this->_helperData = $helperData;
        $this->_request = $request;
        $this->_bargain = $bargain;
        $this->_calculator = $calculator;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var $item Item */
        $item = $observer->getEvent()->getData('quote_item');
        if ($this->_helperData->isEnabled()) {
            $productId = 0;

            if ($item->getHasChildren()) {
                /** get child product id configuration product **/
                foreach ($item->getChildren() as $child) {
                    $productId = $child->getProduct()->getId();
                }
            } else {
                $productId = $item->getProduct()->getId();
            }

            $requestId = $this->_request->getParam('mpb-request-id');
            $verifyBundle = $this->_request->getParam('mpb-verify-options');

            if ($verifyBundle) {
                $productId = $this->_request->getParam('product');
                if ($verifyBundle !== 'ok') {
                    $requestId = $verifyBundle;
                }
            }

            if ($requestId === 'null') {
                $requestId = null;
            }

            $collection = $this->_bargain->getBargainCollection(
                [HelperData::STATUS_APPROVED],
                $productId,
                $requestId
            );

            /** set price for product */
            if ($collection && $collection->getSize()) {
                $bargainPrice = $collection->getFirstItem()['bargain_price'];

                /** set price for bundle product */
                if ($verifyBundle) {
                    $totalQty = 0;

                    /** @var Item $child */
                    if ($item->getHasChildren()) {
                        foreach ($item->getChildren() as $child) {
                            $totalQty += $child->getQty();
                        }

                        $childPrice = $this->_calculator->deltaRound($bargainPrice / $totalQty);
                        foreach ($item->getChildren() as $child) {
                            $child->setOriginalCustomPrice($childPrice);
                            $child->getProduct()->setIsSuperMode(true);
                            $child->setAdditionalData($requestId);
                        }
                    }
                }

                /** check applied discount for item */
                if (!$this->_helperData->isApplyDiscount()) {
                    $item->setNoDiscount(1);
                }

                $item->setAdditionalData($requestId);
                $item->setOriginalCustomPrice($bargainPrice);
                $item->getProduct()->setIsSuperMode(true);
            } else {
                $this->setDefaultOriginalPrice($item);
            }
        } else {
            $this->setDefaultOriginalPrice($item);
        }
    }

    /**
     * Set default price
     *
     * @param Item $item
     */
    public function setDefaultOriginalPrice($item)
    {
        /** @var Item $item */
        /** @var Item $child */
        if ($item->getHasChildren()) {
            foreach ($item->getChildren() as $child) {
                $child->setOriginalCustomPrice(null);
            }
        }
        $item->setOriginalCustomPrice(null);
        $item->getProduct()->setIsSuperMode(true);
    }
}

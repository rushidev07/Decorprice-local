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

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Item;
use Mageplaza\NameYourPrice\Helper\Data as HelperData;
use Mageplaza\NameYourPrice\Model\ResourceModel\Requests\CollectionFactory;

/**
 * Class ChangeTax
 * @package Mageplaza\NameYourPrice\Observer
 */
class ChangeTax implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * ChangeTax constructor.
     *
     * @param HelperData $helperData
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        HelperData $helperData,
        CollectionFactory $collectionFactory
    ) {
        $this->_helperData = $helperData;
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (!$this->_helperData->isApplyTax()) {
            return;
        }

        $tax = 0;
        $baseTax = 0;
        /** @var  Total $total */
        $total = $observer->getData('total');
        /** @var Quote $quote */
        $quote = $observer->getEvent()->getQuote();

        /** @var $item Item */
        foreach ($quote->getAllItems() as $item) {
            if ($item->getAdditionalData()) {
                if ($item->getHasChildren() && $item->getProductType() !== 'configurable') {
                    continue;
                }

                $tax += $item->getTaxAmount();
                $baseTax += $item->getBaseTaxAmount();
            }
        }

        if (!empty($total->getAppliedTaxes())) {
            $total->setTotalAmount('tax', $total->getTotalAmount('tax') - $tax);
            $total->setBaseTotalAmount('tax', $total->getBaseTotalAmount('tax') - $baseTax);
        }
    }
}

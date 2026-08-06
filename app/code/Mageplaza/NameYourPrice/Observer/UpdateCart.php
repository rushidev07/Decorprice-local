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

use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Model\Quote\Item;
use Mageplaza\NameYourPrice\Block\Product\View\Bargain;
use Mageplaza\NameYourPrice\Helper\Data as HelperData;
use Mageplaza\NameYourPrice\Model\ResourceModel\Requests\CollectionFactory;

/**
 * Class UpdateCart
 * @package Mageplaza\NameYourPrice\Observer
 */
class UpdateCart implements ObserverInterface
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
     * @var ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var Bargain
     */
    protected $_bargain;

    /**
     * UpdateCart constructor.
     *
     * @param HelperData $helperData
     * @param CollectionFactory $collectionFactory
     * @param ManagerInterface $messageManager
     * @param Bargain $bargain
     */
    public function __construct(
        HelperData $helperData,
        CollectionFactory $collectionFactory,
        ManagerInterface $messageManager,
        Bargain $bargain
    ) {
        $this->_helperData = $helperData;
        $this->_collectionFactory = $collectionFactory;
        $this->_messageManager = $messageManager;
        $this->_bargain = $bargain;
    }

    /**
     * @param Observer $observer
     *
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if (!$this->_helperData->isEnabled()) {
            return;
        }

        $productId = 0;
        $quote = $observer->getCart()->getQuote();
        $allItems = $quote->getAllItems();

        /** @var $item Item */
        foreach ($allItems as $item) {
            if (!$item->getAdditionalData()) {
                continue;
            }

            if (!$this->_bargain->isLogin()) {
                $item->setOriginalCustomPrice(null);
            }

            $price = $item->getOriginalCustomPrice();
            if ($item->getHasChildren() && $item->getProduct()->getTypeId() === 'configurable') {
                /** get child product id configuration product **/
                foreach ($item->getChildren() as $child) {
                    $productId = $child->getProduct()->getId();
                }
            } else {
                $productId = $item->getProduct()->getId();
            }

            $collection = $this->_bargain->getBargainCollection([HelperData::STATUS_APPROVED], $productId);
            if ($item->getProduct()->getTypeId() === Type::TYPE_BUNDLE) {
                $collection->addFieldToFilter('bargain_price', $price);
            }

            if ($collection && $collection->getSize() > 0) {
                $qty = $item->getTotalQty();
                $bargainQty = (float)$collection->setPageSize(1)->getFirstItem()['bargain_qty'];

                if ($bargainQty > $qty) {
                    throw new NoSuchEntityException(
                        __(
                            'The quantity of %1 must be equal to or greater than %2 items.',
                            $item->getName(),
                            $bargainQty
                        )
                    );
                }
            }
        }
    }
}

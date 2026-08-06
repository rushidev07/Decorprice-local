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

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Mageplaza\NameYourPrice\Block\Product\View\Bargain;
use Mageplaza\NameYourPrice\Helper\Data as HelperData;
use Mageplaza\NameYourPrice\Model\ResourceModel\Requests\CollectionFactory;
use Mageplaza\Osc\Model\CheckoutManagement;

/**
 * Class CheckUpdateQty
 * @package Mageplaza\NameYourPrice\Plugin\Model
 */
class CheckUpdateQty
{
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var Bargain
     */
    protected $_bargain;

    /**
     * CheckUpdateQty constructor.
     *
     * @param CartRepositoryInterface $cartRepository
     * @param HelperData $helperData
     * @param CollectionFactory $collectionFactory
     * @param Bargain $bargain
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        HelperData $helperData,
        CollectionFactory $collectionFactory,
        Bargain $bargain
    ) {
        $this->cartRepository = $cartRepository;
        $this->_helperData = $helperData;
        $this->_collectionFactory = $collectionFactory;
        $this->_bargain = $bargain;
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(Unused)
     * @throws NoSuchEntityException
     */
    public function aroundUpdateItemQty(CheckoutManagement $subject, callable $proceed, $cartId, $itemId, $itemQty)
    {
        if ((int)$itemQty === 0 || !$this->_helperData->isEnabled()) {
            return $proceed($cartId, $itemId, $itemQty);
        }

        /** @var Quote $quote */
        $quote = $this->cartRepository->getActive($cartId);
        $quoteItem = $quote->getItemById($itemId);
        $productId = 0;

        if ($quoteItem->getHasChildren() && $quoteItem->getProduct()->getTypeId() === 'configurable') {
            /** get child product id configuration product **/
            foreach ($quoteItem->getChildren() as $child) {
                $productId = $child->getProduct()->getId();
            }
        } else {
            $productId = $quoteItem->getProduct()->getId();
        }

        $email = $this->_bargain->getEmailCustomer();
        $collection = $this->_collectionFactory->create()
            ->addFieldToFilter('status', HelperData::STATUS_APPROVED)
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('bargain_price', $quoteItem->getOriginalCustomPrice());

        if ($email) {
            $collection->addFieldToFilter('customer_email', $email);
        }

        if ($collection && $collection->getSize() > 0) {
            $bargainQty = $collection->setPageSize(1)->getFirstItem()['bargain_qty'];
            if ($bargainQty > $itemQty) {
                throw new NoSuchEntityException(
                    __(
                        'The quantity of %1 must be equal to or greater than %2 items.',
                        $bargainQty,
                        $quoteItem->getName()
                    )
                );
            }
        }

        return $proceed($cartId, $itemId, $itemQty);
    }
}

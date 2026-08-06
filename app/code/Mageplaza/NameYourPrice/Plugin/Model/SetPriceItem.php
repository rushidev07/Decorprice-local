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

use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Mageplaza\NameYourPrice\Block\Product\View\Bargain;
use Mageplaza\NameYourPrice\Helper\Data as HelperData;
use Mageplaza\NameYourPrice\Model\ResourceModel\Requests\CollectionFactory;

/**
 * Class SetPriceItem
 * @package Mageplaza\NameYourPrice\Plugin\Model
 */
class SetPriceItem
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
     * @var Bargain
     */
    protected $_bargain;

    /**
     * CheckUpdateQty constructor.
     *
     * @param HelperData $helperData
     * @param CollectionFactory $collectionFactory
     * @param Bargain $bargain
     */
    public function __construct(
        HelperData $helperData,
        CollectionFactory $collectionFactory,
        Bargain $bargain
    ) {
        $this->_helperData = $helperData;
        $this->_collectionFactory = $collectionFactory;
        $this->_bargain = $bargain;
    }

    /**
     * @param AbstractItem $subject
     */
    public function beforeGetCalculationPriceOriginal(AbstractItem $subject)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $request = $objectManager->get('Magento\Framework\App\RequestInterface');
        if($request->getFullActionName() != 'sales_order_create_loadBlock'){
            /** @var $item Item */
            if ($this->_helperData->isEnabled()) {
                foreach ($subject->getQuote()->getAllItems() as $item) {
                    $requestId = $item->getAdditionalData();
                    if ($requestId) {
                        $collection = $this->_collectionFactory->create()
                            ->addFieldToFilter('request_id', $requestId)
                            ->addFieldToFilter('status', HelperData::STATUS_APPROVED);
                    } else {
                        $productId = $item->getProduct()->getId();
                        if ($item->getHasChildren()) {
                            /** get child product id configuration product **/
                            foreach ($item->getChildren() as $child) {
                                $productId = $child->getProduct()->getId();
                            }
                        }
                        $collection = $this->_bargain->getBargainCollection([HelperData::STATUS_APPROVED], $productId);
                    }
                    if ($collection && $collection->getSize()) {
                        $data = $collection->setPageSize(1)->getFirstItem();
                        $bargainPrice = $data['bargain_price'];
                        $requestId = $data['request_id'];
                        $item->setOriginalCustomPrice($bargainPrice);
                        $item->setAdditionalData($requestId);
                    } else {
                        $item->setOriginalCustomPrice(null);
                    }
                }
            } else {
                foreach ($subject->getQuote()->getAllItems() as $item) {
                    $item->setOriginalCustomPrice(null);
                }
            }
        }  
    }
}

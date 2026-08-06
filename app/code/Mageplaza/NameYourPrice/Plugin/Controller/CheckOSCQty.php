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

namespace Mageplaza\NameYourPrice\Plugin\Controller;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Mageplaza\NameYourPrice\Block\Product\View\Bargain;
use Mageplaza\NameYourPrice\Helper\Data as HelperData;
use Mageplaza\NameYourPrice\Model\ResourceModel\Requests\CollectionFactory;
use Mageplaza\Osc\Controller\Index\Index;

/**
 * Class CheckOSCQty
 * @package Mageplaza\NameYourPrice\Plugin\Controller
 */
class CheckOSCQty
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var Bargain
     */
    protected $_bargain;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var ManagerInterface
     */
    protected $_messageManager;

    /**
     * CheckOSCQty constructor.
     *
     * @param HelperData $helperData
     * @param Bargain $bargain
     * @param RequestInterface $request
     * @param CollectionFactory $collectionFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        HelperData $helperData,
        Bargain $bargain,
        RequestInterface $request,
        CollectionFactory $collectionFactory,
        ManagerInterface $messageManager
    ) {
        $this->_helperData = $helperData;
        $this->_bargain = $bargain;
        $this->_request = $request;
        $this->_collectionFactory = $collectionFactory;
        $this->_messageManager = $messageManager;
    }

    /**
     * @param Index $subject
     * @param callable $proceed
     * @param array $skuArray
     *
     * @return $this
     */
    public function aroundAddProductOsc(Index $subject, callable $proceed, $skuArray)
    {
        if ($this->_helperData->isEnabled()) {
            $email = $this->_bargain->getEmailCustomer();
            foreach ($skuArray as $sku => $qty) {
                $collection = $this->_collectionFactory->create()
                    ->addFieldToFilter('sku', $sku)
                    ->addFieldToFilter('status', HelperData::STATUS_APPROVED)
                    ->addFieldToFilter('customer_email', $email)
                    ->setPageSize(1);

                if ($collection && $collection->getSize() > 0) {
                    $bargainQty = $collection->getFirstItem()['bargain_qty'];

                    if ($qty < $bargainQty) {
                        $this->_messageManager->getMessages(true);
                        $this->_messageManager->addErrorMessage(
                            __('The quantity must be equal to or greater than %1 items.', $bargainQty)
                        );

                        return $this;
                    }
                }
            }
        }

        return $proceed($skuArray);
    }
}

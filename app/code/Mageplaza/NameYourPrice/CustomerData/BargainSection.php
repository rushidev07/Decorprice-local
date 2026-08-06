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

namespace Mageplaza\NameYourPrice\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Mageplaza\NameYourPrice\Helper\Data as HelperData;
use Mageplaza\NameYourPrice\Model\ResourceModel\Requests\Collection;
use Mageplaza\NameYourPrice\Model\ResourceModel\Requests\CollectionFactory;

/**
 * Class BargainSection
 * @package Mageplaza\NameYourPrice\CustomerData
 */
class BargainSection implements SectionSourceInterface
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
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * CustomSection constructor.
     *
     * @param HelperData $helperData
     * @param CurrentCustomer $currentCustomer
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        HelperData $helperData,
        CurrentCustomer $currentCustomer,
        CollectionFactory $collectionFactory
    ) {
        $this->_helperData = $helperData;
        $this->currentCustomer = $currentCustomer;
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        if (!$this->currentCustomer->getCustomerId()) {
            return [
                'bargainData' => HelperData::jsonEncode([]),
                'isLogin' => false
            ];
        }

        $customer = $this->currentCustomer->getCustomer();
        $email = $customer->getEmail();
        $collections = $this->_collectionFactory->create()
            ->addFieldToFilter('status', ['pending', 'approved'])
            ->addFieldToFilter('customer_email', $email);

        $data = [];
        $productIds = [];
        if ($collections && $collections->getSize() > 0) {
            /** @var Collection[] $collections */
            foreach ($collections as $request) {
                $data[$request->getProductId()] = [
                    'request_id' => $request->getRequestId(),
                    'status' => $request->getStatus(),
                    'qty' => $request->getBargainQty(),
                    'serializeData' => $request->getSerializeBundle(),
                    'options_configurable' => $request->getOptionsConfigurable(),
                    'bargain_price' => $this->_helperData->formatPrice($request->getBargainPrice()),
                ];
                $productIds[] = $request->getProductId();
            }
        }

        return [
            'bargainData' => HelperData::jsonEncode($data),
            'productIds' => HelperData::jsonEncode($productIds),
            'isLogin' => true
        ];
    }
}

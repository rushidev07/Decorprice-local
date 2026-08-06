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

namespace Mageplaza\NameYourPrice\Plugin\Block\Catalog\Product\View\Type\Bundle;

use Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option as BundleOption;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\RequestInterface;
use Mageplaza\NameYourPrice\Block\Product\View\Bargain;
use Mageplaza\NameYourPrice\Helper\Data as HelperData;

/**
 * Class Option
 * @package Mageplaza\NameYourPrice\Plugin\Block\Catalog\Product\View\Type\Bundle
 */
class Option
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var Bargain
     */
    protected $_bargain;

    /**
     * Option constructor.
     *
     * @param RequestInterface $request
     * @param HelperData $helperData
     * @param Bargain $bargain
     */
    public function __construct(
        RequestInterface $request,
        HelperData $helperData,
        Bargain $bargain
    ) {
        $this->_request = $request;
        $this->_helperData = $helperData;
        $this->_bargain = $bargain;
    }

    /**
     * @param BundleOption $subject
     * @param callable $proceed
     * @param Product $selection
     *
     * @return bool
     * @SuppressWarnings(Unused)
     */
    public function aroundIsSelected(BundleOption $subject, callable $proceed, $selection)
    {
        $requestId = $this->_request->getParam('request_id');
        if (!$requestId || !$this->_helperData->isEnabled()) {
            return $proceed($selection);
        }

        $productId = $this->_request->getParam('id');
        $collection = $this->_bargain->getBargainCollection([HelperData::STATUS_APPROVED], $productId, $requestId);
        if ($collection->getSize() > 0) {
            $optionsBundle = HelperData::jsonDecode($collection->getFirstItem()['options_bundle']);
            $listSelectedId = $optionsBundle['selectedIds'];

            return in_array($selection->getSelectionId(), $listSelectedId, true);
        }

        return $proceed($selection);
    }
}

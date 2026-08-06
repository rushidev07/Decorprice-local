<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @since        v1.12.11
 * @version      v1.0.0
 * @created      2025-03-25
 */

namespace WeSupply\Toolbox\Observer\NortonShoppingGuarantee\PackageProtection;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use WeSupply\Toolbox\Helper\NortonShoppingGuarantee\PackageProtection\NsgPpData as PackageProtectionHelper;

/**
 * Class SaveEpsiQuoteToOrder
 *
 * @package WeSupply\Toolbox\Observer\NortonShoppingGuarantee\PackageProtection
 */
class SaveEpsiQuoteToOrder implements ObserverInterface
{
    /**
     * @var PackageProtectionHelper
     */
    private PackageProtectionHelper $helper;

    /**
     *
     * @var PackageProtectionHelper
     */
    public function __construct(
        PackageProtectionHelper $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Transfer EPSI data from quote to order during checkout
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Quote $quote */
        $quote = $observer->getEvent()->getData('quote');

        // Transfer EPSI data from quote to order
        $isEpsi = $this->helper->isNsgPpEnabled() // Just make sure NSG is still enabled
            && $quote->getData('is_epsi');
        $epsiAmount = $isEpsi ? $quote->getData('epsi_amount') : 0;

        /** @var Order $order */
        $order = $observer->getEvent()->getData('order');

        // Set the data on the order
        $order->setData('is_epsi', $isEpsi);
        $order->setData('epsi_amount', $epsiAmount);
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @version      v1.0.0
 * @since        v1.12.11
 * @created      2025-03-26
 */

namespace WeSupply\Toolbox\Observer\NortonShoppingGuarantee\PackageProtection;

use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use WeSupply\Toolbox\Helper\NortonShoppingGuarantee\PackageProtection\NsgPpData as PackageProtectionHelper;
use WeSupply\Toolbox\Logger\Logger;

class UpdateEpsiData implements ObserverInterface
{
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var CookieManagerInterface
     */
    private CookieManagerInterface $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private CookieMetadataFactory $cookieMetaData;

    /**
     * @var CheckoutSession
     */
    private CheckoutSession $checkoutSession;

    /**
     * @var PackageProtectionHelper
     */
    private PackageProtectionHelper $helper;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * SendStateUpdated constructor.
     *
     * @param SerializerInterface $serializer
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetaData
     * @param CheckoutSession $checkoutSession
     * @param Logger $logger
     */
    public function __construct(
        SerializerInterface $serializer,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetaData,
        CheckoutSession $checkoutSession,
        PackageProtectionHelper $helper,
        Logger $logger
    ) {
        $this->serializer = $serializer;
        $this->cookieManager = $cookieManager;
        $this->cookieMetaData = $cookieMetaData;
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $quote = $observer->getEvent()->getData('quote');
        $isEpsi = $this->helper->isNsgPpEnabled() // Just make sure NSG is still enabled
            && $observer->getEvent()->getData('is_epsi');
        $cartFee = $isEpsi ? $observer->getEvent()->getData('cart_fee') : 0.0000;

        if ($quote) {
            $quote->setData('is_epsi', $isEpsi);
            $quote->setData('epsi_amount', ($isEpsi ? $cartFee : 0.0000));

            // Force recalculation of totals
            $quote->collectTotals();
            $quote->save();
        }

        // Set cookie for frontend detection
        $cookieMetadata = $this->cookieMetaData->createPublicCookieMetadata()
            ->setDuration(3600)
            ->setPath('/')
            ->setHttpOnly(false);

        try {
            $this->cookieManager->setPublicCookie(
                'nsgpp_reload',
                $this->serializer->serialize(['action' => 'reload_totals']),
                $cookieMetadata
            );
        } catch (Exception $e) {
            $this->logger->error('Failed to set NSGPP cookie: ' . $e->getMessage());
        }
    }
}

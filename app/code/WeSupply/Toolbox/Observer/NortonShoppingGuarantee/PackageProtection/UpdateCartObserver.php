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
use Magento\Framework\Event;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use WeSupply\Toolbox\Helper\NortonShoppingGuarantee\PackageProtection\NsgPpData as PackageProtectionHelper;
use WeSupply\Toolbox\Logger\Logger;

/**
 * Class UpdateCartObserver
 *
 * @package WeSupply\Toolbox\Observer\NortonShoppingGuarantee\PackageProtection
 */
class UpdateCartObserver implements ObserverInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $quoteRepository;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $eventManager;

    /**
     * @var PackageProtectionHelper
     */
    private PackageProtectionHelper $helper;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * UpdateCartObserver constructor.
     *
     * @param CartRepositoryInterface $quoteRepository
     * @param ManagerInterface $eventManager
     * @param PackageProtectionHelper $helper
     * @param Logger $logger
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        ManagerInterface $eventManager,
        PackageProtectionHelper $helper,
        Logger $logger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->eventManager = $eventManager;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        $quote = $this->handleDifferentEventTypes($event);

        if (!$quote || !$quote->getId()) {
            return;
        }

        /**
         * Force reload the quote to get updated totals,
         * and calculate the NSG Fee based on the updated totals.
         */
        $reloadedQuote = $this->quoteRepository->get($quote->getId());

        if (!$reloadedQuote || !$reloadedQuote->getId()) {
            return;
        }

        $this->eventManager->dispatch(
            'nsgpp_epsi_data_update',
            [
                'quote' => $reloadedQuote,
                'is_epsi' => $reloadedQuote->getData('is_epsi') ?? FALSE,
                'cart_fee' => $this->helper->calculateNsgFee($reloadedQuote),
            ]
        );
    }

    /**
     * Get quote from different event types
     *
     * @param Event $event
     *
     * @return null
     */
    private function handleDifferentEventTypes(Event $event)
    {
        try {
            // Handle different event types
            if ($event->getCart()) {
                return $event->getCart()->getQuote();
            }

            if ($event->getQuote()) {
                return $event->getQuote();
            }

            if ($event->getQuoteItem()) {
                return $event->getQuoteItem()->getQuote();
            }

            if ($event->getItem()) {
                return $event->getItem()->getQuote();
            }
        } catch (Exception $e) {
            $this->logger->error('Failed to load quote for EPSI amount update: ' . $e->getMessage());
        }

        return NULL;
    }
}

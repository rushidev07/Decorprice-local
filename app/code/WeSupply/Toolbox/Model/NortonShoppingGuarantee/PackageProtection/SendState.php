<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @version      v1.0.0
 * @since        v1.12.11
 * @created      2025-03-24
 */

namespace WeSupply\Toolbox\Model\NortonShoppingGuarantee\PackageProtection;

use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\ManagerInterface;
use WeSupply\Toolbox\Api\NortonShoppingGuarantee\PackageProtection\SendStateInterface;
use WeSupply\Toolbox\Api\NortonShoppingGuarantee\PackageProtection\StateResponseInterface;
use WeSupply\Toolbox\Helper\NortonShoppingGuarantee\PackageProtection\NsgPpData as PackageProtectionHelper;

/**
 * Class SendState
 *
 * @package WeSupply\Toolbox\Model\NortonShoppingGuarantee\PackageProtection
 */
class SendState implements SendStateInterface
{
    /**
     * @var StateResponseInterface
     */
    private StateResponseInterface $stateResponse;

    /**
     * @var PackageProtectionHelper
     */
    protected PackageProtectionHelper $helper;

    /**
     * @var CheckoutSession
     */
    protected CheckoutSession $checkoutSession;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $eventManager;

    /**
     * SendState constructor.
     *
     * @param StateResponseInterface $stateResponse
     * @param ManagerInterface $eventManager
     * @param CheckoutSession $checkoutSession
     * @param PackageProtectionHelper $helper
     */
    public function __construct(
        StateResponseInterface $stateResponse,
        ManagerInterface $eventManager,
        CheckoutSession $checkoutSession,
        PackageProtectionHelper $helper
    ) {
        $this->stateResponse = $stateResponse;
        $this->eventManager = $eventManager;
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
    }

    /**
     * Send the state to update the cart.
     *
     * @param string $isEpsi
     *
     * @return StateResponseInterface
     */
    public function execute($isEpsi)
    {
        try {
            $quote = $this->checkoutSession->getQuote();

            if (!$quote->getId()) {
                return $this->stateResponse->errorHandler(NULL, 'No active cart found!');
            }

            $isEpsi = filter_var($isEpsi ?? FALSE, FILTER_VALIDATE_BOOLEAN);
            // Calculate the NSG Fee based on the current cart total
            $cartFee = $this->helper->calculateNsgFee($quote);

            $this->eventManager->dispatch(
                'nsgpp_epsi_data_update',
                [
                    'quote' => $quote,
                    'is_epsi' => $isEpsi,
                    'cart_fee' => $cartFee,
                ]
            );

            $this->stateResponse->setIsEpsi($isEpsi);
            $this->stateResponse->setCartFee($cartFee);

        } catch (Exception $e) {
            return $this->stateResponse->errorHandler($e);
        }

        return $this->stateResponse;
    }
}

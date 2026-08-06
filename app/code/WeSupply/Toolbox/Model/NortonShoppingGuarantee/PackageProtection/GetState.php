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
use WeSupply\Toolbox\Api\NortonShoppingGuarantee\PackageProtection\GetStateInterface;
use WeSupply\Toolbox\Api\NortonShoppingGuarantee\PackageProtection\StateResponseInterface;
use WeSupply\Toolbox\Helper\NortonShoppingGuarantee\PackageProtection\NsgPpData as PackageProtectionHelper;

/**
 * Class GetState
 *
 * @package WeSupply\Toolbox\Model\NortonShoppingGuarantee\PackageProtection
 */
class GetState implements GetStateInterface
{
    /**
     * @var CheckoutSession
     */
    protected CheckoutSession $checkoutSession;

    /**
     * @var PackageProtectionHelper
     */
    protected PackageProtectionHelper $helper;

    /**
     * @var StateResponseInterface
     */
    private StateResponseInterface $stateResponse;

    /**
     * GetState constructor.
     *
     * @param CheckoutSession         $checkoutSession
     * @param PackageProtectionHelper $nsgPpHelper
     * @param StateResponseInterface  $stateResponseFactory
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        PackageProtectionHelper $nsgPpHelper,
        StateResponseInterface $stateResponseFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->stateResponse = $stateResponseFactory;
        $this->helper = $nsgPpHelper;
    }

    /**
     * Get the cart quote id and NSG state.
     *
     * @return StateResponseInterface
     */
    public function execute()
    {
        try {
            $quote = $this->checkoutSession->getQuote();

            if (!$quote->getId()) {
                return $this->stateResponse->errorHandler(NULL, 'No active cart found!');
            }

            $isEpsi = filter_var(
                $quote->getData('is_epsi') ?? FALSE,
                FILTER_VALIDATE_BOOLEAN
            );

            $this->stateResponse->setIsEpsi($isEpsi);
            $this->stateResponse->setCartFee($this->helper->calculateNsgFee($quote));

        } catch (Exception $e) {
            return $this->stateResponse->errorHandler($e);
        }

        return $this->stateResponse;
    }
}

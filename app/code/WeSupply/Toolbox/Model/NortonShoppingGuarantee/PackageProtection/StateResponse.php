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

use WeSupply\Toolbox\Api\NortonShoppingGuarantee\PackageProtection\StateResponseInterface;
use WeSupply\Toolbox\Logger\Logger;


/**
 * Class StateResponse
 *
 * @package WeSupply\Toolbox\Model\NortonShoppingGuarantee\PackageProtection
 */
class StateResponse implements StateResponseInterface
{

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @var bool|null
     */
    private ?bool $isEpsi = null;

    /**
     * @var float|null
     */
    private ?float $cartFee = null;

    public function __construct(
        Logger $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function getIsEpsi()
    {
        return $this->isEpsi;
    }

    /**
     * @inheritDoc
     */
    public function setIsEpsi($isEpsi)
    {
        $this->isEpsi = $isEpsi;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCartFee()
    {
        return $this->cartFee;
    }

    /**
     * @inheritDoc
     */
    public function setCartFee($cartFee)
    {
        $this->cartFee = $cartFee;

        return $this;
    }

    /**
     * Error handler
     *
     * @param null   $exception
     * @param string $message
     */
    public function errorHandler($exception = NULL, $message = '')
    {
        $errorMessage = $exception
            ? $exception->getMessage() : $message;

        $this->logger->error(sprintf('NSGPP Error :: %s', $errorMessage));

        return $this;
    }
}

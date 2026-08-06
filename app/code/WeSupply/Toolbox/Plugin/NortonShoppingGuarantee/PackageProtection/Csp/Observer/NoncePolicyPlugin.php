<?php

namespace WeSupply\Toolbox\Plugin\NortonShoppingGuarantee\PackageProtection\Csp\Observer;

use Exception;
use Magento\Csp\Observer\Render;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use WeSupply\Toolbox\Helper\NortonShoppingGuarantee\PackageProtection\NsgPpNonceData as NonceHelper;

class NoncePolicyPlugin
{
    /**
     * @var Random
     */
    protected Random $mathRandom;

    /**
     * @var NonceHelper
     */
    private NonceHelper $helper;

    /**
     * NoncePolicyPlugin constructor.
     *
     * @param Random      $mathRandom
     * @param NonceHelper $helper
     */
    public function __construct(
        Random $mathRandom,
        NonceHelper $helper
    ) {
        $this->mathRandom = $mathRandom;
        $this->helper = $helper;
    }

    /**
     * Add nonce to CSP policy for script-src-elem
     *
     * @param Render   $subject
     * @param Observer $observer
     *
     * @return array
     * @throws LocalizedException
     */
    public function beforeExecute(Render $subject, Observer $observer)
    {
        try {
            // Generate nonce regardless of ability to set CSP
            $nonce = base64_encode($this->mathRandom->getRandomString(16));
            $this->helper->setNonce($nonce);

            // Get the event
            $event = $observer->getEvent();
            if ($event) {
                // Try to access response object via event
                $response = $event->getData('response');

                if ($response) {
                    // Manual CSP header approach for older Magento versions
                    $scriptSrc = "script-src 'self' 'unsafe-inline' 'unsafe-eval' 'nonce-{$nonce}'";
                    $scriptSrcElem = "script-src-elem 'self' 'unsafe-inline' 'nonce-{$nonce}'";

                    // Add or update CSP headers
                    $response->setHeader('Content-Security-Policy', $scriptSrc, true);

                    // For browsers that support script-src-elem
                    if (!$response->getHeader('Content-Security-Policy-Report-Only')) {
                        $response->setHeader('Content-Security-Policy-Report-Only', $scriptSrcElem, true);
                    }
                }
            }
        } catch (Exception $e) {
            // Silently catch exceptions to prevent breaking page rendering
        }

        return [$observer];
    }
}

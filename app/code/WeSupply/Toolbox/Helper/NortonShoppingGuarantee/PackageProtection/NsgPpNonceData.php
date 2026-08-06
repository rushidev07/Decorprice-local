<?php

namespace WeSupply\Toolbox\Helper\NortonShoppingGuarantee\PackageProtection;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

class NsgPpNonceData extends AbstractHelper
{
    private const NONCE_COOKIE_NAME = 'nsgpp_nonce';

    /**
     * @var CookieManagerInterface
     */
    private CookieManagerInterface $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private CookieMetadataFactory $cookieMetadata;

    /**
     * NsgPpNonceData constructor.
     *
     * @param Context $context
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadata
     */
    public function __construct(
        Context $context,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadata
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadata = $cookieMetadata;

        parent::__construct($context);
    }

    /**
     * Set nonce in cookie
     *
     * @param string $nonce
     */
    public function setNonce(string $nonce): void
    {
        $metadata = $this->cookieMetadata->createPublicCookieMetadata()
            ->setDuration(3600)
            ->setPath('/')
            ->setHttpOnly(false); // Make accessible from JavaScript

        try {
            $this->cookieManager->setPublicCookie(self::NONCE_COOKIE_NAME, $nonce, $metadata);
        } catch (Exception $e) {
            // Silently catch exceptions to prevent breaking page rendering
        }
    }

    /**
     * Get nonce from cookie
     *
     * @return string|null
     */
    public function getNonce(): ?string
    {
        return $this->cookieManager->getCookie(self::NONCE_COOKIE_NAME);
    }
}

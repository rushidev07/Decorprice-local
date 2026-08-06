<?php

declare(strict_types=1);

namespace Ahy\WeltpixelOverrides\Plugin\WeltPixel\GA4;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use WeltPixel\GA4\Helper\Data as GtmHelper;
use WeltPixel\GA4\Model\CookieManager as Subject;

class CookieManagerPlugin
{
    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var GtmHelper
     */
    private $gtmHelper;

    public function __construct(
        CookieMetadataFactory $cookieMetadataFactory,
        CookieManagerInterface $cookieManager,
        SessionManagerInterface $sessionManager,
        GtmHelper $gtmHelper
    ) {
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieManager = $cookieManager;
        $this->sessionManager = $sessionManager;
        $this->gtmHelper = $gtmHelper;
    }

    /**
     * Run WeltPixel logic first, then override guest customerGroup cookie value.
     *
     * @param Subject  $subject
     * @param callable $proceed
     * @return void
     */
    public function aroundSetGA4Cookies(Subject $subject, callable $proceed)
    {
        $proceed();

        // Only when customer group dimension is enabled
        if (!$this->gtmHelper->isCustomDimensionCustomerGroupEnabled()) {
            return;
        }

        $currentGroup = (string)$this->cookieManager->getCookie(Subject::COOKIE_CUSTOMER_GROUP);

        // If it's already set to something meaningful, do not overwrite
        if ($currentGroup !== 'NOT LOGGED IN' && $currentGroup !== '') {
            return;
        }

        // Keep a stable value for guest users: if already set, reuse it; otherwise generate once.
        $existing = (string)$this->cookieManager->getCookie(Subject::COOKIE_CUSTOMER_GROUP);
        $guestValue = ($existing && $existing !== 'NOT LOGGED IN')
            ? $existing
            : hash('sha256', $this->sessionManager->getSessionId() . '|' . microtime(true));

        $secureCookieFlag = (bool)$this->gtmHelper->getSecureCookiesFlag();

        $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setDurationOneYear()
            ->setPath('/')
            ->setDomain($this->sessionManager->getCookieDomain())
            ->setSecure($secureCookieFlag)
            ->setHttpOnly(false);

        $this->cookieManager->setPublicCookie(Subject::COOKIE_CUSTOMER_GROUP, $guestValue, $cookieMetadata);
    }
}
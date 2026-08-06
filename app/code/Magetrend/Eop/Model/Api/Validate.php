<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.3 or later
 *
 * @category MageTrend
 * @package  Magetend/Eop
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-exit-intent-popup-extension
 */

namespace Magetrend\Eop\Model\Api;

/**
 * Class Validate
 * @package Magetrend\Eop\Model\Api
 */
class Validate implements \Magetrend\Eop\Api\ValidateInterface
{
    public $cart;

    public $campaignFactory;

    public function __construct(
        \Magento\Checkout\Model\Cart $cart,
        \Magetrend\Eop\Model\CampaignFactory $campaignFactory
    ) {
        $this->cart = $cart;
        $this->campaignFactory = $campaignFactory;
    }

    /**
     * @param int $campaignId
     * @return bool
     */
    public function canShowPopup($campaignId)
    {
        /**
         * @var \Magetrend\Eop\Model\Campaign $campaign
         */
        $campaign = $this->campaignFactory->create();
        $campaign->load($campaignId);
        if (!$campaign->getId()) {
            return false;
        }

        $quoteId = $this->cart->getQuote()->getId();
        if (!$quoteId) {
            return false;
        }

        return $campaign->canApplyRule($this->cart->getQuote());
    }
}

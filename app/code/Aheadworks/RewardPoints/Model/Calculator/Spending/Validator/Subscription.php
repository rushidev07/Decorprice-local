<?php
namespace Aheadworks\RewardPoints\Model\Calculator\Spending\Validator;

use Aheadworks\RewardPoints\Model\Config;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem as QuoteAbstractItem;
use Magento\Store\Model\StoreManagerInterface;
use Aheadworks\RewardPoints\Model\Quote\Item\Checker as QuoteItemChecker;

/**
 * Class Subscription
 *
 * @package Aheadworks\RewardPoints\Model\Calculator\Spending\Validator
 */
class Subscription extends AbstractValidator
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var QuoteItemChecker
     */
    private $quoteItemChecker;

    /**
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param QuoteItemChecker $quoteItemChecker
     */
    public function __construct(
        Config $config,
        StoreManagerInterface $storeManager,
        QuoteItemChecker $quoteItemChecker
    ) {
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->quoteItemChecker = $quoteItemChecker;
    }

    /**
     * Returns true if and only if quote item entity meets the validation requirements
     *
     * @param CartItemInterface|QuoteAbstractItem $quoteItem
     * @return bool
     */
    public function isValid($quoteItem)
    {
        if ($this->quoteItemChecker->hasSubscriptionProduct($quoteItem)
            && !$this->canSpendRewardPointsOnSubscriptionProduct($quoteItem)
        ) {
            return false;
        }

        return true;
    }

    /**
     * Check if reward points can be spend on subscription product
     *
     * @param CartItemInterface|QuoteAbstractItem $quoteItem
     * @return bool
     */
    private function canSpendRewardPointsOnSubscriptionProduct($quoteItem)
    {
        try {
            $websiteId = $this->storeManager->getStore($quoteItem->getStoreId())->getWebsiteId();
            return $this->config->isEnableApplyingPointsOnSubscription($websiteId);
        } catch (\Exception $exception) {
            return false;
        }
    }
}

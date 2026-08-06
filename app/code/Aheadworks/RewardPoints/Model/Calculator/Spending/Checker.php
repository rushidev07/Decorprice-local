<?php
namespace Aheadworks\RewardPoints\Model\Calculator\Spending;

use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem as QuoteAbstractItem;
use Magento\Framework\Validator\ValidatorInterface;

/**
 * Class Checker
 *
 * @package Aheadworks\RewardPoints\Model\Calculator\Spending
 */
class Checker
{
    /**
     * @var ValidatorInterface
     */
    private $quoteItemValidator;

    /**
     * @param ValidatorInterface $quoteItemValidator
     */
    public function __construct(
        ValidatorInterface $quoteItemValidator
    ) {
        $this->quoteItemValidator = $quoteItemValidator;
    }

    /**
     * Check if reward points can be spend on quote item
     *
     * @param CartItemInterface|QuoteAbstractItem $quoteItem
     * @return bool
     */
    public function canSpendRewardPointsOnQuoteItem($quoteItem)
    {
        try {
            $canSpend = $this->quoteItemValidator->isValid($quoteItem);
        } catch (\Exception $exception) {
            $canSpend = false;
        }
        return $canSpend;
    }
}

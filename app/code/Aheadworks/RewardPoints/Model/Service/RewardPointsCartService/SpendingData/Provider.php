<?php
namespace Aheadworks\RewardPoints\Model\Service\RewardPointsCartService\SpendingData;

use Aheadworks\RewardPoints\Model\Calculator\RateCalculator;
use Aheadworks\RewardPoints\Model\Service\RewardPointsCartService\SpendingData;
use Aheadworks\RewardPoints\Model\Service\RewardPointsCartService\SpendingDataFactory;
use Aheadworks\RewardPoints\Model\Config;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item\AbstractItem as QuoteAbstractItem;
use Magento\Quote\Model\Quote\Address;
use Aheadworks\RewardPoints\Model\Calculator\Spending\Checker as SpendingChecker;
use Aheadworks\RewardPoints\Model\Calculator\Quote\Item as QuoteItemCalculator;

/**
 * Class Provider
 *
 * @package Aheadworks\RewardPoints\Model\Service\RewardPointsCartService\SpendingData
 */
class Provider
{
    /**
     * Delta value for operations with float values
     */
    const DELTA = 0.0001;

    /**
     * @var SpendingDataFactory
     */
    private $dataFactory;

    /**
     * @var SpendingChecker
     */
    private $spendingChecker;

    /**
     * @var QuoteItemCalculator
     */
    private $quoteItemCalculator;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var RateCalculator
     */
    private $rateCalculator;

    /**
     * @param SpendingDataFactory $dataFactory
     * @param SpendingChecker $spendingChecker
     * @param QuoteItemCalculator $quoteItemCalculator
     * @param Config $config
     * @param RateCalculator $rateCalculator
     */
    public function __construct(
        SpendingDataFactory $dataFactory,
        SpendingChecker $spendingChecker,
        QuoteItemCalculator $quoteItemCalculator,
        Config $config,
        RateCalculator $rateCalculator
    ) {
        $this->dataFactory = $dataFactory;
        $this->spendingChecker = $spendingChecker;
        $this->quoteItemCalculator = $quoteItemCalculator;
        $this->config = $config;
        $this->rateCalculator = $rateCalculator;
    }

    /**
     * Retrieve calculated reward points data for applying on the specific quote
     *
     * @param Quote $quote
     * @param int|null $pointsQtyToApply
     * @return SpendingData
     */
    public function getDataByQuote($quote, $pointsQtyToApply = null)
    {
        $shippingAddress = $quote->getShippingAddress();
        $billingAddress = $quote->getBillingAddress();
        $quoteItems = $shippingAddress->getAllItems();
        if (empty($quoteItems)) {
            $quoteItems = $billingAddress->getAllItems();
        }
        return $this->getData(
            $quote,
            $quoteItems,
            $shippingAddress,
            $pointsQtyToApply
        );
    }

    /**
     * Retrieve calculated reward points data for applying on the specific quote
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param int|null $pointsQtyToApply
     * @return SpendingData
     */
    public function getDataByShippingAssignment($quote, $shippingAssignment, $pointsQtyToApply = null)
    {
        $address = $shippingAssignment->getShipping()->getAddress();
        $items = $shippingAssignment->getItems();
        return $this->getData(
            $quote,
            $items,
            $address,
            $pointsQtyToApply
        );
    }

    /**
     * Retrieve calculated reward points data for applying
     *
     * @param Quote $quote
     * @param CartItemInterface[]|QuoteAbstractItem[] $quoteItemList
     * @param AddressInterface|Address $quoteAddress
     * @param int|null $pointsQtyToApply
     * @return SpendingData
     */
    public function getData(
        $quote,
        $quoteItemList,
        $quoteAddress,
        $pointsQtyToApply = null
    ) {
        /** @var SpendingData $spendingData */
        $spendingData = $this->dataFactory->create();

        $customerId = $quote->getCustomerId();
        $websiteId = $quote->getStore()->getWebsiteId();

        $maxBaseTotal = 0;
        $validItemsCount = 0;

        if (!is_array($quoteItemList) || empty($quoteItemList)) {
            return $spendingData;
        }

        foreach ($quoteItemList as $quoteItem) {
            if ($quoteItem->getParentItem()) {
                continue;
            }

            if ($this->spendingChecker->canSpendRewardPointsOnQuoteItem($quoteItem)) {
                $maxBaseTotal += $this->quoteItemCalculator->calculateItemTotal($quoteItem);
            }
            $validItemsCount++;
        }

        $baseItemsTotal = $maxBaseTotal;
        $baseShippingAmount = 0;

        if ($this->config->isApplyingPointsToShipping($websiteId)) {
            if ($quoteAddress->getBaseShippingAmountForDiscount() > self::DELTA) {
                $baseShippingAmount = $quoteAddress->getBaseShippingAmountForDiscount();
            } elseif ($this->config->isShippingPriceIncludesTax($quoteAddress->getQuote()->getStore()->getId())) {
                $baseShippingAmount = $quoteAddress->getBaseShippingInclTax();
            } else {
                $baseShippingAmount = $quoteAddress->getBaseShippingAmount();
            }
            $maxBaseTotal += $baseShippingAmount - $quoteAddress->getBaseShippingDiscountAmount();
        }

        if ($shareCoveredPercent = $this->config->getShareCoveredValue($websiteId)) {
            $maxBaseTotal = $maxBaseTotal * $shareCoveredPercent / 100;
        }

        if (!$maxBaseTotal) {
            return $spendingData;
        }

        $availablePointsQty = $this->calculateAvailablePointsQty(
            $customerId,
            $websiteId,
            $maxBaseTotal,
            $pointsQtyToApply
        );
        if (!$availablePointsQty) {
            return $spendingData;
        }

        $maxBaseTotalCoveredByPoints = $this->rateCalculator->calculateRewardDiscount(
            $customerId,
            $availablePointsQty,
            $websiteId
        );
        $maxBaseTotalCoveredByPoints = min($maxBaseTotalCoveredByPoints, $maxBaseTotal);
        $maxTotalCoveredByPoints = $this->rateCalculator->convertCurrency($maxBaseTotalCoveredByPoints);

        if ($quote->getAwUseRewardPoints()) {
            $usedPoints =
                $quote->getAwRewardPoints() > $availablePointsQty
                    ? $availablePointsQty
                    : $quote->getAwRewardPoints()
            ;
            $usedPointsAmount =
                $quote->getAwRewardPointsAmount() > $maxTotalCoveredByPoints
                    ? $maxTotalCoveredByPoints
                    : $quote->getAwRewardPointsAmount()
            ;
            $baseUsedPointsAmount =
                $quote->getBaseAwRewardPointsAmount() > $maxBaseTotalCoveredByPoints
                    ? $maxBaseTotalCoveredByPoints
                    : $quote->getBaseAwRewardPointsAmount()
            ;

            $spendingData->setUsedPoints($usedPoints);
            $spendingData->setUsedPointsAmount($usedPointsAmount);
            $spendingData->setBaseUsedPointsAmount($baseUsedPointsAmount);
        }

        $spendingData->setBaseAvailablePointsAmount($maxBaseTotalCoveredByPoints);
        $spendingData->setAvailablePointsAmount($maxTotalCoveredByPoints);
        $spendingData->setAvailablePoints($availablePointsQty);
        $spendingData->setItemsCount($validItemsCount);
        $spendingData->setBaseItemsTotal($baseItemsTotal);
        $spendingData->setItemsTotal(
            $this->rateCalculator->convertCurrency($baseItemsTotal)
        );
        $spendingData->setBaseShippingAmount($baseShippingAmount);
        $spendingData->setShippingAmount(
            $this->rateCalculator->convertCurrency($baseShippingAmount)
        );
        return $spendingData;
    }

    /**
     * Calculate available points qty
     *
     * @param int $customerId
     * @param int $websiteId
     * @param float $maxBaseTotal
     * @param int|null $pointsQtyToApply
     * @return float
     */
    private function calculateAvailablePointsQty($customerId, $websiteId, $maxBaseTotal, $pointsQtyToApply)
    {
        $availablePointsQtyByBalance = $this->rateCalculator->calculateSpendPoints(
            $customerId,
            $maxBaseTotal,
            $websiteId
        );
        $pointsQtyToApply = (isset($pointsQtyToApply) && $pointsQtyToApply > 0)
            ? $pointsQtyToApply
            : null
        ;
        $availablePointsQtyByPointsQtyToApply = $this->rateCalculator->calculateSpendPoints(
            $customerId,
            $maxBaseTotal,
            $websiteId,
            $pointsQtyToApply
        );
        return min($availablePointsQtyByBalance, $availablePointsQtyByPointsQtyToApply);
    }
}

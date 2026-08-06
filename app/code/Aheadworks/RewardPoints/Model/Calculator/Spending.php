<?php
namespace Aheadworks\RewardPoints\Model\Calculator;

use Aheadworks\RewardPoints\Model\Calculator\Quote\Item as QuoteItemCalculator;
use Aheadworks\RewardPoints\Model\Config;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Quote\Api\Data\AddressInterface;
use Aheadworks\RewardPoints\Model\Service\RewardPointsCartService\SpendingData;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Spending
 *
 * @package Aheadworks\RewardPoints\Model\Calculator
 */
class Spending
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var RateCalculator
     */
    private $rateCalculator;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var QuoteItemCalculator
     */
    private $quoteItemCalculator;

    /**
     * @param Config $config
     * @param RateCalculator $rateCalculator
     * @param PriceCurrencyInterface $priceCurrency
     * @param QuoteItemCalculator $quoteItemCalculator
     */
    public function __construct(
        Config $config,
        RateCalculator $rateCalculator,
        PriceCurrencyInterface $priceCurrency,
        QuoteItemCalculator $quoteItemCalculator
    ) {
        $this->config = $config;
        $this->rateCalculator = $rateCalculator;
        $this->priceCurrency = $priceCurrency;
        $this->quoteItemCalculator = $quoteItemCalculator;
    }

    /**
     * Quote item reward points calculation process
     *
     * @param SpendingData $spendingData
     * @param AbstractItem $item
     * @param int $customerId
     * @param int $websiteId
     * @return $this
     */
    public function process(SpendingData $spendingData, AbstractItem $item, $customerId, $websiteId)
    {
        $item->setAwRewardPointsAmount(0);
        $item->setBaseAwRewardPointsAmount(0);
        $item->setAwRewardPoints(0);

        $itemPrice = $this->quoteItemCalculator->getItemPrice($item);
        if ($itemPrice < 0) {
            return $this;
        }

        $this->applyPoints($spendingData, $item, $customerId, $websiteId);
        return $this;
    }

    /**
     * Distribute reward points at parent item to children items
     *
     * @param AbstractItem $item
     * @param int $customerId
     * @param int $websiteId
     * @return $this
     */
    public function distributeRewardPoints(AbstractItem $item, $customerId, $websiteId)
    {
        $roundingDelta = [];
        $keys = [
            'aw_reward_points_amount',
            'base_aw_reward_points_amount'
        ];

        // Calculate parent price with discount for bundle dynamic product
        if ($item->getHasChildren() && $item->isChildrenCalculated()) {
            $parentBaseRowTotal = $this->quoteItemCalculator->getItemBasePrice($item) * $item->getTotalQty();
            foreach ($item->getChildren() as $child) {
                $parentBaseRowTotal = $parentBaseRowTotal - $child->getBaseDiscountAmount();
            }
        } else {
            $parentBaseRowTotal = $this->quoteItemCalculator->getItemBasePrice($item) * $item->getTotalQty();
        }
        $parentAwRewardPoints = $item->getAwRewardPoints();
        foreach ($keys as $key) {
            // Initialize the rounding delta to a tiny number to avoid floating point precision problem
            $roundingDelta[$key] = 0.0000001;
        }
        if ($parentBaseRowTotal > 0) {
            foreach ($item->getChildren() as $child) {
                $ratio = ($this->quoteItemCalculator->getItemBasePrice($child) * $child->getTotalQty() - $child->getBaseDiscountAmount())
                    / $parentBaseRowTotal;
                foreach ($keys as $key) {
                    if (!$item->hasData($key)) {
                        continue;
                    }
                    $value = $item->getData($key) * $ratio;
                    $roundedValue = $this->priceCurrency->round($value + $roundingDelta[$key]);
                    $roundingDelta[$key] += $value - $roundedValue;
                    $child->setData($key, $roundedValue);
                }
                $rewardPoints = $this->rateCalculator->calculateSpendPoints(
                    $customerId,
                    $child->getBaseAwRewardPointsAmount(),
                    $websiteId
                );
                $rewardPoints = min($rewardPoints, $parentAwRewardPoints);
                $child->setAwRewardPoints($rewardPoints);
                $parentAwRewardPoints = $parentAwRewardPoints - $rewardPoints;
            }
        }

        $item->setAwRewardPointsAmount(0);
        $item->setBaseAwRewardPointsAmount(0);
        $item->setAwRewardPoints(0);
        return $this;
    }

    /**
     * Shipping reward points calculation process
     *
     * @param SpendingData $spendingData
     * @param AddressInterface $address
     * @param int $customerId
     * @param int $websiteId
     * @return $this
     */
    public function processShipping(SpendingData $spendingData, AddressInterface $address, $customerId, $websiteId)
    {
        $shippingRewardPointsAmount = min(
            $spendingData->getAvailablePointsAmountLeft(),
            $spendingData->getShippingAmount()
        );
        $shippingBaseRewardPointsAmount = min(
            $spendingData->getBaseAvailablePointsAmountLeft(),
            $spendingData->getBaseShippingAmount()
        );
        $rewardPoints = $this->rateCalculator->calculateSpendPoints(
            $customerId,
            $shippingBaseRewardPointsAmount,
            $websiteId
        );
        $shippingRewardPoints = min($rewardPoints, $spendingData->getAvailablePointsLeft());

        $address->setAwRewardPointsShippingAmount($shippingRewardPointsAmount);
        $address->setBaseAwRewardPointsShippingAmount($shippingBaseRewardPointsAmount);
        $address->setAwRewardPointsShipping($shippingRewardPoints);

        $spendingData->setUsedPoints(
            $spendingData->getUsedPoints() + $shippingRewardPoints
        );
        $spendingData->setUsedPointsAmount(
            $spendingData->getUsedPointsAmount() + $shippingRewardPointsAmount
        );
        $spendingData->setBaseUsedPointsAmount(
            $spendingData->getBaseUsedPointsAmount() + $shippingBaseRewardPointsAmount
        );

        return $this;
    }

    /**
     * Apply points amount to item
     *
     * @param SpendingData $spendingData
     * @param AbstractItem $item
     * @param int $customerId
     * @param int $websiteId
     * @return void
     */
    private function applyPoints($spendingData, $item, $customerId, $websiteId)
    {
        $shippingPointAmount = $this->getAvailableShipmentPointsAmount(
            $websiteId,
            $spendingData->getItemsTotal(),
            $spendingData->getShippingAmount(),
            $spendingData->getAvailablePointsAmount()
        );
        $baseShippingPointAmount = $this->getAvailableShipmentPointsAmount(
            $websiteId,
            $spendingData->getBaseItemsTotal(),
            $spendingData->getBaseShippingAmount(),
            $spendingData->getBaseAvailablePointsAmount()
        );

        $itemRewardPointsAmount = $spendingData->getAvailablePointsAmountLeft() - $shippingPointAmount;
        $itemBaseRewardPointsAmount = $spendingData->getBaseAvailablePointsAmountLeft()
            - $baseShippingPointAmount;

        $itemPrice = $this->quoteItemCalculator->calculateItemPriceWithDiscount($item);
        $baseItemPrice = $this->quoteItemCalculator->calculateItemBasePriceWithDiscount($item);

        if ($spendingData->getItemsCount() > 1
            && $spendingData->getBaseItemsTotal() > 0
            && $spendingData->getItemsTotal()
        ) {
            $rateForItem = $baseItemPrice / $spendingData->getBaseItemsTotal();
            $itemBaseRewardPointsAmount =
                ($spendingData->getBaseAvailablePointsAmount() - $baseShippingPointAmount) * $rateForItem;

            $rateForItem = $itemPrice / $spendingData->getItemsTotal();
            $itemRewardPointsAmount =
                ($spendingData->getAvailablePointsAmount() - $shippingPointAmount) * $rateForItem;

            $spendingData->setItemsCount($spendingData->getItemsCount() - 1);
        }

        $rewardPointsAmount = min($itemRewardPointsAmount, $itemPrice);
        $baseRewardPointsAmount = min($itemBaseRewardPointsAmount, $baseItemPrice);
        $rewardPoints = $this->rateCalculator->calculateSpendPoints($customerId, $baseRewardPointsAmount, $websiteId);
        $rewardPoints = min($rewardPoints, $spendingData->getAvailablePointsLeft());

        $item->setAwRewardPointsAmount($rewardPointsAmount);
        $item->setBaseAwRewardPointsAmount($baseRewardPointsAmount);
        $item->setAwRewardPoints($rewardPoints);

        $spendingData->setUsedPoints(
            $spendingData->getUsedPoints() + $rewardPoints
        );
        $spendingData->setUsedPointsAmount(
            $spendingData->getUsedPointsAmount() + $rewardPointsAmount
        );
        $spendingData->setBaseUsedPointsAmount(
            $spendingData->getBaseUsedPointsAmount() + $baseRewardPointsAmount
        );
    }

    /**
     * Retrieve point amount fot shipment
     *
     * @param int $websiteId
     * @param float $itemsTotal
     * @param float $shippingAmount
     * @param float $availablePointsAmount
     * @return float|int
     */
    private function getAvailableShipmentPointsAmount($websiteId, $itemsTotal, $shippingAmount, $availablePointsAmount)
    {
        if (!$shippingAmount) {
            return 0;
        }

        $shippingPointAmount = $shippingAmount;
        if ($shareCoveredValue = $this->config->getShareCoveredValue($websiteId)) {
            $shippingPointAmount *= $shareCoveredValue / 100;
            $itemsTotal *= $shareCoveredValue / 100;
        }

        if ($availablePointsAmount - $itemsTotal < 0) {
            $shippingPointAmount = 0;
        } elseif ($availablePointsAmount - $itemsTotal - $shippingAmount < 0) {
            $shippingPointAmount = $availablePointsAmount - $itemsTotal;
        }

        return $shippingPointAmount;
    }
}

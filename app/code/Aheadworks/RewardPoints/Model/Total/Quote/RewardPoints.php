<?php
namespace Aheadworks\RewardPoints\Model\Total\Quote;

use Aheadworks\RewardPoints\Model\Calculator\Spending as SpendingCalculator;
use Aheadworks\RewardPoints\Model\Calculator\Spending\Checker as SpendingChecker;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;
use Aheadworks\RewardPoints\Model\Service\RewardPointsCartService\SpendingData\Provider
    as SpendingDataProvider;

/**
 * Class Aheadworks\RewardPoints\Model\Quote\Address\Total\RewardPoints
 */
class RewardPoints extends AbstractTotal
{
    /**
     * @var SpendingCalculator
     */
    private $spendingCalculator;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SpendingDataProvider
     */
    private $spendingDataProvider;

    /**
     * @var SpendingChecker
     */
    private $spendingChecker;

    /**
     * @param SpendingCalculator $spendingCalculator
     * @param StoreManagerInterface $storeManager
     * @param SpendingDataProvider $spendingDataProvider
     * @param SpendingChecker $spendingChecker
     */
    public function __construct(
        SpendingCalculator $spendingCalculator,
        StoreManagerInterface $storeManager,
        SpendingDataProvider $spendingDataProvider,
        SpendingChecker $spendingChecker
    ) {
        $this->setCode('aw_reward_points');
        $this->spendingCalculator = $spendingCalculator;
        $this->storeManager = $storeManager;
        $this->spendingDataProvider = $spendingDataProvider;
        $this->spendingChecker = $spendingChecker;
    }

    /**
     *  {@inheritDoc}
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $pointsQtyToApply = $quote->getAwRewardPointsQtyToApply();
        $websiteId = $this->storeManager->getStore($quote->getStoreId())->getWebsiteId();
        $customerId = $quote->getCustomerId();

        $address = $shippingAssignment->getShipping()->getAddress();
        $items = $shippingAssignment->getItems();

        if (!count($items)) {
            return $this;
        }

        $this->reset($total, $quote, $address, $items);

        if (!$customerId || !$quote->getAwUseRewardPoints()) {
            $quote->setAwUseRewardPoints(false);
            $quote->setAwRewardPointsQtyToApply(0);
            $this->reset($total, $quote, $address, $items);
            return $this;
        }

        $rewardPointsData = $this->spendingDataProvider->getDataByShippingAssignment(
            $quote,
            $shippingAssignment,
            $pointsQtyToApply
        );
        if (!$rewardPointsData->getAvailablePoints()) {
            $quote->setAwUseRewardPoints(false);
            $quote->setAwRewardPointsQtyToApply(0);
            $this->reset($total, $quote, $address, $items);
            return $this;
        }

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($items as $item) {
            if (!$this->spendingChecker->canSpendRewardPointsOnQuoteItem($item)) {
                continue;
            }
            // To determine the child item discount, we calculate the parent
            if ($item->getParentItem()) {
                continue;
            }

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                $this->spendingCalculator->process($rewardPointsData, $item, $customerId, $websiteId);
                $this->spendingCalculator->distributeRewardPoints($item, $customerId, $websiteId);
            } else {
                $this->spendingCalculator->process($rewardPointsData, $item, $customerId, $websiteId);
            }
        }

        $this->spendingCalculator->processShipping($rewardPointsData, $address, $customerId, $websiteId);
        $this->addRewardPointsToTotal(
            $rewardPointsData->getUsedPointsAmount(),
            $rewardPointsData->getBaseUsedPointsAmount(),
            $rewardPointsData->getUsedPoints(),
            __('%1 Reward Points', $rewardPointsData->getUsedPoints())
        );

        $total->setSubtotalWithDiscount(
            $total->getSubtotal() + $total->getAwRewardPointsAmount()
        );
        $total->setBaseSubtotalWithDiscount(
            $total->getBaseSubtotal() + $total->getBaseAwRewardPointsAmount()
        );

        $quote->setAwRewardPointsAmount($total->getAwRewardPointsAmount());
        $quote->setBaseAwRewardPointsAmount($total->getBaseAwRewardPointsAmount());
        $quote->setAwRewardPoints($total->getAwRewardPoints());
        $quote->setAwRewardPointsDescription($total->getAwRewardPointsDescription());

        $address->setAwUseRewardPoints($quote->getAwUseRewardPoints());
        $address->setAwRewardPointsAmount($total->getAwRewardPointsAmount());
        $address->setBaseAwRewardPointsAmount($total->getBaseAwRewardPointsAmount());
        $address->setAwRewardPoints($total->getAwRewardPoints());
        $address->setAwRewardPointsDescription($total->getAwRewardPointsDescription());

        return $this;
    }

    /**
     *  {@inheritDoc}
     */
    public function fetch(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $result = null;
        $amount = $total->getAwRewardPointsAmount();

        if ($amount != 0) {
            $description = $total->getAwRewardPointsDescription();
            $result = [
                'code' => $this->getCode(),
                'title' => strlen($description) ? __($description)
                    : __('%1 Reward Points', $total->getAwRewardPoints()),
                'value' => $amount,
            ];
        }
        return $result;
    }

    /**
     * Reset reward points total
     *
     * @param Total $total
     * @param Quote $quote
     * @param AddressInterface $address
     * @param \Magento\Quote\Api\Data\CartItemInterface[] $items
     * @return RewardPoints
     */
    private function reset(Total $total, Quote $quote, AddressInterface $address, $items)
    {
        $this->_addAmount(0);
        $this->_addBaseAmount(0);

        $total->setAwRewardPoints(0);
        $total->setAwRewardPointsDescription('');

        $quote->setAwRewardPointsAmount(0);
        $quote->setBaseAwRewardPointsAmount(0);
        $quote->setAwRewardPoints(0);
        $quote->setAwRewardPointsDescription('');

        $address->setAwUseRewardPoints(false);
        $address->setAwRewardPointsAmount(0);
        $address->setBaseAwRewardPointsAmount(0);
        $address->setAwRewardPoints(0);
        $address->setAwRewardPointsDescription('');
        $address->setAwRewardPointsShippingAmount(0);
        $address->setBaseAwRewardPointsShippingAmount(0);
        $address->setAwRewardPointsShipping(0);

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($items as $item) {
            $item->setAwRewardPointsAmount(0);
            $item->setBaseAwRewardPointsAmount(0);
            $item->setAwRewardPoints(0);

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $child->setAwRewardPointsAmount(0);
                    $child->setBaseAwRewardPointsAmount(0);
                    $child->setAwRewardPoints(0);
                }
            }
        }
        return $this;
    }

    /**
     * Add reward points
     *
     * @param  float $rewardPointsAmount
     * @param  float $baseRewardPointsAmount
     * @param  int $pointsForUse
     * @param  string $description
     * @return RewardPoints
     */
    private function addRewardPointsToTotal(
        $rewardPointsAmount,
        $baseRewardPointsAmount,
        $pointsForUse,
        $description
    ) {
        $this->_addAmount(-$rewardPointsAmount);
        $this->_addBaseAmount(-$baseRewardPointsAmount);

        $this->_getTotal()->setAwRewardPoints($pointsForUse);
        $this->_getTotal()->setAwRewardPointsDescription($description);
        return $this;
    }
}

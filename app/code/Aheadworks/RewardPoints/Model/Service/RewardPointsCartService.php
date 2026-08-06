<?php
namespace Aheadworks\RewardPoints\Model\Service;

use Aheadworks\RewardPoints\Api\RewardPointsCartManagementInterface;
use Aheadworks\RewardPoints\Api\CustomerRewardPointsManagementInterface;
use Aheadworks\RewardPoints\Model\Config;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Aheadworks\RewardPoints\Model\Service\RewardPointsCartService\SpendingData\Provider
    as SpendingDataProvider;
use Magento\Quote\Model\Quote\TotalsCollector as QuoteTotalsCollector;
use Magento\Quote\Model\Quote;
use Aheadworks\RewardPoints\Api\Data\CustomerCartMetadataInterface;
use Aheadworks\RewardPoints\Api\Data\CustomerCartMetadataInterfaceFactory;

/**
 * Class Aheadworks\RewardPoints\Model\Service$RewardPointsCartService
 */
class RewardPointsCartService implements RewardPointsCartManagementInterface
{
    /**
     * Quote data flag to check if quote totals have been already collected before points statistics calculation
     */
    const ARE_QUOTE_TOTALS_COLLECTED_FLAG = 'are_quote_totals_collected_flag';

    /**
     * @var CustomerRewardPointsManagementInterface
     */
    private $customerRewardPointsService;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SpendingDataProvider
     */
    private $spendingDataProvider;

    /**
     * @var QuoteTotalsCollector
     */
    private $quoteTotalsCollector;

    /**
     * @var CustomerCartMetadataInterfaceFactory
     */
    private $customerCartMetadataFactory;

    /**
     * @param CustomerRewardPointsManagementInterface $customerRewardPointsService
     * @param CartRepositoryInterface $quoteRepository
     * @param Config $config
     * @param SpendingDataProvider $spendingDataProvider
     * @param QuoteTotalsCollector $quoteTotalsCollector
     * @param CustomerCartMetadataInterfaceFactory $customerCartMetadataFactory
     */
    public function __construct(
        CustomerRewardPointsManagementInterface $customerRewardPointsService,
        CartRepositoryInterface $quoteRepository,
        Config $config,
        SpendingDataProvider $spendingDataProvider,
        QuoteTotalsCollector $quoteTotalsCollector,
        CustomerCartMetadataInterfaceFactory $customerCartMetadataFactory
    ) {
        $this->customerRewardPointsService = $customerRewardPointsService;
        $this->quoteRepository = $quoteRepository;
        $this->config = $config;
        $this->spendingDataProvider = $spendingDataProvider;
        $this->quoteTotalsCollector = $quoteTotalsCollector;
        $this->customerCartMetadataFactory = $customerCartMetadataFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function get($cartId)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }
        return $quote->getAwUseRewardPoints();
    }

    /**
     * {@inheritDoc}
     */
    public function set($cartId, $pointsQty)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }

        $onceMinBalance = $this->customerRewardPointsService->getCustomerRewardPointsOnceMinBalance(
            $quote->getCustomerId(),
            $quote->getStore()->getWebsiteId()
        );
        if (!$quote->getCustomerId()
            || !$this->customerRewardPointsService->getCustomerRewardPointsBalance($quote->getCustomerId())
            || $onceMinBalance
        ) {
            throw new NoSuchEntityException(__('No reward points to be used'));
        }

        $quote->getShippingAddress()->setCollectShippingRates(true);
        try {
            $quote->setAwUseRewardPoints(true);
            $quote->setAwRewardPointsQtyToApply($pointsQty);
            $this->quoteRepository->save($quote->collectTotals());
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not apply reward points'));
        }

        if (!$quote->getAwUseRewardPoints()) {
            throw new NoSuchEntityException(__('No possibility to use reward points discounts in the cart'));
        }

        return [
            CustomAttributesDataInterface::CUSTOM_ATTRIBUTES => [
                'success' => true,
                'message' => $this->getMessage($quote)
            ]
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function remove($cartId)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }

        $quote->getShippingAddress()->setCollectShippingRates(true);
        try {
            $quote->setAwUseRewardPoints(false);
            $quote->setAwRewardPointsQtyToApply(0);
            $this->quoteRepository->save($quote->collectTotals());
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not remove reward points'));
        }
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getCustomerCartMetadata($customerId, $cartId)
    {
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $websiteId = $quote->getStore()->getWebsiteId();

        if (!$quote->getData(self::ARE_QUOTE_TOTALS_COLLECTED_FLAG)) {
            $this->quoteTotalsCollector->collect($quote);
            $quote->setData(self::ARE_QUOTE_TOTALS_COLLECTED_FLAG, true);
        }

        $spendingData = $this->spendingDataProvider->getDataByQuote($quote);

        $customerRewardPointsDetails = $this->customerRewardPointsService->getCustomerRewardPointsDetails(
            $customerId,
            $websiteId
        );

        return $this->customerCartMetadataFactory->create(
            [
                'data' => [
                    CustomerCartMetadataInterface::REWARD_POINTS_BALANCE_QTY =>
                        $customerRewardPointsDetails->getCustomerRewardPointsBalance(),
                    CustomerCartMetadataInterface::CAN_APPLY_REWARD_POINTS =>
                        ($customerRewardPointsDetails->getCustomerRewardPointsOnceMinBalance() == 0)
                        && $customerRewardPointsDetails->isCustomerRewardPointsSpendRateByGroup()
                        && $customerRewardPointsDetails->isCustomerRewardPointsSpendRate()
                        && ($spendingData->getAvailablePoints() > 0),
                    CustomerCartMetadataInterface::REWARD_POINTS_MAX_ALLOWED_QTY_TO_APPLY =>
                        $spendingData->getAvailablePoints(),
                    CustomerCartMetadataInterface::REWARD_POINTS_CONVERSION_RATE_POINT_TO_CURRENCY_VALUE =>
                        $customerRewardPointsDetails->getCustomerConversionRatePointToCurrencyValue(),
                    CustomerCartMetadataInterface::ARE_REWARD_POINTS_APPLIED =>
                        ($spendingData->getUsedPoints() > 0),
                    CustomerCartMetadataInterface::APPLIED_REWARD_POINTS_QTY =>
                        $spendingData->getUsedPoints(),
                    CustomerCartMetadataInterface::APPLIED_REWARD_POINTS_AMOUNT =>
                        $spendingData->getUsedPointsAmount(),
                ]
            ]
        );
    }

    /**
     * Retrieves message to show customer
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return string
     */
    private function getMessage($quote)
    {
        $shareCoveredValue = $this->config->getShareCoveredValue($quote->getStore()->getWebsiteId());
        if ($shareCoveredValue && ($shareCoveredValue != 100)) {
            $message = __(
                'Reward points were successfully applied. '
                . 'Important: It is allowed to cover only %1% of the purchase with Reward Points.',
                $shareCoveredValue
            );
        } else {
            $message = __('Reward points were successfully applied.');
        }
        return $message;
    }
}

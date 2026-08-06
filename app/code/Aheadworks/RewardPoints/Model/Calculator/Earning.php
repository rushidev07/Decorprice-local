<?php
namespace Aheadworks\RewardPoints\Model\Calculator;

use Aheadworks\RewardPoints\Model\Calculator\Earning\Calculator;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItem;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemsResolver;
use Aheadworks\RewardPoints\Model\Calculator\Earning\Predictor;
use Aheadworks\RewardPoints\Model\Source\Calculation\PointsEarning;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Aheadworks\RewardPoints\Model\Config;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class Earning
 *
 * @package Aheadworks\RewardPoints\Model\Calculator
 */
class Earning
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var EarnItemsResolver
     */
    private $earnItemsResolver;

    /**
     * @var Calculator
     */
    private $calculator;

    /**
     * @var Predictor
     */
    private $predictor;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Config $config
     * @param EarnItemsResolver $earnItemsResolver
     * @param Calculator $calculator
     * @param Predictor $predictor
     * @param StoreManagerInterface $storeManager
     * @param Logger $logger
     */
    public function __construct(
        Config $config,
        EarnItemsResolver $earnItemsResolver,
        Calculator $calculator,
        Predictor $predictor,
        StoreManagerInterface $storeManager,
        Logger $logger
    ) {
        $this->config = $config;
        $this->earnItemsResolver = $earnItemsResolver;
        $this->calculator = $calculator;
        $this->predictor = $predictor;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Retrieve calculation earning points value by quote
     *
     * @param CartInterface|Quote $quote
     * @param int|null $customerId
     * @param int|null $websiteId
     * @return ResultInterface
     */
    public function calculationByQuote($quote, $customerId, $websiteId = null)
    {
        $websiteId = $websiteId ? $websiteId : $this->getCurrentWebsiteId();
        if (!$websiteId) {
            return $this->calculator->getEmptyResult();
        }
        $beforeTax = $this->config->getPointsEarningCalculation($websiteId) == PointsEarning::BEFORE_TAX;
        try {
            /** @var EarnItem[] $items */
            $items = $this->earnItemsResolver->getItemsByQuote($quote, $beforeTax);
            if (!$customerId) {
                $customerGroupId = $this->config->getDefaultCustomerGroupIdForGuest();
                $result = $this->calculator->calculateByCustomerGroup($items, $customerGroupId, $websiteId);
            } else {
                $result = $this->calculator->calculate($items, $customerId, $websiteId);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $result = $this->calculator->getEmptyResult();
        }

        return $result;
    }

    /**
     * Retrieve calculation earning points value by invoice
     *
     * @param InvoiceInterface $invoice
     * @param int $customerId
     * @param int|null $websiteId
     * @return ResultInterface
     */
    public function calculationByInvoice($invoice, $customerId, $websiteId = null)
    {
        $websiteId = $websiteId ? $websiteId : $this->getCurrentWebsiteId();
        if (!$websiteId) {
            return $this->calculator->getEmptyResult();
        }
        $beforeTax = $this->config->getPointsEarningCalculation($websiteId) == PointsEarning::BEFORE_TAX;
        try {
            /** @var EarnItem[] $items */
            $items = $this->earnItemsResolver->getItemsByInvoice($invoice, $beforeTax);
            $result = $this->calculator->calculate($items, $customerId, $websiteId);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $result = $this->calculator->getEmptyResult();
        }

        return $result;
    }

    /**
     * Retrieve calculation earning points value by credit memo
     *
     * @param CreditmemoInterface $creditmemo
     * @param int $customerId
     * @param int|null $websiteId
     * @return ResultInterface
     */
    public function calculationByCreditmemo($creditmemo, $customerId, $websiteId = null)
    {
        $websiteId = $websiteId ? $websiteId : $this->getCurrentWebsiteId();
        if (!$websiteId) {
            return $this->calculator->getEmptyResult();
        }
        $beforeTax = $this->config->getPointsEarningCalculation($websiteId) == PointsEarning::BEFORE_TAX;
        try {
            /** @var EarnItem[] $items */
            $items = $this->earnItemsResolver->getItemsByCreditmemo($creditmemo, $beforeTax);
            $result = $this->calculator->calculate($items, $customerId, $websiteId);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $result = $this->calculator->getEmptyResult();
        }

        return $result;
    }

    /**
     * Retrieve calculation earning points value by product.
     *
     * @param ProductInterface $product
     * @param bool $mergeRuleIds
     * @param int|null $customerId
     * @param int|null $websiteId
     * @param int|null $customerGroupId
     * @return ResultInterface
     */
    public function calculationByProduct(
        $product,
        $mergeRuleIds,
        $customerId,
        $websiteId = null,
        $customerGroupId = null
    ) {
        $websiteId = $websiteId ? $websiteId : $this->getCurrentWebsiteId();
        if (!$websiteId) {
            return $this->calculator->getEmptyResult();
        }
        $beforeTax = $this->config->getPointsEarningCalculation($websiteId) == PointsEarning::BEFORE_TAX;
        try {
            /** @var EarnItem[] $items */
            $items = $this->earnItemsResolver->getItemsByProduct($product, $beforeTax);
            if (isset($customerGroupId)) {
                $result = $this->predictor->calculateMaxPointsForCustomerGroup(
                    $items,
                    $websiteId,
                    $customerGroupId,
                    $mergeRuleIds
                );
            } elseif ($customerId) {
                $result = $this->predictor->calculateMaxPointsForCustomer(
                    $items,
                    $customerId,
                    $websiteId,
                    $mergeRuleIds
                );
            } else {
                $result = $this->predictor->calculateMaxPointsForGuest($items, $websiteId, $mergeRuleIds);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $result = $this->calculator->getEmptyResult();
        }

        return $result;
    }

    /**
     * Get current website
     *
     * @return int|null
     */
    private function getCurrentWebsiteId()
    {
        try {
            $currentWebsite = $this->storeManager->getWebsite();
        } catch (LocalizedException $e) {
            return null;
        }
        return $currentWebsite->getId();
    }
}

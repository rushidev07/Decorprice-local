<?php
namespace Aheadworks\RewardPoints\Block\ProductList;

use Aheadworks\RewardPoints\Model\Config;
use Aheadworks\RewardPoints\Model\EarnRule\CategoryPromoTextResolver;
use Aheadworks\RewardPoints\Model\EarnRule\Applier;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Store\Model\StoreManagerInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning as EarningCalculator;
use Aheadworks\RewardPoints\Model\Calculator\ResultInterface;

/**
 * Class CategoryText
 *
 * @method Product|ProductInterface|null getProduct()
 *
 * @package Aheadworks\RewardPoints\Block\Product
 */
class CategoryText extends Template
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_RewardPoints::product/list/earning.phtml';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var CategoryPromoTextResolver
     */
    private $categoryPromoTextResolver;

    /**
     * @var Applier
     */
    private $ruleApplier;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EarningCalculator
     */
    private $earningCalculator;

    /**
     * @var string
     */
    private $promoText;

    /**
     * @var ResultInterface
     */
    private $calculationResult;

    /**
     * @param Context $context
     * @param Config $config
     * @param HttpContext $httpContext
     * @param CategoryPromoTextResolver $categoryPromoTextResolver
     * @param Applier $ruleApplier
     * @param StoreManagerInterface $storeManager
     * @param EarningCalculator $earningCalculator
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        HttpContext $httpContext,
        CategoryPromoTextResolver $categoryPromoTextResolver,
        Applier $ruleApplier,
        StoreManagerInterface $storeManager,
        EarningCalculator $earningCalculator,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->httpContext = $httpContext;
        $this->categoryPromoTextResolver = $categoryPromoTextResolver;
        $this->ruleApplier = $ruleApplier;
        $this->storeManager = $storeManager;
        $this->earningCalculator = $earningCalculator;
    }

    /**
     * Check if block should be displayed
     *
     * @return bool
     */
    public function isDisplayBlock()
    {
        $text = $this->getPromoText();

        return $text && $this->getMaxPossibleEarningPoints() > 0;
    }

    /**
     * Get max possible earning points
     *
     * @return int
     */
    private function getMaxPossibleEarningPoints()
    {
        $maxPossibleEarningPoints = 0;

        $product = $this->getProduct();
        if ($product) {
            $result = $this->getCalculationResult($product);
            $maxPossibleEarningPoints = $result->getPoints();
        }

        return $maxPossibleEarningPoints;
    }

    /**
     * Get calculation result
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return ResultInterface
     */
    private function getCalculationResult($product)
    {
        if (!$this->calculationResult) {
            $customerGroupId = $this->getCustomerGroupId();
            /** @var ResultInterface $result */
            $this->calculationResult = $this->earningCalculator->calculationByProduct(
                $product,
                true,
                null,
                null,
                $customerGroupId
            );
        }
        return $this->calculationResult;
    }

    /**
     * Get promo text
     *
     * @return string
     */
    public function getPromoText()
    {
        if ($this->promoText == null) {
            $this->promoText = $this->categoryPromoTextResolver->getPromoText(
                $this->getAppliedRuleIds(),
                $this->getCurrentStoreId()
            );
        }

        return $this->promoText;
    }

    /**
     * Get applied rule ids
     *
     * @return int[]
     */
    public function getAppliedRuleIds()
    {
        $appliedRuleIds = [];
        $product = $this->getProduct();
        $websiteId = $this->getCurrentWebsiteId();
        if ($product && $websiteId) {
            $customerGroupId = $this->getCustomerGroupId();
            $appliedRuleIds = $this->ruleApplier->getAppliedRuleIds($product->getId(), $customerGroupId, $websiteId);
        }

        return $appliedRuleIds;
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

    /**
     * Get current store
     *
     * @return int|null
     */
    private function getCurrentStoreId()
    {
        try {
            $currentStore = $this->storeManager->getStore();
        } catch (LocalizedException $e) {
            return null;
        }
        return $currentStore->getId();
    }

    /**
     * Get group id for current customer
     *
     * @return int
     */
    private function getCustomerGroupId()
    {
        $loggedId = $this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
        $customerGroupId = $loggedId
            ? $this->httpContext->getValue(CustomerContext::CONTEXT_GROUP)
            : $this->config->getDefaultCustomerGroupIdForGuest();
        return $customerGroupId;
    }
}

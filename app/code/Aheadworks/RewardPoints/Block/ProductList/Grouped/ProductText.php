<?php
namespace Aheadworks\RewardPoints\Block\ProductList\Grouped;

use Aheadworks\RewardPoints\Model\Calculator\ResultInterface;
use Aheadworks\RewardPoints\Model\Config;
use Aheadworks\RewardPoints\Model\EarnRule\Applier;
use Aheadworks\RewardPoints\Model\EarnRule\ProductPromoTextResolver;
use Aheadworks\RewardPoints\Model\Calculator\Earning as EarningCalculator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Phrase;

/**
 * Class ProductText
 *
 * @method Product|ProductInterface|null getProduct()
 *
 * @package Aheadworks\RewardPoints\Block\Product
 */
class ProductText extends Template
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
     * @var EarningCalculator
     */
    private $earningCalculator;

    /**
     * @var ProductPromoTextResolver
     */
    private $productPromoTextResolver;

    /**
     * @var Applier
     */
    private $ruleApplier;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

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
     * @param EarningCalculator $earningCalculator
     * @param ProductPromoTextResolver $productPromoTextResolver
     * @param Applier $ruleApplier
     * @param CustomerSession $customerSession
     * @param HttpContext $httpContext
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        EarningCalculator $earningCalculator,
        ProductPromoTextResolver $productPromoTextResolver,
        Applier $ruleApplier,
        CustomerSession $customerSession,
        HttpContext $httpContext,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->earningCalculator = $earningCalculator;
        $this->productPromoTextResolver = $productPromoTextResolver;
        $this->ruleApplier = $ruleApplier;
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
        $this->storeManager = $storeManager;
    }

    /**
     * Check if block should be displayed
     *
     * @return bool
     */
    public function isDisplayBlock()
    {
        $text = $this->getPromoText();

        return !empty($text->getText()) && $this->getMaxPossibleEarningPoints() > 0 && $this->isAjax();
    }

    /**
     * Get promo text
     *
     * @return Phrase
     */
    public function getPromoText()
    {
        if ($this->promoText == null) {
            $points = $this->getMaxPossibleEarningPoints();
            $text = $this->productPromoTextResolver->getPromoText(
                $this->getAppliedRuleIds(),
                $this->getCurrentStoreId(),
                $this->httpContext->getValue(CustomerContext::CONTEXT_AUTH)
            );
            $this->promoText = __($text, ['X' => $points]);
        }

        return $this->promoText;
    }

    /**
     * Get max possible earning points for current product
     *
     * @return int
     */
    public function getMaxPossibleEarningPoints()
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
     * Is ajax request or not
     *
     * @return bool
     */
    public function isAjax()
    {
        return $this->_request->isAjax();
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
     * Get calculation result
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return ResultInterface
     */
    private function getCalculationResult($product)
    {
        if (!$this->calculationResult) {
            $customerId = $this->customerSession->getCustomerId();
            if ($customerId) {
                /** @var ResultInterface $result */
                $this->calculationResult = $this->earningCalculator->calculationByProduct(
                    $product,
                    true,
                    $customerId
                );
            } else {
                /** @var ResultInterface $result */
                $this->calculationResult = $this->earningCalculator->calculationByProduct(
                    $product,
                    true,
                    null,
                    null,
                    $this->getCustomerGroupId()
                );
            }
        }
        return $this->calculationResult;
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

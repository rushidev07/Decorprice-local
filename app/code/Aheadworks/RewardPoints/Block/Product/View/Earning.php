<?php
namespace Aheadworks\RewardPoints\Block\Product\View;

use Aheadworks\RewardPoints\Model\Calculator\Earning as EarningCalculator;
use Aheadworks\RewardPoints\Model\Calculator\ResultInterface;
use Aheadworks\RewardPoints\Model\EarnRule\ProductPromoTextResolver;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Phrase;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Aheadworks\RewardPoints\Block\Product\View\Earning
 */
class Earning extends \Magento\Framework\View\Element\Template
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_RewardPoints::product/view/earning.phtml';

    /**
     * @var EarningCalculator
     */
    private $earningCalculator;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductPromoTextResolver
     */
    private $productPromoTextResolver;

    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @var ResultInterface
     */
    private $calculationResult;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param EarningCalculator $earningCalculator
     * @param CustomerSession $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param ProductPromoTextResolver $productPromoTextResolver
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        EarningCalculator $earningCalculator,
        CustomerSession $customerSession,
        ProductRepositoryInterface $productRepository,
        ProductPromoTextResolver $productPromoTextResolver,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->earningCalculator = $earningCalculator;
        $this->customerSession = $customerSession;
        $this->productRepository = $productRepository;
        $this->productPromoTextResolver = $productPromoTextResolver;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
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
     * Check if block should be displayed
     *
     * @return bool
     */
    public function isDisplayBlock()
    {
        $text = $this->productPromoTextResolver->getPromoText(
            $this->getAppliedRuleIds(),
            $this->getCurrentStoreId(),
            $this->customerSession->isLoggedIn()
        );

        return $text && $this->getMaxPossibleEarningPoints() > 0;
    }

    /**
     * Get promo text
     *
     * @return Phrase
     */
    public function getPromoText()
    {
        $points = $this->getMaxPossibleEarningPoints();
        $text = $this->productPromoTextResolver->getPromoText(
            $this->getAppliedRuleIds(),
            $this->getCurrentStoreId(),
            $this->customerSession->isLoggedIn()
        );

        return __($text, ['X' => $points]);
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
     * Get applied rule ids for current product
     *
     * @return int[]
     */
    public function getAppliedRuleIds()
    {
        $appliedRuleIds = [];

        $product = $this->getProduct();
        if ($product) {
            $result = $this->getCalculationResult($product);
            $appliedRuleIds = $result->getAppliedRuleIds();
        }

        return $appliedRuleIds;
    }

    /**
     * Retrieve current product id
     *
     * @return ProductInterface|false
     */
    private function getProduct()
    {
        if ($this->product == null) {
            $productId = $this->getRequest()->getParam('product_id', null)
                ? $this->getRequest()->getParam('product_id')
                : $this->getRequest()->getParam('id');

            try {
                $this->product = $this->productRepository->getById($productId);
            } catch (NoSuchEntityException $e) {
                $this->product = false;
            }
        }
        return $this->product;
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
            /** @var ResultInterface $result */
            $this->calculationResult = $this->earningCalculator->calculationByProduct(
                $product,
                true,
                $customerId
            );
        }
        return $this->calculationResult;
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
}

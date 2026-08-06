<?php
namespace Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor;

use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Model\Product;

/**
 * Class CatalogPriceCalculator
 * @package Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor
 */
class CatalogPriceCalculator
{
    /**
     * @var CatalogHelper
     */
    private $catalogHelper;

    /**
     * @param CatalogHelper $catalogHelper
     */
    public function __construct(
        CatalogHelper $catalogHelper
    ) {
        $this->catalogHelper = $catalogHelper;
    }
    /**
     * Get final price
     *
     * @param Product $product
     * @param float $price
     * @param bool $exclTax
     * @return float
     */
    public function getFinalPriceAmount($product, $price, $exclTax = true)
    {
        $includingTax = !$exclTax;
        $finalPrice = $this->catalogHelper->getTaxPrice(
            $product,
            $price,
            $includingTax,
            null,
            null,
            null,
            null,
            null,
            true
        );

        return $finalPrice;
    }
}

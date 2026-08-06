<?php
namespace Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor\TypeProcessor;

use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor\CatalogPriceCalculator;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor\TypeProcessorInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemInterfaceFactory;

/**
 * Class Simple
 * @package Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor\TypeProcessor
 */
class Simple implements TypeProcessorInterface
{
    /**
     * @var EarnItemInterfaceFactory
     */
    private $earnItemFactory;

    /**
     * @var CatalogPriceCalculator
     */
    private $catalogPriceCalculator;

    /**
     * @param EarnItemInterfaceFactory $earnItemFactory
     * @param CatalogPriceCalculator $catalogPriceCalculator
     */
    public function __construct(
        EarnItemInterfaceFactory $earnItemFactory,
        CatalogPriceCalculator $catalogPriceCalculator
    ) {
        $this->earnItemFactory = $earnItemFactory;
        $this->catalogPriceCalculator = $catalogPriceCalculator;
    }

    /**
     * {@inheritdoc}
     */
    public function getEarnItems($product, $beforeTax = true)
    {
        $price = $this->catalogPriceCalculator->getFinalPriceAmount(
            $product,
            $product->getFinalPrice(),
            $beforeTax
        );

        $earnItems = [];

        /** @var EarnItemInterface $earnItem */
        $earnItem = $this->earnItemFactory->create();
        $earnItem
            ->setProductId($product->getId())
            ->setBaseAmount($price)
            ->setQty(1);

        $earnItems[] = $earnItem;

        return $earnItems;
    }
}

<?php
namespace Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor\TypeProcessor;

use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor\CatalogPriceCalculator;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor\TypeProcessorInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemInterfaceFactory;

/**
 * Class Configurable
 * @package Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor\TypeProcessor
 */
class Configurable implements TypeProcessorInterface
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
        $earnItems = [];

        $children = $product->getTypeInstance()
            ->getUsedProducts($product);

        foreach ($children as $child) {
            /** @var EarnItemInterface $earnItem */
            $earnItem = $this->earnItemFactory->create();

            $price = $this->catalogPriceCalculator->getFinalPriceAmount(
                $child,
                $child->getFinalPrice(),
                $beforeTax
            );

            $earnItem
                ->setProductId($child->getId())
                ->setBaseAmount($price)
                ->setQty(1);

            $earnItems[] = $earnItem;
        }
        
        return $earnItems;
    }
}

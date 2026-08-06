<?php
namespace Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemProcessor;

use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemInterfaceFactory;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemProcessorInterface;

/**
 * Class Configurable
 * @package Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemProcessor
 */
class Configurable implements ItemProcessorInterface
{
    /**
     * @var EarnItemInterfaceFactory
     */
    private $earnItemFactory;

    /**
     * @param EarnItemInterfaceFactory $earnItemFactory
     */
    public function __construct(
        EarnItemInterfaceFactory $earnItemFactory
    ) {
        $this->earnItemFactory = $earnItemFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getEarnItem($groupedItems, $beforeTax = true)
    {
        /** @var EarnItemInterface $earnItem */
        $earnItem = $this->earnItemFactory->create();

        /** @var ItemInterface $parenItem */
        $parenItem = $this->getParentItem($groupedItems);
        /** @var ItemInterface $childItem */
        $childItem = $this->getChildItem($groupedItems);
        if ($parenItem && $childItem) {
            $baseSubtotal = $beforeTax ? $parenItem->getBaseRowTotal() : $parenItem->getBaseRowTotalInclTax();
            $discount = $parenItem->getBaseDiscountAmount() + $parenItem->getBaseAwRewardPointsAmount();
            $baseSubtotal -= $discount;

            $earnItem
                ->setProductId($childItem->getProductId())
                ->setBaseAmount($baseSubtotal)
                ->setQty($parenItem->getQty());
        } else {
            $earnItem
                ->setProductId(null)
                ->setBaseAmount(0)
                ->setQty(0);
        }

        return $earnItem;
    }

    /**
     * Get parent item
     *
     * @param ItemInterface[] $items
     * @return ItemInterface|null
     */
    private function getParentItem($items)
    {
        foreach ($items as $item) {
            if ($item->getParentItem() == null) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Get child item
     *
     * @param ItemInterface[] $items
     * @return ItemInterface|null
     */
    private function getChildItem($items)
    {
        foreach ($items as $item) {
            if ($item->getParentItem() != null) {
                return $item;
            }
        }
        return null;
    }
}

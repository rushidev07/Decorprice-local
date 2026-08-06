<?php
namespace Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemProcessor;

use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemInterfaceFactory;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemProcessorInterface;

/**
 * Class Simple
 * @package Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemProcessor
 */
class Simple implements ItemProcessorInterface
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

        /** @var ItemInterface $item */
        $item = reset($groupedItems);
        if ($item) {
            $baseSubtotal = $beforeTax ? $item->getBaseRowTotal() : $item->getBaseRowTotalInclTax();
            $discount = $item->getBaseDiscountAmount() + $item->getBaseAwRewardPointsAmount();
            $baseSubtotal -= $discount;

            $earnItem
                ->setProductId($item->getProductId())
                ->setBaseAmount($baseSubtotal)
                ->setQty($item->getQty());
        } else {
            $earnItem
                ->setProductId(null)
                ->setBaseAmount(0)
                ->setQty(0);
        }

        return $earnItem;
    }
}

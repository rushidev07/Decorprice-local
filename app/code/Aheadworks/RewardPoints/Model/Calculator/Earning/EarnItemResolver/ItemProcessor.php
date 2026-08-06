<?php
namespace Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver;

use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemInterface;

/**
 * Class ItemProcessor
 * @package Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver
 */
class ItemProcessor implements ItemProcessorInterface
{
    /**
     * @var ItemProcessorPool
     */
    public $processorPool;

    /**
     * @param ItemProcessorPool $processorPool
     */
    public function __construct(
        ItemProcessorPool $processorPool
    ) {
        $this->processorPool = $processorPool;
    }

    /**
     * Get earn item
     *
     * @param ItemInterface[] $groupedItems
     * @param bool $beforeTax
     * @return EarnItemInterface
     * @throws \Exception
     */
    public function getEarnItem($groupedItems, $beforeTax = true)
    {
        $productType = null;
        foreach ($groupedItems as $item) {
            if ($item->getParentItem() == null) {
                $productType = $item->getProductType();
                break;
            }
        }

        /** @var ItemProcessorInterface $processor */
        $processor = $this->processorPool->getProcessorByCode($productType);

        return $processor->getEarnItem($groupedItems, $beforeTax);
    }
}

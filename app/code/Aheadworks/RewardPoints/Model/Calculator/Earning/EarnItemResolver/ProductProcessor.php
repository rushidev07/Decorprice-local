<?php
namespace Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver;

use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor\TypeProcessorPool;
use Magento\Catalog\Model\Product;

/**
 * Class ProductProcessor
 * @package Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver
 */
class ProductProcessor
{
    /**
     * @var TypeProcessorPool
     */
    private $typeProcessorPool;

    /**
     * @param TypeProcessorPool $typeProcessorPool
     */
    public function __construct(
        TypeProcessorPool $typeProcessorPool
    ) {
        $this->typeProcessorPool = $typeProcessorPool;
    }

    /**
     * Get earn items
     *
     * @param Product $product
     * @param $beforeTax
     * @return EarnItemInterface[]
     * @throws \Exception
     */
    public function getEarnItems($product, $beforeTax)
    {
        $typeProcessor = $this->typeProcessorPool->getProcessorByCode($product->getTypeId());

        return $typeProcessor->getEarnItems($product, $beforeTax);
    }
}

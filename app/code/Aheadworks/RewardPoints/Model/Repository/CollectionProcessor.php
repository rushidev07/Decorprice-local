<?php
namespace Aheadworks\RewardPoints\Model\Repository;

use Aheadworks\RewardPoints\Model\ResourceModel\AbstractCollection;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Exception\ConfigurationMismatchException;

/**
 * Class CollectionProcessor
 * @package Aheadworks\RewardPoints\Model\Repository
 */
class CollectionProcessor implements CollectionProcessorInterface
{
    /**
     * @var CollectionProcessorInterface[]
     */
    private $processors;

    /**
     * @param CollectionProcessorInterface[] $processors
     */
    public function __construct(
        $processors = []
    ) {
        $this->processors = $processors;
    }

    /**
     * Process collection
     *
     * @param SearchCriteria $searchCriteria
     * @param AbstractCollection $collection
     * @throws \Exception
     */
    public function process($searchCriteria, $collection)
    {
        foreach ($this->processors as $processor) {
            if (!$processor instanceof CollectionProcessorInterface) {
                throw new ConfigurationMismatchException(
                    __('Collection processor must implement %1', CollectionProcessorInterface::class)
                );
            }
            $processor->process($searchCriteria, $collection);
        }
    }
}

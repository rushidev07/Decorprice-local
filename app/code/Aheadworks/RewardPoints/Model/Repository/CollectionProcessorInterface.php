<?php
namespace Aheadworks\RewardPoints\Model\Repository;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection as FrameworkAbstractCollection;

/**
 * Interface CollectionProcessorInterface
 * @package Aheadworks\RewardPoints\Model\Repository
 */
interface CollectionProcessorInterface
{
    /**
     * Process collection
     *
     * @param SearchCriteria $searchCriteria
     * @param FrameworkAbstractCollection $collection
     * @return void
     */
    public function process($searchCriteria, $collection);
}

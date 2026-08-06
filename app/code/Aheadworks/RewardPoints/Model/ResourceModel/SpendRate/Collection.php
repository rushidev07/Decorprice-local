<?php
namespace Aheadworks\RewardPoints\Model\ResourceModel\SpendRate;

use Aheadworks\RewardPoints\Model\SpendRate;
use Aheadworks\RewardPoints\Model\ResourceModel\SpendRate as SpendRateResource;

/**
 * Class Aheadworks\RewardPoints\Model\ResourceModel\SpendRate\Collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(SpendRate::class, SpendRateResource::class);
    }

    /**
     * Retrieve array of items for configuration page
     *
     * @return array
     */
    public function toConfigDataArray()
    {
        $arrItems = [];
        foreach ($this as $item) {
            $arrItems[] = $item->toArray([]);
        }
        return $arrItems;
    }
}

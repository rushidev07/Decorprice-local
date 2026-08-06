<?php
namespace Aheadworks\RewardPoints\Model\ResourceModel\EarnRate;

use Aheadworks\RewardPoints\Model\EarnRate;
use Aheadworks\RewardPoints\Model\ResourceModel\EarnRate as EarnRateResource;

/**
 * Class Aheadworks\RewardPoints\Model\ResourceModel\EarnRate\Collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(EarnRate::class, EarnRateResource::class);
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

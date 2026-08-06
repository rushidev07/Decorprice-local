<?php
namespace Aheadworks\RewardPoints\Model\Source\Transaction;

use Magento\Framework\Option\ArrayInterface;
use Magento\Store\Model\System\Store as SystemStore;

/**
 * Class Aheadworks\RewardPoints\Model\Source\Transaction\Website
 */
class Website implements ArrayInterface
{
    /**
     * @var SystemStore
     */
    private $systemStore;

    /**
     * @param SystemStore $systemStore
     */
    public function __construct(
        SystemStore $systemStore
    ) {
        $this->systemStore = $systemStore;
    }

    /**
     * Return array of websites
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->systemStore->getWebsiteValuesForForm();
    }
}

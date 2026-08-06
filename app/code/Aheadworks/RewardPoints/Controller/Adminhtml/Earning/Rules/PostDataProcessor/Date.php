<?php
namespace Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\PostDataProcessor;

use Aheadworks\RewardPoints\Api\Data\EarnRuleInterface;
use Aheadworks\RewardPoints\Model\Data\ProcessorInterface;
use Magento\Framework\Stdlib\DateTime\Filter\Date as DateFilter;

/**
 * Class Date
 * @package Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules\PostDataProcessor
 */
class Date implements ProcessorInterface
{
    /**
     * @var DateFilter
     */
    private $dateFilter;

    /**
     * @param DateFilter $dateFilter
     */
    public function __construct(
        DateFilter $dateFilter
    ) {
        $this->dateFilter = $dateFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function process($data)
    {
        $data[EarnRuleInterface::FROM_DATE] = empty($data[EarnRuleInterface::FROM_DATE])
            ? null
            : $this->dateFilter->filter($data[EarnRuleInterface::FROM_DATE]);

        $data[EarnRuleInterface::TO_DATE] = empty($data[EarnRuleInterface::TO_DATE])
            ? null
            : $this->dateFilter->filter($data[EarnRuleInterface::TO_DATE]);

        return $data;
    }
}

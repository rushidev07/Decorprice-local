<?php
namespace Aheadworks\RewardPoints\Model\Data;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

/**
 * Interface CommandInterface
 *
 * @package Aheadworks\RewardPoints\Model\Data
 */
interface CommandInterface
{
    /**
     * Execute command
     *
     * @param array $data
     * @return DataObject|bool
     * @throws LocalizedException
     */
    public function execute($data);
}

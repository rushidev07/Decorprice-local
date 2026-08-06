<?php
namespace Aheadworks\RewardPoints\Model\Data;

/**
 * Interface ProcessorInterface
 * @package Aheadworks\RewardPoints\Model\Data
 */
interface ProcessorInterface
{
    /**
     * Process data
     *
     * @param array $data
     * @return array
     */
    public function process($data);
}

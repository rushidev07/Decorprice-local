<?php
namespace Aheadworks\RewardPoints\Model\Data;

/**
 * Class Processor
 * @package Aheadworks\RewardPoints\Model\Data
 */
class Processor implements ProcessorInterface
{
    /**
     * @var ProcessorInterface[]
     */
    private $processors;

    /**
     * @param ProcessorInterface[] $processors
     */
    public function __construct(array $processors = [])
    {
        $this->processors = $processors;
    }

    /**
     * {@inheritdoc}
     */
    public function process($data)
    {
        foreach ($this->processors as $processor) {
            $data = $processor->process($data);
        }
        return $data;
    }
}

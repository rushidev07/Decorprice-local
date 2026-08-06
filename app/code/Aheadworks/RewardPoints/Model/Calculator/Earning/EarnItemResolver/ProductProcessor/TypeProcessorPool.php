<?php
namespace Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor;

use Magento\Framework\Exception\ConfigurationMismatchException;

/**
 * Class TypeProcessorPool
 * @package Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ProductProcessor
 */
class TypeProcessorPool
{
    /**
     * Array key for default processor
     */
    const DEFAULT_PROCESSOR_CODE = 'default';

    /**
     * @var TypeProcessorInterface[]
     */
    private $processors;

    /**
     * @param TypeProcessorInterface[] $processors
     */
    public function __construct(
        $processors = []
    ) {
        $this->processors = $processors;
    }

    /**
     * Get processors
     *
     * @return TypeProcessorInterface[]
     */
    public function getProcessors()
    {
        return $this->processors;
    }

    /**
     * Get processor by code
     *
     * @param string $code
     * @return TypeProcessorInterface
     * @throws \Exception
     */
    public function getProcessorByCode($code)
    {
        if (!isset($this->processors[$code])) {
            $code = self::DEFAULT_PROCESSOR_CODE;
        }
        $processor = $this->processors[$code];
        if (!$processor instanceof TypeProcessorInterface) {
            throw new ConfigurationMismatchException(
                __('Type processor must implements %1', TypeProcessorInterface::class)
            );
        }

        return $processor;
    }
}

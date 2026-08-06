<?php
namespace Aheadworks\RewardPoints\Model\EarnRule\Action;

use Magento\Framework\Exception\ConfigurationMismatchException;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class TypePool
 * @package Aheadworks\RewardPoints\Model\EarnRule\Action
 */
class TypePool
{
    /**
     * @var TypeInterface[]
     */
    private $types;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Logger $logger
     * @param array $types
     */
    public function __construct(
        Logger $logger,
        $types = []
    ) {
        $this->logger = $logger;
        $this->types = $types;
    }

    /**
     * Get types
     *
     * @return TypeInterface[]
     * @throws \Exception
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * Get types count
     *
     * @return int
     * @throws \Exception
     */
    public function getTypesCount()
    {
        $types = $this->getTypes();
        return count($types);
    }

    /**
     * Get type by code
     *
     * @param string $code
     * @return TypeInterface
     * @throws \Exception
     */
    public function getTypeByCode($code)
    {
        $types = $this->getTypes();
        if (!isset($types[$code])) {
            throw new ConfigurationMismatchException(__('Unknown action type: %1 requested', $code));
        }

        return $types[$code];
    }

    /**
     * Check if the type exists
     *
     * @param string $code
     * @return bool
     */
    public function isTypeExists($code)
    {
        $result = false;
        try {
            $types = $this->getTypes();
            $result = isset($types[$code]);
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }

        return $result;
    }
}

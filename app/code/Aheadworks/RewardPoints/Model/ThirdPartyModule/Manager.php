<?php
namespace Aheadworks\RewardPoints\Model\ThirdPartyModule;

use Magento\Framework\Module\ModuleListInterface;

/**
 * Class Manager
 *
 * @package Aheadworks\RewardPoints\Model\ThirdPartyModule
 */
class Manager
{
    /**
     * Aheadworks SARP2 module name
     */
    const SARP2_MODULE_NAME = 'Aheadworks_Sarp2';

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @param ModuleListInterface $moduleList
     */
    public function __construct(
        ModuleListInterface $moduleList
    ) {
        $this->moduleList = $moduleList;
    }

    /**
     * Check if SARP2 module enabled
     *
     * @return bool
     */
    public function isSarp2ModuleEnabled()
    {
        return $this->moduleList->has(self::SARP2_MODULE_NAME);
    }
}

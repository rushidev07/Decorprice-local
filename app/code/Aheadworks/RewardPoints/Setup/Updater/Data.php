<?php
namespace Aheadworks\RewardPoints\Setup\Updater;

use Magento\Framework\DB\Ddl\Table;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Quote\Setup\QuoteSetup;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class Data
 *
 * @package Aheadworks\RewardPoints\Setup\Updater
 */
class Data
{
    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;

    /**
     * @param QuoteSetupFactory $quoteSetupFactory
     */
    public function __construct(
        QuoteSetupFactory $quoteSetupFactory
    ) {
        $this->quoteSetupFactory = $quoteSetupFactory;
    }

    /**
     * Upgrade to version 1.8.1
     *
     * @param ModuleDataSetupInterface $setup
     * @return $this
     */
    public function upgradeTo181(ModuleDataSetupInterface $setup)
    {
        /** @var QuoteSetup $quoteSetup */
        $quoteSetup = $this->quoteSetupFactory->create(
            [
                'setup' => $setup
            ]
        );

        $quoteSetup->addAttribute(
            'quote',
            'aw_reward_points_qty_to_apply',
            [
                'type' => Table::TYPE_INTEGER
            ]
        );

        return $this;
    }
}

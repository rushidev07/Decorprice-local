<?php
namespace Aheadworks\RewardPoints\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Framework\DB\Ddl\Table;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Quote\Setup\QuoteSetup;
use Magento\Sales\Setup\SalesSetup;
use Aheadworks\RewardPoints\Setup\Updater\Data as DataUpdater;

/**
 * Class UpgradeData
 *
 * @package Aheadworks\RewardPoints\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;

    /**
     * @var DataUpdater
     */
    private $dataUpdater;

    /**
     * @param SalesSetupFactory $salesSetupFactory
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param DataUpdater $dataUpdater
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        QuoteSetupFactory $quoteSetupFactory,
        DataUpdater $dataUpdater
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->dataUpdater = $dataUpdater;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->addRewardPointsAttributes($setup);
        }
        if (version_compare($context->getVersion(), '1.3.0', '<')) {
            $this->addRewardPointsAttributes130($setup);
        }
        if (version_compare($context->getVersion(), '1.8.1', '<')) {
            $this->dataUpdater->upgradeTo181($setup);
        }
    }

    /**
     * Add reward points attributes
     *
     * @param ModuleDataSetupInterface $setup
     * @return $this
     */
    private function addRewardPointsAttributes(ModuleDataSetupInterface $setup)
    {
        /** @var SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
        $salesSetup->addAttribute('order', 'aw_reward_points_blnce_invoiced', ['type' => Table::TYPE_INTEGER]);

        $salesSetup->addAttribute('order', 'aw_reward_points_blnce_refunded', ['type' => Table::TYPE_INTEGER]);

        $salesSetup->addAttribute('order', 'base_aw_reward_points_refund', ['type' => Table::TYPE_DECIMAL]);
        $salesSetup->addAttribute('order', 'aw_reward_points_refund', ['type' => Table::TYPE_DECIMAL]);
        $salesSetup->addAttribute('order', 'aw_reward_points_blnce_refund', ['type' => Table::TYPE_INTEGER]);
        $salesSetup->addAttribute('creditmemo', 'base_aw_reward_points_refund', ['type' => Table::TYPE_DECIMAL]);
        $salesSetup->addAttribute('creditmemo', 'aw_reward_points_refund', ['type' => Table::TYPE_DECIMAL]);
        $salesSetup->addAttribute('creditmemo', 'aw_reward_points_blnce_refund', ['type' => Table::TYPE_INTEGER]);

        $salesSetup->addAttribute('order', 'base_aw_reward_points_reimbursed', ['type' => Table::TYPE_DECIMAL]);
        $salesSetup->addAttribute('order', 'aw_reward_points_reimbursed', ['type' => Table::TYPE_DECIMAL]);
        $salesSetup->addAttribute('order', 'aw_reward_points_blnce_reimbursed', ['type' => Table::TYPE_INTEGER]);
        $salesSetup->addAttribute('creditmemo', 'base_aw_reward_points_reimbursed', ['type' => Table::TYPE_DECIMAL]);
        $salesSetup->addAttribute('creditmemo', 'aw_reward_points_reimbursed', ['type' => Table::TYPE_DECIMAL]);
        $salesSetup->addAttribute('creditmemo', 'aw_reward_points_blnce_reimbursed', ['type' => Table::TYPE_INTEGER]);

        return $this;
    }

    /**
     * Add reward points attributes for 1.3.0 version
     *
     * @param ModuleDataSetupInterface $setup
     * @return $this
     */
    private function addRewardPointsAttributes130(ModuleDataSetupInterface $setup)
    {
        /** @var QuoteSetup $quoteSetup */
        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
        /** @var SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);

        $itemAttributes = [
            'base_aw_reward_points_amount' => Table::TYPE_DECIMAL,
            'aw_reward_points_amount' => Table::TYPE_DECIMAL,
            'aw_reward_points' => Table::TYPE_INTEGER
        ];
        foreach ($itemAttributes as $code => $type) {
            $quoteSetup->addAttribute('quote_item', $code, ['type' => $type, 'visible' => false]);
            $quoteSetup->addAttribute('quote_address_item', $code, ['type' => $type, 'visible' => false]);

            $salesSetup->addAttribute('order_item', $code, ['type' => $type, 'visible' => false]);
            $salesSetup->addAttribute('invoice_item', $code, ['type' => $type, 'visible' => false]);
            $salesSetup->addAttribute('creditmemo_item', $code, ['type' => $type, 'visible' => false]);
        }

        $orderItemAttributes = [
            'base_aw_reward_points_refunded' => Table::TYPE_DECIMAL,
            'aw_reward_points_refunded' => Table::TYPE_DECIMAL,
            'aw_reward_points_blnce_refunded' => Table::TYPE_INTEGER,
            'base_aw_reward_points_reimbursed' => Table::TYPE_DECIMAL,
            'aw_reward_points_reimbursed' => Table::TYPE_DECIMAL,
            'aw_reward_points_blnce_reimbursed' => Table::TYPE_INTEGER,
            'base_aw_reward_points_invoiced' => Table::TYPE_DECIMAL,
            'aw_reward_points_invoiced' => Table::TYPE_DECIMAL,
            'aw_reward_points_blnce_invoiced' => Table::TYPE_INTEGER
        ];
        foreach ($orderItemAttributes as $code => $type) {
            $salesSetup->addAttribute('order_item', $code, ['type' => $type, 'visible' => false]);
        }

        $creditmemoItemAttributes = [
            'base_aw_reward_points_refunded' => Table::TYPE_DECIMAL,
            'aw_reward_points_refunded' => Table::TYPE_DECIMAL,
            'aw_reward_points_blnce_refunded' => Table::TYPE_INTEGER,
            'base_aw_reward_points_reimbursed' => Table::TYPE_DECIMAL,
            'aw_reward_points_reimbursed' => Table::TYPE_DECIMAL,
            'aw_reward_points_blnce_reimbursed' => Table::TYPE_INTEGER
        ];
        foreach ($creditmemoItemAttributes as $code => $type) {
            $salesSetup->addAttribute('creditmemo_item', $code, ['type' => $type, 'visible' => false]);
        }

        $attributes = [
            'aw_reward_points_shipping_amount' => ['type' => Table::TYPE_DECIMAL],
            'base_aw_reward_points_shipping_amount' => ['type' => Table::TYPE_DECIMAL],
            'aw_reward_points_shipping' => ['type' => Table::TYPE_INTEGER]
        ];
        foreach ($attributes as $attributeCode => $attributeParams) {
            $quoteSetup->addAttribute('quote_address', $attributeCode, $attributeParams);
            $salesSetup->addAttribute('order', $attributeCode, $attributeParams);
        }
    }
}

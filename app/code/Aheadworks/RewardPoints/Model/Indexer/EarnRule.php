<?php
namespace Aheadworks\RewardPoints\Model\Indexer;

use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\InputException;
use Aheadworks\RewardPoints\Model\Indexer\EarnRule\Action\Row as ActionRow;
use Aheadworks\RewardPoints\Model\Indexer\EarnRule\Action\Rows as ActionRows;
use Aheadworks\RewardPoints\Model\Indexer\EarnRule\Action\Full as ActionFull;

/**
 * Class EarnRule
 * @package Aheadworks\RewardPoints\Model\Indexer
 */
class EarnRule implements IndexerActionInterface, MviewActionInterface
{
    /**
     * @var ActionRow
     */
    private $ruleIndexerRow;

    /**
     * @var ActionRows
     */
    private $ruleIndexerRows;

    /**
     * @var ActionFull
     */
    private $ruleIndexerFull;

    /**
     * @param ActionRow $ruleIndexerRow
     * @param ActionRows $ruleIndexerRows
     * @param ActionFull $ruleIndexerFull
     */
    public function __construct(
        ActionRow $ruleIndexerRow,
        ActionRows $ruleIndexerRows,
        ActionFull $ruleIndexerFull
    ) {
        $this->ruleIndexerRow = $ruleIndexerRow;
        $this->ruleIndexerRows = $ruleIndexerRows;
        $this->ruleIndexerFull = $ruleIndexerFull;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @throws InputException
     * @throws LocalizedException
     */
    public function execute($ids)
    {
        $this->ruleIndexerRows->execute($ids);
    }

    /**
     * Execute full indexation
     * @return void
     * @throws LocalizedException
     */
    public function executeFull()
    {
        $this->ruleIndexerFull->execute();
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     * @throws InputException
     * @throws LocalizedException
     */
    public function executeList(array $ids)
    {
        $this->ruleIndexerRows->execute($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     * @throws InputException
     * @throws LocalizedException
     */
    public function executeRow($id)
    {
        $this->ruleIndexerRow->execute($id);
    }
}

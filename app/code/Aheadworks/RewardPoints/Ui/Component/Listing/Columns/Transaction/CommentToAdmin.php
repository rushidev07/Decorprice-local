<?php
namespace Aheadworks\RewardPoints\Ui\Component\Listing\Columns\Transaction;

use Aheadworks\RewardPoints\Model\Comment\CommentPoolInterface;
use Aheadworks\RewardPoints\Model\Comment\Admin\AppliedEarningRules;
use Aheadworks\RewardPoints\Model\Source\Transaction\EntityType as TransactionEntityType;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class Aheadworks\RewardPoints\Ui\Component\Listing\Columns\Transaction\CommentToAdmin
 */
class CommentToAdmin extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var CommentPoolInterface
     */
    private $commentPool;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CommentPoolInterface $commentPool
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CommentPoolInterface $commentPool,
        array $components = [],
        array $data = []
    ) {
        $this->commentPool = $commentPool;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * {@inheritDoc}
     * phpcs:disable Generic.Metrics.NestingLevel
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items']) && is_array($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (!empty($item['entities'])) {
                    foreach ($item['entities'] as $entityType => $entityData) {
                        if ($entityType == TransactionEntityType::EARN_RULE_ID) {
                            $commentInstance = $this->commentPool->get(
                                AppliedEarningRules::COMMENT_FOR_APPLIED_EARNING_RULES
                            );
                            if ($commentInstance) {
                                $commentLabel = $commentInstance->renderComment(
                                    $item['entities'],
                                    null,
                                    $item['comment_to_admin_placeholder'],
                                    true
                                );
                            }

                            if (!empty($commentLabel)) {
                                $item['comment_to_admin'] = $commentLabel;
                            }
                        }
                    }
                }
            }
        }
        return $dataSource;
    }
}

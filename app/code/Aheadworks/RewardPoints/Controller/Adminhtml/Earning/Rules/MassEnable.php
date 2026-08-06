<?php
namespace Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules;

use Aheadworks\RewardPoints\Api\EarnRuleManagementInterface;
use Aheadworks\RewardPoints\Model\ResourceModel\EarnRule\CollectionFactory as EarnRuleCollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassEnable
 * @package Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules
 */
class MassEnable extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_RewardPoints::aw_reward_points_earning_rules';

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var EarnRuleCollectionFactory
     */
    private $ruleCollectionFactory;

    /**
     * @var EarnRuleManagementInterface
     */
    private $ruleManagement;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param EarnRuleCollectionFactory $ruleCollectionFactory
     * @param EarnRuleManagementInterface $ruleManagement
     */
    public function __construct(
        Context $context,
        Filter $filter,
        EarnRuleCollectionFactory $ruleCollectionFactory,
        EarnRuleManagementInterface $ruleManagement
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->ruleManagement = $ruleManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->ruleCollectionFactory->create());
            $count = 0;
            foreach ($collection->getAllIds() as $ruleId) {
                $this->ruleManagement->enable($ruleId);
                $count++;
            }
            if ($count) {
                $this->messageManager->addSuccessMessage(__('A total of %1 rule(s) were enabled.', $count));
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}

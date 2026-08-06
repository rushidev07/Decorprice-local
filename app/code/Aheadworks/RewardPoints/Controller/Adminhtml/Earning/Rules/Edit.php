<?php
namespace Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules;

use Aheadworks\RewardPoints\Api\EarnRuleRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;

/**
 * Class Edit
 * @package Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules
 */
class Edit extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_RewardPoints::aw_reward_points_earning_rules';

    /**
     * Key used for registry to store rule
     */
    const CURRENT_RULE_KEY = 'aw_reward_points_current_rule';

    /**
     * @var EarnRuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param Context $context
     * @param EarnRuleRepositoryInterface $ruleRepository
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        EarnRuleRepositoryInterface $ruleRepository,
        PageFactory $resultPageFactory,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->ruleRepository = $ruleRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $ruleId = (int) $this->getRequest()->getParam('id');
        if ($ruleId) {
            try {
                $rule = $this->ruleRepository->get($ruleId);
                $this->registry->register(self::CURRENT_RULE_KEY, $rule);
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('This rule no longer exists.')
                );
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/');
                return $resultRedirect;
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage
            ->setActiveMenu('Aheadworks_RewardPoints::aw_reward_points_earning_rules')
            ->getConfig()->getTitle()->prepend(
                $ruleId
                    ? __('Edit "%1" Rule', $rule->getName())
                    : __('New Rule')
            );

        return $resultPage;
    }
}

<?php
namespace Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;

/**
 * Class Index
 * @package Aheadworks\RewardPoints\Controller\Adminhtml\Earning\Rules
 */
class Index extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Aheadworks_RewardPoints::aw_reward_points_earning_rules';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage **/
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Aheadworks_RewardPoints::aw_reward_points_earning_rules');
        $resultPage->addBreadcrumb(__('Aheadworks Reward Points'), __('Points Earning Rules'));
        $resultPage->getConfig()->getTitle()->prepend(__('Points Earning Rules'));

        return $resultPage;
    }
}

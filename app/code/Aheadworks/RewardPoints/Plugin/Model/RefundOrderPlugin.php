<?php
namespace Aheadworks\RewardPoints\Plugin\Model;

use Aheadworks\RewardPoints\Api\CustomerRewardPointsManagementInterface;
use Magento\Sales\Api\RefundOrderInterface;

/**
 * Class RefundOrderPlugin
 *
 * @package Aheadworks\RewardPoints\Plugin\Model
 */
class RefundOrderPlugin
{
    /**
     * @var CustomerRewardPointsManagementInterface
     */
    private $customerRewardPointsService;

    /**
     * @param CustomerRewardPointsManagementInterface $customerRewardPointsService
     */
    public function __construct(
        CustomerRewardPointsManagementInterface $customerRewardPointsService
    ) {
        $this->customerRewardPointsService = $customerRewardPointsService;
    }

    /**
     * Refund Reward Points to customer on credit memo
     *
     * @param RefundOrderInterface $subject
     * @param int $result
     * @return int
     * @since 100.1.3
     */
    public function afterExecute(RefundOrderInterface $subject, $result)
    {
        $this->customerRewardPointsService->refundToRewardPoints($result);
        $this->customerRewardPointsService->reimbursedSpentRewardPoints($result);
        $this->customerRewardPointsService->cancelEarnedPointsRefundOrder($result);

        return $result;
    }
}

<?php
namespace Aheadworks\RewardPoints\Model\Data\Command\Transaction;

use Aheadworks\RewardPoints\Api\CustomerRewardPointsManagementInterface;
use Aheadworks\RewardPoints\Model\Data\CommandInterface;
use Aheadworks\RewardPoints\Model\Data\Processor\Post\Transaction\Processor
    as TransactionPostDataProcessor;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Create
 *
 * @package Aheadworks\RewardPoints\Model\Data\Command\Transaction
 */
class Create implements CommandInterface
{
    /**
     * @var TransactionPostDataProcessor
     */
    private $transactionPostDataProcessor;

    /**
     * @var CustomerRewardPointsManagementInterface
     */
    private $customerRewardPointsService;

    /**
     * @param TransactionPostDataProcessor $transactionPostDataProcessor
     * @param CustomerRewardPointsManagementInterface $customerRewardPointsService
     */
    public function __construct(
        TransactionPostDataProcessor $transactionPostDataProcessor,
        CustomerRewardPointsManagementInterface $customerRewardPointsService
    ) {
        $this->transactionPostDataProcessor = $transactionPostDataProcessor;
        $this->customerRewardPointsService = $customerRewardPointsService;
    }

    /**
     * @inheritdoc
     */
    public function execute($data)
    {
        $data = $this->transactionPostDataProcessor->filter($data);
        $customerSelection = $this->transactionPostDataProcessor->customerSelectionFilter($data);
        if (!empty($customerSelection)) {
            foreach ($customerSelection as $transactionData) {
                $this->customerRewardPointsService->resetCustomer();
                $this->customerRewardPointsService->saveAdminTransaction($transactionData);
            }
        } else {
            throw new LocalizedException(
                __('Invalid customer selection')
            );
        }
        return true;
    }
}

<?php
namespace Aheadworks\RewardPoints\Cron;

use Aheadworks\RewardPoints\Api\CustomerRewardPointsManagementInterface;
use Aheadworks\RewardPoints\Model\Config;
use Aheadworks\RewardPoints\Model\FlagFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Aheadworks\RewardPoints\Model\TransactionRepository;
use Aheadworks\RewardPoints\Model\Flag;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class CustomerBirthday
 * @package Aheadworks\RewardPoints\Cron
 */
class CustomerBirthday extends CronAbstract
{
    const RUN_INTERVAL = 82800;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param DateTime $dateTime
     * @param CustomerRewardPointsManagementInterface $customerRewardPointsService
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param TransactionRepository $transactionRepository
     * @param FlagFactory $flagFactory
     * @param Config $config
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        DateTime $dateTime,
        CustomerRewardPointsManagementInterface $customerRewardPointsService,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        TransactionRepository $transactionRepository,
        FlagFactory $flagFactory,
        Config $config,
        CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct(
            $dateTime,
            $customerRewardPointsService,
            $searchCriteriaBuilder,
            $transactionRepository,
            $flagFactory
        );
        $this->config = $config;
        $this->customerRepository = $customerRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($this->isLocked(Flag::AW_RP_CUSTOMER_BIRTHDAY_LAST_EXEC_TIME, self::RUN_INTERVAL)) {
            return $this;
        }
        $this->createCustomerBirthdayTransactions();
        $this->setFlagData(Flag::AW_RP_CUSTOMER_BIRTHDAY_LAST_EXEC_TIME);

        return $this;
    }

    /**
     * Create customer birthday transactions
     *
     * @return $this
     * @throws LocalizedException
     */
    private function createCustomerBirthdayTransactions()
    {
        $interval = $this->config->getCustomerBirthdayInAdvanceDays();
        $date = new \DateTime('now');
        $date->modify('+' . $interval . 'days');

        $this->searchCriteriaBuilder
            ->addFilter(
                CustomerInterface::DOB,
                '%' . $date->format('m-d') . '%',
                'like'
            );

        $customers = $this->customerRepository
            ->getList($this->searchCriteriaBuilder->create())
            ->getItems();

        foreach ($customers as $customer) {
            $this->customerRewardPointsService->addPointsForCustomerBirthday(
                $customer->getId(),
                $customer->getWebsiteId()
            );
        }
        return $this;
    }
}

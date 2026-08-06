<?php
namespace Aheadworks\RewardPoints\Plugin\Model\Customer;

use Aheadworks\RewardPoints\Api\Data\PointsSummaryInterface;
use Aheadworks\RewardPoints\Model\Config;
use Aheadworks\RewardPoints\Model\Service\PointsSummaryService;
use Aheadworks\RewardPoints\Model\Source\Customer\BirthdayLimit;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime;

/**
 * Class CustomerRepositoryPlugin
 * @package Aheadworks\RewardPoints\Plugin\Model\Customer
 */
class CustomerRepositoryPlugin
{
    /**
     * @var PointsSummaryService
     */
    private $pointsSummaryService;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $processedIds = [];

    /**
     * @param PointsSummaryService $pointsSummaryService
     * @param Config $config
     */
    public function __construct(
        PointsSummaryService $pointsSummaryService,
        Config $config
    ) {
        $this->pointsSummaryService = $pointsSummaryService;
        $this->config = $config;
    }

    /**
     * Track dob updating
     *
     * @param CustomerRepositoryInterface $subject
     * @param \Closure $proceed
     * @param CustomerInterface $customer
     * @param string|null $passwordHash
     * @return CustomerInterface
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave($subject, \Closure $proceed, CustomerInterface $customer, $passwordHash = null)
    {
        $customerId = $customer->getId();

        if (!in_array($customerId, $this->processedIds)) {
            $isLimited = $this->config->getCustomerBirthdayLimit() == BirthdayLimit::ONCE_A_YEAR;

            if ($isLimited && $customerId) {
                $origCustomer = $subject->getById($customerId);
                $dobChanged = $origCustomer->getDob() != $customer->getDob();
                $this->validate($customer, $dobChanged);
            } else {
                $dobChanged = (bool)$customer->getDob();
            }

            /** @var CustomerInterface $result */
            $result = $proceed($customer, $passwordHash);

            if ($dobChanged && !empty($result->getDob())) {
                $this->updateDobUpdateDate($result);
                $this->processedIds[] = $result->getId();
            }
        } else {
            $result = $proceed($customer, $passwordHash);
        }

        return $result;
    }

    /**
     * Validate
     *
     * @param CustomerInterface $customer
     * @param boolean $dobChanged
     * @throws LocalizedException
     */
    private function validate(CustomerInterface $customer, $dobChanged)
    {
        $pointsSummary = $this->pointsSummaryService->getPointsSymmary($customer->getId());
        $nowDate = new \DateTime('now', new \DateTimeZone('UTC'));

        if ($pointsSummary->getDobUpdateDate() && $dobChanged) {
            $dobUpdateDate = new \DateTime($pointsSummary->getDobUpdateDate(), new \DateTimeZone('UTC'));
            $dobUpdateDate->modify('+1year');
            if ($dobUpdateDate > $nowDate) {
                throw new LocalizedException(__('Date of Birth can be modified only once a year.'));
            }
        }
    }

    /**
     * Update dob update date
     *
     * @param CustomerInterface $customer
     * @throws LocalizedException
     */
    private function updateDobUpdateDate(CustomerInterface $customer)
    {
        $nowDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $transferObject = new DataObject([
            PointsSummaryInterface::CUSTOMER_ID => $customer->getId(),
            PointsSummaryInterface::DOB_UPDATE_DATE => $nowDate->format(DateTime::DATE_PHP_FORMAT)
        ]);

        $this->pointsSummaryService->updateCustomerSummary($transferObject);
    }
}

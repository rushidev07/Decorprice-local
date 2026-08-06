<?php
namespace Aheadworks\RewardPoints\Plugin\Model\Service;

use Aheadworks\RewardPoints\Api\CustomerRewardPointsManagementInterface;
use Aheadworks\RewardPoints\Model\ResourceModel\Order as OrderResource;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class ReviewServicePlugin
 * @package Aheadworks\RewardPoints\Plugin\Model\Service
 */
class ReviewServicePlugin
{
    /**
     * @var CustomerRewardPointsManagementInterface
     */
    private $customerRewardPointsService;

    /**
     * @var OrderResource
     */
    private $orderResource;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Aheadworks\AdvancedReviews\Api\ReviewRepositoryInterface
     */
    private $reviewRepository;

    /**
     * @param CustomerRewardPointsManagementInterface $customerRewardPointsService
     * @param OrderResource $orderResource
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        CustomerRewardPointsManagementInterface $customerRewardPointsService,
        OrderResource $orderResource,
        ObjectManagerInterface $objectManager
    ) {
        $this->customerRewardPointsService = $customerRewardPointsService;
        $this->orderResource = $orderResource;
        $this->objectManager = $objectManager;
    }

    /**
     * Process create new review
     *
     * @param \Aheadworks\AdvancedReviews\Model\Service\ReviewService $subject
     * @param \Closure $proceed
     * @param \Aheadworks\AdvancedReviews\Api\Data\ReviewInterface $review
     * @return \Aheadworks\AdvancedReviews\Api\Data\ReviewInterface
     */
    public function aroundCreateReview($subject, \Closure $proceed, $review)
    {
        return $this->saveReviewProcess($proceed, $review);
    }

    /**
     * Process update review
     *
     * @param \Aheadworks\AdvancedReviews\Model\Service\ReviewService $subject
     * @param \Closure $proceed
     * @param \Aheadworks\AdvancedReviews\Api\Data\ReviewInterface $review
     * @return \Aheadworks\AdvancedReviews\Api\Data\ReviewInterface
     */
    public function aroundUpdateReview($subject, \Closure $proceed, $review)
    {
        return $this->saveReviewProcess($proceed, $review);
    }

    /**
     * Process save review
     *
     * @param \Closure $proceed
     * @param \Aheadworks\AdvancedReviews\Api\Data\ReviewInterface $review
     * @return \Aheadworks\AdvancedReviews\Api\Data\ReviewInterface
     */
    private function saveReviewProcess(\Closure $proceed, $review)
    {
        if ($review->getId()) {
            $beforeSaveReview = $this->getReviewRepository()->getById($review->getId());
            $beforeSaveStatus = $beforeSaveReview->getStatus();
        } else {
            $beforeSaveStatus = $review->getStatus();
        }
        $processedReview = $proceed($review);
        $afterSaveStatus = $review->getStatus();

        if ($beforeSaveStatus != \Aheadworks\AdvancedReviews\Model\Source\Review\Status::APPROVED
            && $afterSaveStatus == \Aheadworks\AdvancedReviews\Model\Source\Review\Status::APPROVED
        ) {
            $customerId = $review->getCustomerId();
            if ($customerId) {
                $this->customerRewardPointsService->addPointsForReviews(
                    $customerId,
                    $this->orderResource->isCustomersOwnerOfProductId(
                        $customerId,
                        $review->getProductId()
                    )
                );
            }
        }

        return $processedReview;
    }

    /**
     * Retrieve review repository instance
     *
     * @return \Aheadworks\AdvancedReviews\Api\ReviewRepositoryInterface
     */
    private function getReviewRepository()
    {
        if (null === $this->reviewRepository) {
            $this->reviewRepository = $this->objectManager->create(
                \Aheadworks\AdvancedReviews\Api\ReviewRepositoryInterface::class
            );
        }
        return $this->reviewRepository;
    }
}

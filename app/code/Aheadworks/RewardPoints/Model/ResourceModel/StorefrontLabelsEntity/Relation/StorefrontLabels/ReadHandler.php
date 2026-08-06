<?php
namespace Aheadworks\RewardPoints\Model\ResourceModel\StorefrontLabelsEntity\Relation\StorefrontLabels;

use Aheadworks\RewardPoints\Api\Data\StorefrontLabelsEntityInterface;
use Aheadworks\RewardPoints\Api\Data\StorefrontLabelsInterface;
use Aheadworks\RewardPoints\Model\ResourceModel\StorefrontLabels\Repository;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Aheadworks\RewardPoints\Model\StorefrontLabelsResolver;

/**
 * Class ReadHandler
 *
 * @package Aheadworks\RewardPoints\Model\ResourceModel\StorefrontLabelsEntity\Relation\StorefrontLabels
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var StorefrontLabelsResolver
     */
    private $storefrontLabelsResolver;

    /**
     * @param Repository $repository
     * @param StorefrontLabelsResolver $storefrontLabelsResolver
     */
    public function __construct(
        Repository $repository,
        StorefrontLabelsResolver $storefrontLabelsResolver
    ) {
        $this->repository = $repository;
        $this->storefrontLabelsResolver = $storefrontLabelsResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($entity, $arguments = [])
    {
        /** @var StorefrontLabelsEntityInterface $entity */
        if (!(int)$entity->getEntityId()) {
            return $entity;
        }

        /** @var StorefrontLabelsInterface[] $labelsObjects */
        $labelsObjects = $this->repository->get($entity);
        $currentLabelsRecord = $this->storefrontLabelsResolver->getLabelsForStore(
            $labelsObjects,
            isset($arguments['store_id']) ? $arguments['store_id'] : null
        );
        $entity
            ->setLabels($labelsObjects)
            ->setCurrentLabels($currentLabelsRecord);

        return $entity;
    }
}

<?php
namespace Aheadworks\RewardPoints\Model;

use Aheadworks\RewardPoints\Api\Data\StorefrontLabelsInterface;
use Aheadworks\RewardPoints\Model\StorefrontLabels\ObjectResolver;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\Store;

/**
 * Class StorefrontLabelsResolver
 *
 * @package Aheadworks\RewardPoints\Model
 */
class StorefrontLabelsResolver
{
    /**
     * @var ObjectResolver
     */
    private $objectResolver;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @param ObjectResolver $objectResolver
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        ObjectResolver $objectResolver,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->objectResolver = $objectResolver;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Retrieve labels on storefront for specific store view
     *
     * @param StorefrontLabelsInterface[]|array $labelsData
     * @param int|null $storeId
     * @return StorefrontLabelsInterface
     */
    public function getLabelsForStore($labelsData, $storeId)
    {
        $labelRecordForStore = null;
        foreach ($labelsData as $labelsDataRow) {
            $labelsRecord = $this->objectResolver->resolve($labelsDataRow);
            if ($labelsRecord->getStoreId() == Store::DEFAULT_STORE_ID
                && (!isset($storeId) || !isset($labelRecordForStore))
            ) {
                $labelRecordForStore = $labelsRecord;
            }
            if (isset($storeId) && $labelsRecord->getStoreId() == $storeId) {
                $labelRecordForStore = $labelsRecord;
            }
        }
        return $labelRecordForStore;
    }

    /**
     * Retrieve labels on storefront for specific store view as array
     *
     * @param array $labelsData
     * @param int|null $storeId
     * @return array
     */
    public function getLabelsForStoreAsArray($labelsData, $storeId)
    {
        $storefrontLabel = $this->getLabelsForStore($labelsData, $storeId);

        return $this->dataObjectProcessor->buildOutputDataArray(
            $storefrontLabel,
            StorefrontLabelsInterface::class
        );
    }
}

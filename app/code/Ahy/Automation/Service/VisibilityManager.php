<?php
namespace Ahy\Automation\Service;

use Magento\Catalog\Model\ResourceModel\Product\Action as ProductAction;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Eav\Model\Config as EavConfig;
use Ahy\Automation\Model\TimeResolver;
use Ahy\Automation\Logger\Logger;

class VisibilityManager
{
    protected $productAction;
    protected $collectionFactory;
    protected $timeResolver;
    protected $eavConfig;
    protected $logger;

    // Brands to process
    const BRAND_NAMES = ['Hinkley Lighting', 'Fredrick Ramond', 'Lark'];
    const CATEGORY_EXCLUDE_LABEL = 'Fan Accessories';

    public function __construct(
        ProductAction $productAction,
        ProductCollectionFactory $collectionFactory,
        TimeResolver $timeResolver,
        EavConfig $eavConfig,
        Logger $logger
    ) {
        $this->productAction = $productAction;
        $this->collectionFactory = $collectionFactory;
        $this->timeResolver = $timeResolver;
        $this->eavConfig = $eavConfig;
        $this->logger = $logger;
    }

    /**
     * Main process function
     *
     * @param string|null $simulateDate Optional simulate datetime in EST
     * @param bool $dryRun Only simulate
     * @return int number of products updated
     */
    public function process($simulateDate = null, $dryRun = false)
    {
        // Step 1: Resolve dynamic option IDs
        $brandOptionIds = $this->getAttributeOptionIds('manufacturer', self::BRAND_NAMES);
        $categoryExcludeId = $this->getAttributeOptionIds('category_type', [self::CATEGORY_EXCLUDE_LABEL])[0] ?? null;

        $this->logger->info('Resolved brand option IDs: ' . implode(', ', $brandOptionIds));
        $this->logger->info('Resolved category_type exclusion ID: ' . $categoryExcludeId);

        // Step 2: Determine desired visibility
        $desiredVisibility = $this->timeResolver->getDesiredVisibility($simulateDate);

        // Step 3: Load filtered product collection
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToFilter('manufacturer', ['in' => $brandOptionIds])
                   ->addAttributeToFilter('category_type', ['neq' => $categoryExcludeId])
                   ->addAttributeToFilter('visibility', ['neq' => $desiredVisibility])
                   ->addAttributeToSelect('visibility');

        $productIds = $collection->getAllIds();

        if (empty($productIds)) {
            return 0; // nothing to update
        }

        if ($dryRun) {
            return count($productIds); // just simulate
        }

        // Bulk update visibility
        $this->productAction->updateAttributes(
            $productIds,
            ['visibility' => $desiredVisibility],
            0 // store 0 = global
        );

        return count($productIds);
    }

    /**
     * Resolve attribute option IDs dynamically
     *
     * @param string $attributeCode
     * @param array $optionLabels
     * @return array
     */
    protected function getAttributeOptionIds(string $attributeCode, array $optionLabels): array
    {
        $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);

        $optionIds = [];
        foreach ($attribute->getSource()->getAllOptions(false) as $option) {
            if (in_array($option['label'], $optionLabels)) {
                $optionIds[] = $option['value'];
            }
        }

        return $optionIds;
    }

    /**
     * Get the visibility value for a given datetime (or now if null)
     *
     * @param string|null $simulateDate
     * @return int
     */
    public function getDesiredVisibility($simulateDate = null)
    {
        return $this->timeResolver->getDesiredVisibility($simulateDate);
    }
}
<?php
namespace Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor;

use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemInterface;
use Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\ItemInterfaceFactory;
use Magento\Framework\DataObject\Copy as ObjectCopyService;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;

/**
 * Class ItemGroupConverter
 * @package Aheadworks\RewardPoints\Model\Calculator\Earning\EarnItemResolver\RawItemProcessor
 */
class ItemGroupConverter implements ItemGroupConverterInterface
{
    /**
     * @var ItemInterfaceFactory
     */
    private $itemFactory;

    /**
     * @var ObjectCopyService
     */
    private $objectCopyService;

    /**
     * @var string
     */
    private $fieldset;

    /**
     * @var string
     */
    private $aspect;

    /**
     * @param ItemInterfaceFactory $itemFactory
     * @param ObjectCopyService $objectCopyService
     * @param $fieldset
     * @param $aspect
     */
    public function __construct(
        ItemInterfaceFactory $itemFactory,
        ObjectCopyService $objectCopyService,
        $fieldset,
        $aspect
    ) {
        $this->itemFactory = $itemFactory;
        $this->objectCopyService = $objectCopyService;
        $this->fieldset = $fieldset;
        $this->aspect = $aspect;
    }

    /**
     * Convert raw object item groups to item groups
     *
     * @param array $objectItemGroups
     * @return array
     */
    public function convert($objectItemGroups)
    {
        $groupedItems = [];
        foreach ($objectItemGroups as $parentItemId => $objectItemGroup) {
            $itemGroup = [];
            /** @var ItemInterface $parentItem */
            $parentItem = $this->getItem($objectItemGroup[$parentItemId]);
            foreach ($objectItemGroup as $objectItem) {
                if ($objectItem->getItemId() == $parentItemId) {
                    $itemGroup[] = $parentItem;
                } else {
                    $item = $this->getItem($objectItem);
                    $item->setParentItem($parentItem);
                    $itemGroup[] = $item;
                }
            }
            if (!empty($itemGroup)) {
                $groupedItems[] = $itemGroup;
            }
        }
        return $groupedItems;
    }

    /**
     * Get item from object item
     *
     * @param InvoiceItem $objectItem
     * @return ItemInterface
     */
    public function getItem($objectItem)
    {
        $item = $this->itemFactory->create();
        $this->objectCopyService->copyFieldsetToTarget(
            $this->fieldset,
            $this->aspect,
            $objectItem,
            $item
        );

        return $item;
    }
}

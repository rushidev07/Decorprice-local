<?php

namespace Unirgy\Dropship\Model\Pdf\ShipmentItems;

use \Magento\Bundle\Model\Sales\Order\Pdf\Items\AbstractItems;
use \Magento\Framework\Data\Collection\AbstractDb;
use \Magento\Framework\Filesystem;
use \Magento\Framework\Filter\FilterManager;
use \Magento\Framework\Model\Context;
use \Magento\Framework\Model\ResourceModel\AbstractResource;
use \Magento\Framework\Registry;
use \Magento\Tax\Helper\Data as HelperData;

class Bundle extends AbstractItems
{
    /**
     * @return \Magento\Framework\Stdlib\StringUtils
     */
    protected function strHlp()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\Stdlib\StringUtils::class);
    }

    /**
     * Draw item line
     *
     */
    public function draw()
    {
        $item   = $this->getItem();
        $pdf    = $this->getPdf();
        $page   = $this->getPage();

        $this->_setFontRegular();

        $shipItems = $this->getChilds($item);
        $items = array_merge([$item->getOrderItem()], $item->getOrderItem()->getChildrenItems());

        $_prevOptionId = '';
        $drawItems = [];

        foreach ($shipItems as $_shipItem) {
            $_item = $_shipItem->getOrderItem();
            $line   = [];

            $attributes = $this->getSelectionAttributes($_item);
            if (is_array($attributes)) {
                $optionId   = $attributes['option_id'];
            } else {
                $optionId = 0;
            }

            if (!isset($drawItems[$optionId])) {
                $drawItems[$optionId] = [
                    'lines'  => [],
                    'height' => 15
                ];
            }

            if ($_item->getParentItem()) {
                if ($_prevOptionId != $attributes['option_id']) {
                    $line[0] = [
                        'font'  => 'italic',
                        'text'  => $this->strHlp()->split($attributes['option_label'], 60, true, true),
                        'feed'  => 60
                    ];

                    $drawItems[$optionId] = [
                        'lines'  => [$line],
                        'height' => 15
                    ];

                    $line = [];

                    $_prevOptionId = $attributes['option_id'];
                }
            }

            if (($this->isShipmentSeparately() && $_item->getParentItem())
                || (!$this->isShipmentSeparately() && !$_item->getParentItem())
            ) {
                if (isset($shipItems[$_item->getId()])) {
                    $qty = $shipItems[$_item->getId()]->getQty()*1;
                } elseif ($_item->getIsVirtual()) {
                    $qty = __('N/A');
                } else {
                    $qty = 0;
                }
            } else {
                $qty = '';
            }

            $line[] = [
                'text'  => $qty,
                'feed'  => 35
            ];

            // draw Name
            if ($_item->getParentItem()) {
                $feed = 65;
                $name = $this->getValueHtml($_item);
            } else {
                $feed = 60;
                $name = $_item->getName();
            }
            $text = [];
            foreach ($this->strHlp()->split($name, 60, true, true) as $part) {
                $text[] = $part;
            }
            $line[] = [
                'text'  => $text,
                'feed'  => $feed
            ];

            // draw SKUs
            $text = [];
            foreach ($this->strHlp()->split($_item->getSku(), 25) as $part) {
                $text[] = $part;
            }
            $line[] = [
                'text'  => $text,
                'feed'  => 440
            ];

            $drawItems[$optionId]['lines'][] = $line;
        }

        // custom options
        $options = $item->getOrderItem()->getProductOptions();
        if ($options) {
            if (isset($options['options'])) {
                foreach ($options['options'] as $option) {
                    $lines = [];
                    $lines[][] = [
                        'text'  => $this->strHlp()->split(strip_tags($option['label']), 70, true, true),
                        'font'  => 'italic',
                        'feed'  => 60
                    ];

                    if ($option['value']) {
                        $text = [];
                        $_printValue = isset($option['print_value'])
                            ? $option['print_value']
                            : strip_tags($option['value']);
                        $values = explode(', ', $_printValue);
                        foreach ($values as $value) {
                            foreach ($this->strHlp()->split($value, 50, true, true) as $_value) {
                                $text[] = $_value;
                            }
                        }

                        $lines[][] = [
                            'text'  => $text,
                            'feed'  => 65
                        ];
                    }

                    $drawItems[] = [
                        'lines'  => $lines,
                        'height' => 15
                    ];
                }
            }
        }

        $page = $pdf->drawLineBlocks($page, $drawItems, ['table_header' => true]);
        $this->setPage($page);
    }
}

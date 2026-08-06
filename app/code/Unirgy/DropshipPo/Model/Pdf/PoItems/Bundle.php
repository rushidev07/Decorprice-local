<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_DropshipPo
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\DropshipPo\Model\Pdf\PoItems;

use Magento\Bundle\Model\Sales\Order\Pdf\Items\AbstractItems;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Filesystem;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Tax\Helper\Data as HelperData;

class Bundle extends AbstractItems
{
    /**
     * @return \Magento\Framework\Stdlib\StringUtils
     */
    protected function strHlp()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\Stdlib\StringUtils::class);
    }

    public function getChilds($item)
    {
        $_itemsArray = [];
        $_items = $item->getPo()->getAllItems();

        if ($_items) {
            foreach ($_items as $_item) {
                if ($parentItem = $_item->getOrderItem()->getParentItem()) {
                    $_itemsArray[$parentItem->getId()][$_item->getOrderItemId()] = $_item;
                } else {
                    $_itemsArray[$_item->getOrderItem()->getId()][$_item->getOrderItemId()] = $_item;
                }
            }
        }

        if (isset($_itemsArray[$item->getOrderItem()->getId()])) {
            return $_itemsArray[$item->getOrderItem()->getId()];
        } else {
            return null;
        }
    }
    /**
     * Draw item line
     *
     */
    public function draw()
    {
        $order  = $this->getOrder();
        $item   = $this->getItem();
        $pdf    = $this->getPdf();
        $page   = $this->getPage();
        $fontSize = 8;

        $this->_setFontRegular();

        $poItems = $this->getChilds($item);
        $items = array_merge([$item->getOrderItem()], $item->getOrderItem()->getChildrenItems());

        $_prevOptionId = '';
        $drawItems = [];

        foreach ($poItems as $_poItem) {
            $_item = $_poItem->getOrderItem();
            $line   = [];

            $attributes = $this->getSelectionAttributes($_item);
            if (is_array($attributes)) {
                $optionId   = $attributes['option_id'];
            }
            else {
                $optionId = 0;
            }

            if (!isset($drawItems[$optionId])) {
                $drawItems[$optionId] = [
                    'lines'  => [],
                    'height' => 15,
                ];
            }

            if ($_item->getParentItem()) {
                if ($_prevOptionId != $attributes['option_id']) {
                    $line[0] = [
                        'font'  => 'italic',
                        'text'  => $this->strHlp()->split($attributes['option_label'], 60, true, true),
                        'feed'  => 30,
                        'font_size' => $fontSize
                    ];

                    $drawItems[$optionId] = [
                        'lines'  => [$line],
                        'height' => 15,
                    ];

                    $line = [];

                    $_prevOptionId = $attributes['option_id'];
                }
            }

            if (($this->isShipmentSeparately() && $_item->getParentItem())
                || (!$this->isShipmentSeparately() && !$_item->getParentItem())
            ) {
                if (isset($poItems[$_item->getId()])) {
                    $qty = $poItems[$_item->getId()]->getQty()*1;
                } else if ($_item->getIsVirtual()) {
                    $qty = __('N/A');
                } else {
                    $qty = 0;
                }
            } else {
                $qty = '';
            }

            $line[] = [
                'text'  => $qty,
                'feed'  => 475,
                'font_size' => $fontSize
            ];

            // draw Name
            if ($_item->getParentItem()) {
                $feed = 35;
                $name = $this->getValueHtml($_item);
            } else {
                $feed = 30;
                $name = $_item->getName();
            }
            $text = [];
            foreach ($this->strHlp()->split($name, 60, true, true) as $part) {
                $text[] = $part;
            }
            $line[] = [
                'text'  => $text,
                'feed'  => $feed,
                'font_size' => $fontSize
            ];

            // draw SKUs
            $text = [];
            foreach ($this->strHlp()->split($_item->getSku(), 25) as $part) {
                $text[] = $part;
            }
            $line[] = [
                'text'  => $text,
                'feed'  => 255,
                'font_size' => $fontSize
            ];

            if ($_item->getParentItem()) {
                $__qty = $poItems[$_item->getId()]->getQty();
                if ($_item->isDummy(true)) {
                    $__qty = $_item->getOrderItem()->getQtyOrdered()/$item->getOrderItem()->getQtyOrdered();
                    $__qty *= $poItems[$item->getId()]->getQty();
                }
                $costTxt = $order->getBaseCurrency()->formatTxt($_item->getBaseCost());
                $rowCostTxt = $order->getBaseCurrency()->formatTxt($_item->getBaseCost()*$__qty);
            } else {
                $costTxt = '';
                $rowCostTxt = '';
            }
            // draw Price
            $line[] = [
                'text'  => $costTxt,
                'feed'  => 395,
                'font'  => 'bold',
                'align' => 'right',
                'font_size' => $fontSize
            ];

            // draw Subtotal
            $line[] = [
                'text'  => $rowCostTxt,
                'feed'  => 565,
                'font'  => 'bold',
                'align' => 'right',
                'font_size' => $fontSize
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
                        'feed'  => 20,
                        'font_size' => $fontSize
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
                            'feed'  => 35,
                            'font_size' => $fontSize
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

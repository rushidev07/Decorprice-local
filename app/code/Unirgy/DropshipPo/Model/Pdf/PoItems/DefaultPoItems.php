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

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Filesystem;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Pdf\Items\AbstractItems;
use Magento\Tax\Helper\Data as HelperData;

class DefaultPoItems extends AbstractItems
{
    /**
     * @var String
     */
    protected $_strHlp;

    public function __construct(Context $context,
        Registry $registry,
        HelperData $taxData,
        Filesystem $filesystem,
        FilterManager $filterManager,
        \Magento\Framework\Stdlib\StringUtils $helperString,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->_strHlp = $helperString;

        parent::__construct($context, $registry, $taxData, $filesystem, $filterManager, $resource, $resourceCollection, $data);
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
        $lines  = [];
        $fontSize = 8;

        // draw Product name
        $lines[0] = [[
            'text' => $this->_strHlp->split($item->getName(), 50, true, true),
            'feed' => 35,
            'font_size' => $fontSize
        ]];

        // draw SKU
        $lines[0][] = [
            'text'  => $this->_strHlp->split($this->getSku($item), 25),
            'feed'  => 255,
            'font_size' => $fontSize
        ];

        // draw QTY
        $lines[0][] = [
            'text'  => $item->getQty()*1,
            'feed'  => 475,
            'font_size' => $fontSize
        ];

        // draw Price
        $lines[0][] = [
            'text'  => $order->getBaseCurrency()->formatTxt($item->getBaseCost()),
            'feed'  => 395,
            'font'  => 'bold',
            'align' => 'right',
            'font_size' => $fontSize
        ];

        // draw Subtotal
        $lines[0][] = [
            'text'  => $order->getBaseCurrency()->formatTxt($item->getBaseCost()*$item->getQty()),
            'feed'  => 565,
            'font'  => 'bold',
            'align' => 'right',
            'font_size' => $fontSize
        ];

        // custom options
        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $lines[][] = [
                    'text' => $this->_strHlp->split(strip_tags($option['label']), 70, true, true),
                    'font' => 'italic',
                    'feed' => 35,
                    'font_size' => $fontSize
                ];

                if ($option['value']) {
                    $_printValue = isset($option['print_value']) ? $option['print_value'] : strip_tags($option['value']);
                    $values = explode(', ', $_printValue);
                    foreach ($values as $value) {
                        $lines[][] = [
                            'text' => $this->_strHlp->split($value, 50, true, true),
                            'feed' => 40,
            'font_size' => $fontSize
                        ];
                    }
                }
            }
        }

        $lineBlock = [
            'lines'  => $lines,
            'height' => 10
        ];

        $page = $pdf->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $this->setPage($page);
    }
}

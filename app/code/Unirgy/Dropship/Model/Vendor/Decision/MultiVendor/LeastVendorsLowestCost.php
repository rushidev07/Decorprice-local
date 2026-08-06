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
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\Dropship\Model\Vendor\Decision\MultiVendor;

class LeastVendorsLowestCost
    extends AbstractDecision
{
    public function apply($items)
    {
        $iHlp = $this->_iHlp;
        $this->collectStockLevels($items);
        $this->beforeApply($items);

        $initUniqueVendors = $this->getInitUniqueVendors();
        $stock = $this->getStockToApply();
        if (!$stock) {
            $this->afterApply($items);
            return $this;
        }
        $inStockByVendor = [];
        $pl = sizeof($stock);
        $pl = 0;
        foreach ($stock as $pId => $vendors) {
            $foundInStock = false;
            foreach ($vendors as $vId => $v) {
                if (!empty($v['status'])) {
                    $foundInStock = true;
                    $inStockByVendor[$vId] = @$inStockByVendor[$vId]+1;
                }
            }
            if ($foundInStock) {
                $pl++;
            }
        }
        $this->setInStockByVendor($inStockByVendor);
        // flatten products array to allow simple iteration
        usort($stock, [$this, 'flattenCallback']);
        foreach ($stock as &$vendors) {
            usort($vendors, [$this, 'flattenCallback2']);
        }
        unset($vendors);
#echo "<pre>"; print_r($stock); echo "</pre>"; exit;
        $combinations = [];
        $vis = [];
        $iterCnt = $pl>10 ? 10000 : 10000;
        // 10000 should be enough to iterate through all combinations
        for ($safeguard=0; $safeguard<$iterCnt; $safeguard++) {
            // collect combination from current vendor ids
            $combination = [];
            $uniqueVendors = $initUniqueVendors;
            $totalCost = 0;
            foreach ($stock as $pi=>$vendors) {
                // reset vendor ids
                if (empty($vis[$pi])) {
                    $vis[$pi] = 0;
                }
                if (empty($vendors)) {
                    continue;
                }
                // add vendor to combination
                $v = $vendors[$vis[$pi]];
                if (empty($v['status'])) {
                    continue;
                }
                $combination['products'][$pi] = [
                    'p' => $v['product_id'],
                    'v' => $v['vendor_id'],
                    'c' => $v['vendor_cost'],
                ];
                // update combination totals
                $uniqueVendors[$v['vendor_id']] = @$v['priority'];
                $totalCost += $v['vendor_cost'];
            }

            // if all products are in stock for this combination
            if (!empty($combination['products'])
                && sizeof($combination['products'])==$pl
                && $this->_hlp->verifyDecisionCombination($items, $combination['products'])
            ) {
                // add combination to list
                $combination['num_vendors'] = sizeof($uniqueVendors);
                $combination['total_priority'] = array_sum($uniqueVendors);
                $combination['total_cost'] = $totalCost;
                $combinations[] = $combination;
            }

            // find next vendor ids
            foreach ($vis as $pi=>&$vi) {
                $vi++;
                if ($vi<sizeof($stock[$pi])) {
                    break;
                }
                $vi = 0;
                if ($pi==$pl-1) {
                    break 2;
                }
            }
            unset($vi);
        }

        // no combinations found (with all products in stock)
        if (empty($combinations)) {
            $this->afterApply($items);
            return $this;
        }

        // find the most efficient combination (after sorting it is the first)
        usort($combinations, [$this, 'sortCombinationsCallback']);
        $combination = $combinations[0]['products'];

        $iHlp = $this->_iHlp;
        // set optimized vendor ids to cart items
        foreach ($items as $item) {
            if ($iHlp->getStickedVendorIdOption($item) || $item->getSkipUdropshipDecisionApply()) continue;
            foreach ($combination as $v) {
                if ($item->getProductId()==$v['p']) {
                    $iHlp->setUdropshipVendor($item, $v['v']);
                    if ($v['c']!==null && $v['c']!=='') {
                        $item->setCost($v['c']);
                        $item->setBaseCost($v['c']);
                    }
                    break;
                }
            }
            if ($item->getHasChildren()) {
                $children = $item->getChildrenItems() ? $item->getChildrenItems() : $item->getChildren();
                foreach ($children as $child) {
                    $product = $child->getProduct();
		            $pId = $child->getProductId();
		            if (!$product || !$product->hasUdropshipVendor()) {
		                // if not available, load full product info to get product vendor
		                $product = $this->_hlp->createObj('Magento\Catalog\Model\Product')->load($pId);
		            }
                    /*if ($product->getTypeInstance()->isVirtual()) {
                        continue;
                    }*/
                    foreach ($combination as $v) {
                        if ($child->getProductId()==$v['p']) {
                            $iHlp->setUdropshipVendor($item, $v['v']);
                            $iHlp->setUdropshipVendor($child, $v['v']);
                            if ($v['c']!==null && $v['c']!=='') {
                            $child->setCost($v['c']);
                            $child->setBaseCost($v['c']);
                            if ($item->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                                $item->setCost($v['c']);
                                $item->setBaseCost($v['c']);
                            }
                            }
                            break;
                        }
                    }
                }
            }
        }

        $this->afterApply($items);

        return $this;
    }

    public function flattenCallback($v1, $v2)
    {
        return 0;
    }
    public function flattenCallback2($v1, $v2)
    {
        if (@$v1['status']>@$v2['status']) {
            return -1;
        } elseif (@$v1['status']<@$v2['status']) {
            return 1;
        }
        $iV1 = $this->getInStockByVendor(@$v1['vendor_id']);
        $iV2 = $this->getInStockByVendor(@$v2['vendor_id']);
        if ($iV1>$iV2) {
            return -1;
        } elseif ($iV1<$iV2) {
            return 1;
        }
        return 0;
    }

    public function sortCombinationsCallback($c1, $c2)
    {
        if ($c1['num_vendors']<$c2['num_vendors']) {
            return -1;
        } elseif ($c1['num_vendors']>$c2['num_vendors']) {
            return 1;
        }
        if ($c1['total_cost']<$c2['total_cost']) {
            return -1;
        } elseif ($c1['total_cost']>$c2['total_cost']) {
            return 1;
        }
        if ($c1['total_priority']<$c2['total_priority']) {
            return -1;
        } elseif ($c1['total_priority']>$c2['total_priority']) {
            return 1;
        }
        return 0;
    }

    //public function calculateTotalCost
}

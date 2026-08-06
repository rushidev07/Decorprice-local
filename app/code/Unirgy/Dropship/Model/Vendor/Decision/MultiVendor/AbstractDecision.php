<?php

namespace Unirgy\Dropship\Model\Vendor\Decision\MultiVendor;

use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\DataObject;
use Unirgy\Dropship\Model\Vendor\Decision\AbstractDecision as BaseAbstractDecision;

class AbstractDecision extends BaseAbstractDecision
{
    public function beforeApply($items)
    {
        $iHlp = $this->_iHlp;
        $_stock = $this->getStockResult();
        $initUniqueVendors = [];
        $applyPids = [];
        foreach ($items as $item) {
            $item->setSkipUdropshipDecisionApply(false);
            if ($item->getHasChildren()) {
                continue;
            }
            $applyPid = $item->getProductId();
            if (($_forcedVid = $iHlp->getForcedVendorIdOption($item))) {
                $item->setSkipUdropshipDecisionApply(true);
                $iHlp->setUdropshipVendor($item, $_forcedVid);
                if ($item->getParentItem()) {
                    $item->getParentItem()->setSkipUdropshipDecisionApply(true);
                    $iHlp->setUdropshipVendor($item->getParentItem(), $_forcedVid);
                }
                $initUniqueVendors[$_forcedVid] = @$_stock[$applyPid][$_forcedVid]['priority'];
            } else {
                $applyPids[$applyPid] = $applyPid;
            }
        }

        $stock = [];
        foreach ($_stock as $_sK=>$_sV) {
            if (!empty($_sV) && is_array($_sV)) {
                $hasInStock = false;
                $__defVendors = $_sV;
                uasort($__defVendors, [$this, 'sortDefaultVendorCallback']);
                reset($__defVendors);
                $__defVendor = key($__defVendors);

                foreach ($items as $item) {
                    if ($item->getHasChildren() || $item->getProductId()!=$_sK) {
                        continue;
                    }
                    if (!in_array($item->getUdropshipVendor(), array_keys($_sV)) && $__defVendor) {
                        $iHlp->setUdropshipVendor($item, $__defVendor);
                    }
                }

                foreach ($_sV as $_svK=>$_svV) {
                    if (!empty($_svV['status'])) {
                        $hasInStock = true;
                        if (!empty($_svV['is_priority_vendor'])) {
                            unset($applyPids[$_sK]);
                            foreach ($items as $item) {
                                if ($item->getHasChildren() || $item->getProductId()!=$_sK) {
                                    continue;
                                }
                                $item->setSkipUdropshipDecisionApply(true);
                                $iHlp->setUdropshipVendor($item, $_svK);
                                if ($item->getParentItem()) {
                                    $item->getParentItem()->setSkipUdropshipDecisionApply(true);
                                    $iHlp->setUdropshipVendor($item->getParentItem(), $_svK);
                                }
                                $initUniqueVendors[$_forcedVid] = @$_stock[$_sK][$_svK]['priority'];
                            }
                        }
                    }
                }
                if ($hasInStock && in_array($_sK, $applyPids)) {
                    $stock[$_sK] = $_sV;
                }
            }
        }
        $this->setInitUniqueVendors($initUniqueVendors);
        $this->setStockToApply($stock);
        return $this;
    }
    public function sortDefaultVendorCallback($c1, $c2)
    {
        if (@$c1['vendor_cost']<@$c2['vendor_cost']) {
            return -1;
        } elseif (@$c1['vendor_cost']>@$c2['vendor_cost']) {
            return 1;
        }
        if (@$c1['priority']<@$c2['priority']) {
            return -1;
        } elseif (@$c1['priority']>@$c2['priority']) {
            return 1;
        }
        return 0;
    }
    public function afterApply($items)
    {
        $stock = $this->getStockResult();
        $hlp = $this->_hlp;
        $quote = null;
        $qtyUsed = [];
        $allAddressMatch = true;
        $allZipcodeMatch = true;
        $allCountryMatch = true;
        $hasOutOfStock = false;
        $allowedQtyError = false;
        //foreach ($stock as $pId=>$vendors) {
        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            if (empty($quote)) {
                if ($item->getOrder()) {
                    return $this;
                }
                $quote = $item->getQuote();
            }
            $_children = $item->getChildrenItems() ? $item->getChildrenItems() : $item->getChildren();
            if ($_children) {
                $children = [];
                foreach ($_children as $_child) {
                    if (false !== array_search($_child, $items, true)) {
                        $children[] = $_child;
                    }
                }
                $childrenByPid = [];
                foreach ($children as $child) {
                    $childrenByPid[$child->getProductId()][$child->getUdropshipVendor()][] = $child;
                }
                foreach ($childrenByPid as $pId => $_children) {
                    reset($_children);
                    $vId = key($_children);
                    if (empty($qtyUsed[$pId][$vId])) {
                        $qtyUsed[$pId][$vId] = 0;
                    }
                    $v = $hlp->getVendor($vId);
                    $_children = current($_children);
                    $stockData = (array)@$stock[$pId][$vId];
                    $stockItem = null;
                    $qtyInStock = @$stockData['qty_in_stock'];
                    $addressMatch = $v->isAddressMatch($hlp->getAddressByItem($item));
                    $zipCodeMatch = $v->isZipcodeMatch($hlp->getZipcodeByItem($item));
                    $countryMatch = $v->isCountryMatch($hlp->getCountryByItem($item));
                    $allAddressMatch = $allAddressMatch && $addressMatch;
                    $allZipcodeMatch = $allZipcodeMatch && $zipCodeMatch;
                    $allCountryMatch = $allCountryMatch && $countryMatch;
                    $childName = $item->getName();
                    $childQtyUsed = 0;
                    $childStockStatus = true;
                    foreach ($_children as $_child) {
                        $stockItem = $this->_hlp->getStockItem($_child->getProduct());
                        $stockStatus = @$stockData['per_item_data'][spl_object_hash($_child)]['stock_status'];
                        $childStockStatus = $childStockStatus && $stockStatus;
                        if ($item->getProductType()!='configurable') {
                            $childName = $_child->getName();
                        }
                        $qtyToCheck = $hlp->getItemStockCheckQty($_child);
                        if (!$addressMatch) {
                            $this->_setAddressError($_child);
                            $_child->setMessage(__('This item is not available for your location.'));
                            continue;
                        }
                        if (!$countryMatch) {
                            $this->_setAddressError($_child);
                            $_child->setMessage(__('This item is not available for your country.'));
                            continue;
                        }
                        if (!$zipCodeMatch) {
                            $this->_setAddressError($_child);
                            $_child->setMessage(__('This item is not available for your zipcode.'));
                            continue;
                        }
                        $_qtyInStock = @$stockData['qty_in_stock']-@$qtyUsed[$pId][$vId]-$childQtyUsed;
                        $itemInStock = $_qtyInStock>=$qtyToCheck;
                        $childQtyUsed += $qtyToCheck;
                        if ($_qtyInStock<=0 && !$stockStatus) {
                            $hasOutOfStock = true;
                            $_child->setHasError(true);
                            $_child->setMessage(__('This product is currently out of stock.'));
                        } elseif (!$itemInStock && $_qtyInStock>0 && !$stockStatus) {
                            $hasOutOfStock = true;
                            $_child->setHasError(true);
                            $_child->setMessage(__('Only "%1" of this product in stock.', $_qtyInStock*1));
                        } elseif ($stockStatus
                            && !$itemInStock
                            && @$stockData['backorders'] == Stock::BACKORDERS_YES_NOTIFY
                        ) {
                            $backorderQty = $_qtyInStock>0 ? $qtyToCheck-$_qtyInStock : $qtyToCheck;
                            $_child->setBackorders($backorderQty);
                            $_child->setMessage(
                                __('This product is not available in the requested quantity. %1 of the items will be backordered.', ($backorderQty * 1))
                            );
                        }
                    }
                    $_qtyInStock = @$stockData['qty_in_stock']-@$qtyUsed[$pId][$vId];
                    $itemInStock = $_qtyInStock>=$childQtyUsed;
                    @$qtyUsed[$pId][$vId] += $childQtyUsed;
                    $qtyOptions = $item->getQtyOptions();
                    $qtyOption = @$qtyOptions[$pId];
                    $addressError = $stockError = $message = null;
                    if (!$addressMatch) {
                        $addressError = true;
                        if ($item->getProductType()=='configurable') {
                            $message = __('This product is not available for your location.');
                        } else {
                            $message = __('"%1" is not available for your location.', $childName);
                        }
                    } elseif (!$countryMatch) {
                        $addressError = true;
                        if ($item->getProductType()=='configurable') {
                            $message = __('This product is not available for your country.');
                        } else {
                            $message = __('"%1" is not available for your country.', $childName);
                        }
                    } elseif (!$zipCodeMatch) {
                        $addressError = true;
                        if ($item->getProductType()=='configurable') {
                            $message = __('This product is not available for your zipcode.');
                        } else {
                            $message = __('"%1" is not available for your zipcode.', $childName);
                        }
                    } elseif ($stockItem && $stockItem->getMinSaleQty() && $childQtyUsed < $stockItem->getMinSaleQty()) {
                        $allowedQtyError = true;
                        $stockError = true;
                        if ($item->getProductType()=='configurable') {
                            $message = __('The minimum quantity allowed for purchase is %1.', $stockItem->getMinSaleQty() * 1);
                        } else {
                            $message = __('The minimum quantity allowed for purchase for "%1" is %2.', $childName, $stockItem->getMinSaleQty() * 1);
                        }
                    } elseif ($stockItem && $stockItem->getMaxSaleQty() && $childQtyUsed > $stockItem->getMaxSaleQty()) {
                        $allowedQtyError = true;
                        $stockError = true;
                        if ($item->getProductType()=='configurable') {
                            $message = __('The maximum quantity allowed for purchase is %1.', $stockItem->getMaxSaleQty() * 1);
                        } else {
                            $message = __('The maximum quantity allowed for purchase for "%1" is %2.', $childName, $stockItem->getMaxSaleQty() * 1);
                        }
                    } elseif ($_qtyInStock<=0 && !$childStockStatus) {
                        $hasOutOfStock = true;
                        $stockError = true;
                        if ($item->getProductType()=='configurable') {
                            $message = __('This product is currently out of stock.');
                        } else {
                            $message = __('"%1" is currently out of stock.', $childName);
                        }
                    }  elseif (!$itemInStock && $_qtyInStock>0 && !$childStockStatus) {
                        $hasOutOfStock = true;
                        $stockError = true;
                        if ($item->getProductType()=='configurable') {
                            $message = __('Only "%1" of this product in stock.', $_qtyInStock*1);
                        } else {
                            $message = __('Only "%1" of "%2" in stock.', $_qtyInStock*1, $childName);
                        }
                    } elseif ($childStockStatus
                        && !$itemInStock
                        && @$stockData['backorders'] == Stock::BACKORDERS_YES_NOTIFY
                    ) {
                        $backorderQty = $_qtyInStock>0 ? $childQtyUsed-$_qtyInStock : $childQtyUsed;
                        if ($item->getProductType()=='configurable') {
                            $message = __('This product is not available in the requested quantity. %1 of the items will be backordered.', ($backorderQty * 1));
                        } else {
                            $message = __('"%1" is not available in the requested quantity. %2 of the items will be backordered.', $childName, ($backorderQty * 1));
                        }
                    }
                    if ($stockError || $addressError) {
                        if ($stockError) $this->_setStockError($item);
                        if ($addressError) $this->_setAddressError($item);
                        if ($qtyOption instanceof DataObject) {
                            if ($stockError) $this->_setStockError($qtyOption);
                            if ($addressError) $this->_setAddressError($qtyOption);
                        }
                    }
                    if ($message) {
                        $item->setMessage($message);
                        if ($qtyOption instanceof DataObject) {
                            $qtyOption->setMessage($message);
                        }
                    }
                }
            } else {
                $vId = $item->getUdropshipVendor();
                $v = $hlp->getVendor($vId);
                $pId = $item->getProductId();
                $stockData = (array)@$stock[$pId][$vId];
                $stockItem = $this->_hlp->getStockItem($item->getProduct());
                $stockStatus = @$stockData['per_item_data'][spl_object_hash($item)]['stock_status'];
                $qtyInStock = @$stockData['qty_in_stock'];
                $addressMatch = $v->isAddressMatch($hlp->getAddressByItem($item));
                $zipCodeMatch = $v->isZipcodeMatch($hlp->getZipcodeByItem($item));
                $countryMatch = $v->isCountryMatch($hlp->getCountryByItem($item));
                $allAddressMatch = $allAddressMatch && $addressMatch;
                $allZipcodeMatch = $allZipcodeMatch && $zipCodeMatch;
                $allCountryMatch = $allCountryMatch && $countryMatch;
                $qtyToCheck = $hlp->getItemStockCheckQty($item);
                if (!$addressMatch) {
                    $this->_setAddressError($item);
                    $item->setMessage(__('This item is not available for your location.'));
                } elseif (!$countryMatch) {
                    $this->_setAddressError($item);
                    $item->setMessage(__('This item is not available for your country.'));
                } elseif (!$zipCodeMatch) {
                    $this->_setAddressError($item);
                    $item->setMessage(__('This item is not available for your zipcode.'));
                } elseif ($stockItem && $stockItem->getMinSaleQty() && $qtyToCheck < $stockItem->getMinSaleQty()) {
                    $allowedQtyError = true;
                    $this->_setStockError($item);
                    $item->setMessage(__('The minimum quantity allowed for purchase is %1.', $stockItem->getMinSaleQty() * 1));
                } elseif ($stockItem && $stockItem->getMaxSaleQty() && $qtyToCheck > $stockItem->getMaxSaleQty()) {
                    $allowedQtyError = true;
                    $this->_setStockError($item);
                    $item->setMessage(__('The maximum quantity allowed for purchase is %1.', $stockItem->getMaxSaleQty() * 1));
                } else {
                    if (empty($qtyUsed[$pId][$vId])) {
                        $qtyUsed[$pId][$vId] = 0;
                    }
                    $_qtyInStock = @$stockData['qty_in_stock']-$qtyUsed[$pId][$vId];
                    $itemInStock = $_qtyInStock>=$qtyToCheck;
                    $qtyUsed[$pId][$vId] += $qtyToCheck;
                    if ($_qtyInStock<=0 && !$stockStatus) {
                        $hasOutOfStock = true;
                        $this->_setStockError($item);
                        $item->setMessage(__('This product is currently out of stock.'));
                    } elseif (!$itemInStock && $_qtyInStock>0 && !$stockStatus) {
                        $hasOutOfStock = true;
                        $this->_setStockError($item);
                        $item->setMessage(__('Only "%1" of this product in stock.', $_qtyInStock*1));
                    } elseif ($stockStatus
                        && !$itemInStock
                        && @$stockData['backorders'] == Stock::BACKORDERS_YES_NOTIFY
                    ) {
                        $backorderQty = $_qtyInStock>0 ? $qtyToCheck-$_qtyInStock : $qtyToCheck;
                        $item->setBackorders($backorderQty);
                        $item->setMessage(
                            __('This product is not available in the requested quantity. %1 of the items will be backordered.', ($backorderQty * 1))
                        );
                    }
                }
            }
        }
        //}
        if (!$allAddressMatch) {
            $this->_setAddressError($quote);
            $quote->addMessage(
                __('Some items are not available for your location.')
            );
        }
        if (!$allCountryMatch) {
            $this->_setAddressError($quote);
            $quote->addMessage(
                __('Some items are not available for your country.')
            );
        }
        if (!$allZipcodeMatch) {
            $this->_setAddressError($quote);
            $quote->addMessage(
                __('Some items are not available for your zipcode.')
            );
        }
        if ($allowedQtyError) {
            $this->_setStockError($quote);
            $quote->addMessage(
                __('Some of the products cannot be ordered in requested quantity.')
            );
        }
        if ($hasOutOfStock) {
            $this->_setStockError($quote);
            $quote->addMessage(__('Some of the products are currently out of stock'));
        }
        return $this;
    }
    protected function _setStockError($entity)
    {
        $entity->setHasError(true);
        $entity->setHasStockError(true);
        return $this;
    }
    protected function _setAddressError($entity)
    {
        //$entity->setHasError(true);
        $entity->setHasAddressError(true);
        return $this;
    }
    public function collectStockLevels($items, $options=[])
    {
        $availability = $this->_stockAvailability;
        $availability->collectStockLevels($items, $options);
        $this->setStockResult($availability->getStockResult());
        return $this;
    }
}

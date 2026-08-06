<?php

namespace Unirgy\Dropship\Plugin;

class CategoryTree
{
    protected $_hlp;
    public function __construct(
        \Unirgy\Dropship\Helper\Data $udropshipHelper
    ) {
        $this->_hlp = $udropshipHelper;
    }
    public function afterGetSuggestedCategoriesJson(\Magento\Catalog\Block\Adminhtml\Category\Tree $subject, $result)
    {
        if ($this->getIsLimitCategories()) {
            $lc = $this->getLimitCategories();
            $decodedResult = $this->_hlp->jsonDecode($result);
            if (in_array($this->getIsLimitCategories(), [1,2])) {
                foreach ($decodedResult as &$root) {
                    $this->deactivateCategories($root);
                }
            }
            $result = $this->_hlp->jsonEncode($decodedResult);
        }
        return $result;
    }
    public function getIsLimitCategories()
    {
        return $this->getVendor() ? $this->getVendor()->getIsLimitCategories() : false;
    }
    public function getLimitCategories()
    {
        $lc = false;
        if (($vendor = $this->getVendor()) && $vendor->getIsLimitCategories()) {
            if (!$vendor->getLimitCategoriesArr()) {
                $lc = explode(',', implode(',', (array)$vendor->getLimitCategories()));
                $vendor->setLimitCategoriesArr($lc);
            }
            $lc = $vendor->getLimitCategoriesArr();
        }
        return $lc;
    }
    protected function getVendor()
    {
        $vendor = $this->_hlp->session()->getVendor();
        return $vendor && $vendor->getId() ? $vendor : false;
    }
    private function deactivateCategories(&$node)
    {
        $limitCategories = $this->getLimitCategories();
        $isLimitFlag = $this->getIsLimitCategories()==2;
        if ($limitCategories && is_array($limitCategories)
            && in_array($node['id'], $limitCategories) == $isLimitFlag
        ) {
            $node['udrestrict'] = 1;
        }

        if (!isset($node['children'])) {
            return;
        }

        foreach ($node['children'] as &$child) {
            $this->deactivateCategories($child);
        }
        foreach ($node['children'] as $idx=>$child) {
            if (isset($child['udrestrict'])) {
                unset($node['children'][$idx]);
            }
        }
        $node['children'] = array_values($node['children']);
    }
}

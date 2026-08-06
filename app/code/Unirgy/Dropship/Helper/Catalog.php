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
 * @package    \Unirgy\Dropship
 * @copyright  Copyright (c) 2015-2016 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\Dropship\Helper;

use \Magento\Eav\Model\Config;
use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Helper\Context;
use \Magento\Framework\Db\Select;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\App\ObjectManager;
use \Magento\Catalog\Model\Category;

class Catalog extends AbstractHelper
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Config
     */
    protected $_eavConfig;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->_storeManager = $storeManager;
        $this->_eavConfig = $eavConfig;

        parent::__construct($context);
    }

    /**
     * @return \Magento\CatalogInventory\Model\Configuration
     */
    protected function _invConfig()
    {
        return ObjectManager::getInstance()->get('Magento\CatalogInventory\Model\Configuration');
    }
    /**
     * @return \Magento\Catalog\Model\Category
     */
    protected function _createCategory()
    {
        return ObjectManager::getInstance()->create('Magento\Catalog\Model\Category');
    }
    /**
     * @return \Unirgy\Dropship\Helper\Data
     */
    protected function _hlp()
    {
        return ObjectManager::getInstance()->get('Unirgy\Dropship\Helper\Data');
    }

    /**
     * @return \Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected function _flatState()
    {
        return $this->_hlp()->getObj('Magento\Catalog\Model\Indexer\Category\Flat\State');
    }

    public function isQty($product)
    {
        return $this->_invConfig()->isQty($product->getTypeId());
    }
    protected $_topCats;
    public function getTopCategories()
    {
        if (null === $this->_topCats) {
            $this->_topCats = $this->getCategoryChildren(
                $this->getStoreRootCategory($this->getStore())
            );
        }
        return $this->_topCats;
    }
    public function getStore()
    {
        return $this->_storeManager->getDefaultStoreView();
    }
    protected $_rootCnt;
    protected $_storeRootCategory = [];
    public function getStoreRootCategory($store = null)
    {
        if ($this->_rootCnt===null) {
            $res = $this->_hlp()->rHlp();
            $read = $res->getMyConnection('catalog');
            $select = $read->select()
                ->from($res->getTableName('store_group'), 'COUNT(distinct root_category_id)')
                ->where('root_category_id!=0');
            $this->_rootCnt = $read->fetchOne($select);
        }
        if ($store === null && $this->_rootCnt>1) {
            $rootId = Category::TREE_ROOT_ID;
        } else {
            $store = $this->_storeManager->getStore($store);
            $rootId = $store->getRootCategoryId();
            if (!$rootId) {
                $rootId = $this->getStore()->getRootCategoryId();
            }
            if (!$rootId) {
                $rootId = $this->getStore()->getGroup()->getRootCategoryId();
            }
            if (!$rootId) {
                $rootId = Category::TREE_ROOT_ID;
            }
        }
        if (!isset($this->_storeRootCategory[$rootId])) {
            $this->_storeRootCategory[$rootId] = $this->_createCategory()->load($rootId);
        }
        return $this->_storeRootCategory[$rootId];
    }
    public function getPathInStore($cat)
    {
        $result = [];
        $path = array_reverse($cat->getPathIds());
        foreach ($path as $itemId) {
            if ($itemId == $this->getStore()->getRootCategoryId()) {
                break;
            }
            $result[] = $itemId;
        }
        return implode(',', $result);
    }
    public function getCategoryChildren($cId, $active = true, $recursive = false)
    {
        return $this->_getCategoryChildren($cId, $active, $recursive);
    }
    protected function _getCategoryChildren($cId, $active = true, $recursive = false, $orderBy = 'level,position')
    {
        if ($cId instanceof Category) {
            $cat = $cId;
        } else {
            $cat = $this->_createCategory()->load($cId);
        }
        $collection = $cat->getCollection()
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('all_children')
            ->addAttributeToSelect('is_anchor');
        $orderBy = explode(',', $orderBy);
        foreach ($orderBy as $ob) {
            $ob = explode(':', $ob);
            $ob[1] = !empty($ob[1]) ? $ob[1] : 'asc';
            $collection->setOrder($ob[0], $ob[1]);
        }
        if ($this->_flatState()->isAvailable()) {
            $collection->addUrlRewriteToResult();
        } else {
            $collection->joinUrlRewrite();
        }
        if ($active) {
            $collection->addAttributeToFilter('is_active', 1);
        }
        $collection->getSelect()->where('path LIKE ?', "{$cat->getPath()}/%");
        if (!$recursive) {
            $collection->getSelect()->where('level <= ?', $cat->getLevel() + 1);
        }
        return $collection;
    }
    public function getCategoriesCollection($cIds, $active = true, $orderBy = 'level,position')
    {
        $collection = $this->_createCategory()->getCollection()
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('all_children')
            ->addAttributeToSelect('is_anchor');
        $orderBy = explode(',', $orderBy);
        foreach ($orderBy as $ob) {
            $ob = explode(':', $ob);
            $ob[1] = !empty($ob[1]) ? $ob[1] : 'asc';
            $collection->setOrder($ob[0], $ob[1]);
        }
        if ($this->_flatState()->isAvailable()) {
            $collection->addUrlRewriteToResult();
        } else {
            $collection->joinUrlRewrite();
        }
        if ($active) {
            $collection->addAttributeToFilter('is_active', 1);
        }
        if ($this->_flatState()->isAvailable()) {
            $collection->addFieldToFilter('main_table.entity_id', ['in' => $cIds]);
        } else {
            $collection->addIdFilter($cIds);
        }
        return $collection;
    }
    public function processCategoriesData(&$fCatIds, $returnArray = true)
    {
        if (!is_array($fCatIds)) {
            if (strpos($fCatIds, 'a:')===0) {
                $fCatIds = @unserialize($fCatIds);
            } elseif (strpos($fCatIds, '{')===0) {
                $fCatIds = \Zend_Json::decode($fCatIds);
            }
        }
        if (is_array($fCatIds) && !$returnArray) {
            $fCatIds = implode(',', $fCatIds);
        } elseif (!is_array($fCatIds) && $returnArray) {
            $fCatIds = explode(',', $fCatIds);
        }
        $fCatIds = $fCatIds === null ? '' : $fCatIds;
        return $this;
    }
    protected $_store;
    protected $_oldStore;
    protected $_oldArea;
    protected $_oldDesign;
    protected $_oldTheme;

    protected $_isEmulating = false;

    public function setDesignStore($store = null, $area = \Magento\Framework\App\Area::AREA_FRONTEND, $theme = null)
    {
        /** @var \Magento\Store\Model\App\Emulation $appEmulation */
        $appEmulation = $this->_hlp()->getObj('\Magento\Store\Model\App\Emulation');
        if (!is_null($store)) {
            if ($this->_isEmulating) {
                return $this;
            }
            $this->_isEmulating = true;
            $store = $this->_storeManager->getStore($store);
            $appEmulation->startEnvironmentEmulation($store->getId(), $area, true);
            if ($theme) {
                /** @var \Magento\Framework\View\DesignInterface $viewDesign */
                $viewDesign = $this->_hlp()->getObj('\Magento\Framework\View\DesignInterface');
                try {
                    $viewDesign->setDesignTheme($theme, $area);
                } catch (\Exception $e) {
                }
            }
        } else {
            if (!$this->_isEmulating) {
                return $this;
            }
            $appEmulation->stopEnvironmentEmulation();
            $this->_isEmulating = false;
        }

        return $this;
    }
    public function getPidBySku($sku, $excludePids = null)
    {
        return $this->_getPidBySku($sku, $excludePids);
    }
    public function getPidBySkuForUpdate($sku, $excludePids = null)
    {
        return $this->_getPidBySku($sku, $excludePids, true);
    }
    protected function _getPidBySku($sku, $excludePids = null, $forUpdate = false)
    {
        $res = $this->_hlp()->rHlp();
        $read = $res->getMyConnection('catalog');
        $table = $res->getTableName('catalog_product_entity');
        $select = $read->select()
            ->from($table, 'entity_id')
            ->where('sku = :sku');
        $bind = [':sku' => (string)trim($sku)];
        if (!empty($excludePids)) {
            if (!is_array($excludePids)) {
                $excludePids = [$excludePids];
            }
            $select->where('entity_id not in (?)', $excludePids);
        }
        if ($forUpdate) {
            $select->forUpdate(true);
        }
        return $read->fetchOne($select, $bind);
    }
    public function getPidByVendorSku($vSku, $vId, $excludePids = null)
    {
        $pId = null;
        if ($this->_hlp()->isUdmultiActive()) {
            $res = $this->_hlp()->rHlp();
            $read = $res->getMyConnection('udropship');
            $table = $res->getTableName('udropship_vendor_product');
            $select = $read->select()
                ->from($table, 'product_id')
                ->where('vendor_sku = :vendor_sku and vendor_id = :vendor_id');
            $bind = [':vendor_sku' => (string)trim($vSku), ':vendor_id' => $vId];
            if (!empty($excludePids)) {
                if (!is_array($excludePids)) {
                    $excludePids = [$excludePids];
                }
                $select->where('product_id not in (?)', $excludePids);
            }
            $pId = $read->fetchOne($select, $bind);
        } else {
            $vSkuAttr = $this->_hlp()->getScopeConfig('udropship/vendor/vendor_sku_attribute');
            if ($vSkuAttr && $vSkuAttr!='sku') {
                $attrFilters = [[
                    'attribute' => $vSkuAttr,
                    'in' => [$vSku]
                ]];
                if (!empty($excludePids)) {
                    if (!is_array($excludePids)) {
                        $excludePids = [$excludePids];
                    }
                    $attrFilters[] = [
                        'attribute' => 'entity_id',
                        'nin' => $excludePids
                    ];
                }
                /* @var \Magento\Catalog\Model\ResourceModel\Product\Collection $prodCol */
                $prodCol = $this->_hlp()->createObj('\Magento\Catalog\Model\Product')->getCollection();
                $prodCol->setPage(1, 1)
                    ->addAttributeToSelect($vSkuAttr)
                    ->addAttributeToFilter('udropship_vendor', $vId);
                foreach ($attrFilters as $attrFilter) {
                    $prodCol->addAttributeToFilter($attrFilter['attribute'], $attrFilter);
                }
                $pId = $prodCol->getFirstItem()->getId();
            }
        }
        return $pId;
    }
    public function getVendorSkuByPid($pId, $vId)
    {
        $vSku = null;
        if ($this->_hlp()->isUdmultiActive()) {
            $res = $this->_hlp()->rHlp();
            $read = $res->getMyConnection('udropship');
            $table = $res->getTableName('udropship_vendor_product');
            $select = $read->select()
                ->from($table, 'vendor_sku')
                ->where('product_id = :product_id and vendor_id = :vendor_id');
            $bind = [':product_id' => (string)trim($pId), ':vendor_id' => $vId];
            $vSku = $read->fetchOne($select, $bind);
        } else {
            $vSkuAttr = $this->_hlp()->getScopeConfig('udropship/vendor/vendor_sku_attribute');
            if ($vSkuAttr && $vSkuAttr!='sku') {
                $attrFilters = [[
                    'attribute' => 'entity_id',
                    'in' => [$pId]
                ]];
                /* @var \Magento\Catalog\Model\ResourceModel\Product\Collection $prodCol */
                $prodCol = $this->_hlp()->createObj('\Magento\Catalog\Model\Product')->getCollection();
                $prodCol->setPage(1, 1)
                    ->addAttributeToSelect($vSkuAttr)
                    ->addAttributeToFilter($attrFilters);
                if ($prodCol->getFirstItem()->getId()) {
                    $vSku = $prodCol->getFirstItem()->getData($vSkuAttr);
                }
            }
        }
        return $vSku;
    }

    public function reindexPids($pIds)
    {
        /* @var \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry */
        $indexerRegistry = $this->_hlp()->getObj('\Magento\Framework\Indexer\IndexerRegistry');
        /* @var \Magento\Indexer\Model\Config $indexerConfig */
        $indexerConfig = $this->_hlp()->getObj('\Magento\Indexer\Model\Config');

        foreach ([
            \Unirgy\Dropship\Model\Indexer\ProductVendorAssoc\Processor::INDEXER_ID,
            \Magento\CatalogInventory\Model\Indexer\Stock\Processor::INDEXER_ID,
            \Magento\Catalog\Model\Indexer\Product\Price\Processor::INDEXER_ID,
            \Magento\Catalog\Model\Indexer\Product\Flat\Processor::INDEXER_ID,
            \Magento\Catalog\Model\Indexer\Product\Eav\Processor::INDEXER_ID,
            \Magento\Catalog\Model\Indexer\Product\Category\Processor::INDEXER_ID,
            \Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID,
            'udropship_product_vendor_limit'
        ] as $indexerId) {
            if (!$indexerConfig->getIndexer($indexerId)) {
                continue;
            }
            $indexer = $indexerRegistry->get($indexerId);
            if ($indexer && !$indexer->isScheduled()) {
                $indexer->reindexList($pIds);
            }
        }

        foreach ($pIds as $pId) {
            //$this->_modelUrl->refreshProductRewrite($pId);
        }
    }

    public function getWebsiteValues($hash = false, $selector = true)
    {
        $values = [];
        if ($selector) {
            if ($hash) {
                $values[''] = __('* Select category');
            } else {
                $values[] = ['label'=>__('* Select category'), 'value'=>''];
            }
        }
        foreach ($this->_storeManager->getWebsites() as $website) {
            if ($hash) {
                $values[$website->getId()] = $website->getName();
            } else {
                $values[] = ['label'=>$website->getName(), 'value'=>$website->getId()];
            }
        }
        return $values;
    }
    public function getCategoryValues($hash = false, $selector = true)
    {
        $values = [];
        if ($selector) {
            if ($hash) {
                $values[''] = __('* Select category');
            } else {
                $values[] = ['label'=>__('* Select category'), 'value'=>''];
            }
        }
        $cat = $this->getStoreRootCategory();
        $this->_attachCategoryValues($cat, $values, 0, $hash);
        return $values;
    }
    protected function _attachCategoryValues($cat, &$values, $level, $hash = false)
    {
        $children = $cat->getChildrenCategories();
        if (count($children)>0) {
            if ($hash) {
                $values[$cat->getId()] = $cat->getName();
            } else {
                $values[] = ['label'=>$cat->getName(), 'value'=>$cat->getId(), 'level'=>$level, 'disabled'=>true];
            }
            $level+=1;
            foreach ($children as $child) {
                $this->_attachCategoryValues($child, $values, $level, $hash);
            }
        } else {
            if ($hash) {
                $values[$cat->getId()] = $cat->getName();
            } else {
                $values[] = ['label'=>$cat->getName(), 'value'=>$cat->getId(), 'level'=>$level];
            }
        }
        return $this;
    }

    public function createCfgAttr($cfgProd, $cfgAttrId, $pos)
    {
        $cfgPid = $cfgProd;
        if ($cfgProd instanceof \Magento\Catalog\Model\Product) {
            $cfgPid = $cfgProd->getData($this->_hlp()->rowIdField());
        }
        $res = $this->_hlp()->rHlp();
        $write = $res->getMyConnection('catalog');
        $superAttrTable = $res->getTableName('catalog_product_super_attribute');
        $superLabelTable = $res->getTableName('catalog_product_super_attribute_label');

        $exists = $write->fetchRow("select sa.*, sal.value_id, sal.value label from {$superAttrTable} sa
            inner join {$superLabelTable} sal on sal.product_super_attribute_id=sa.product_super_attribute_id
            where sa.product_id={$cfgPid} and sa.attribute_id={$cfgAttrId} and sal.store_id=0");
        if (!$exists) {
            $write->insert($superAttrTable, [
                'product_id' => $cfgPid,
                'attribute_id' => $cfgAttrId,
                'position' => $pos,
            ]);
            $saId = $write->lastInsertId($superAttrTable);
            $write->insert($superLabelTable, [
                'product_super_attribute_id' => $saId,
                'store_id' => 0,
                'use_default' => 1,
                'value' => '',
            ]);
        }

        return $this;
    }

    public function getCfgSimpleSkus($cfgPid)
    {
        $res = $this->_hlp()->rHlp();
        $write = $res->getMyConnection('catalog');
        $t = $res->getTableName('catalog_product_super_link');
        $t2 = $res->getTableName('catalog_product_entity');
        return $write->fetchCol("select {$t2}.sku from {$t} inner join {$t2} on {$t2}.entity_id={$t}.product_id
            where parent_id='{$cfgPid}'");
    }

    public function getCfgSimplePids($cfgPid)
    {
        $res = $this->_hlp()->rHlp();
        $write = $res->getMyConnection('catalog');
        $t = $res->getTableName('catalog_product_super_link');
        $t2 = $res->getTableName('catalog_product_entity');
        return $write->fetchCol("select {$t2}.entity_id from {$t} inner join {$t2} on {$t2}.entity_id={$t}.product_id
            where parent_id='{$cfgPid}'");
    }

    public function unlinkCfgSimple($cfgPid, $simpleSku, $byPid = false)
    {
        $res = $this->_hlp()->rHlp();
        $write = $res->getMyConnection('catalog');
        $t = $res->getTableName('catalog_product_super_link');
        $t2 = $res->getTableName('catalog_product_relation');

        $p2 = $byPid ? $simpleSku : $this->getPidBySku($simpleSku);

        $linkId = $write->fetchCol("select link_id from {$t}
            where parent_id='{$cfgPid}' and product_id='{$p2}'");
        if ($linkId) {
            $write->delete($t, $write->quoteInto("link_id in (?)", $linkId));
            $write->delete($t2, "parent_id={$cfgPid} and child_id={$p2}");
        }
        return $this;
    }

    public function linkCfgSimple($cfgPid, $simpleSku, $byPid = false)
    {
        $res = $this->_hlp()->rHlp();
        $write = $res->getMyConnection('catalog');
        $t = $res->getTableName('catalog_product_super_link');

        $p2 = $byPid ? $simpleSku : $this->getPidBySku($simpleSku);

        $linkId = $write->fetchOne("select link_id from {$t} where parent_id='{$cfgPid}' and product_id='{$p2}'");
        if (!$linkId && $p2) {
            $write->insert($t, ['parent_id'=>$cfgPid, 'product_id'=>$p2]);
            $relTable = $res->getTableName('catalog_product_relation');
            if (!$write->fetchOne("select parent_id from {$relTable} where parent_id={$cfgPid} and child_id={$p2}")) {
                $write->insert($relTable, ['parent_id'=>$cfgPid, 'child_id'=>$p2]);
            }
        }
        return $this;
    }
    public function getSortedCategoryChildren($cId, $orderBy, $active = true, $recursive = false)
    {
        return $this->_getCategoryChildren($cId, $active, $recursive, $orderBy);
    }

    public function addProductAttributeToSelect($select, $attrCode, $entity_id)
    {
        $alias = $attrCode;
        if (is_array($attrCode)) {
            reset($attrCode);
            $alias = key($attrCode);
            $attrCode = current($attrCode);
        }
        $attribute = $this->_eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attrCode);
        if (!$attribute || !$attribute->getAttributeId()) {
            $select->columns([$alias=>new \Zend_Db_Expr("''")]);
            return $this;
        }
        $attributeId    = $attribute->getAttributeId();
        $attributeTable = $attribute->getBackend()->getTable();
        $store = $this->_storeManager->getStore()->getId();

        $rowIdField = $this->_hlp()->rowIdField();

        if ($attribute->isScopeGlobal()) {
            $_alias = 'ta_' . $attrCode;
            $select->joinLeft(
                [$_alias => $attributeTable],
                "{$_alias}.{$rowIdField} = {$entity_id} AND {$_alias}.attribute_id = {$attributeId}"
                . " AND {$_alias}.store_id = 0",
                []
            );
            $expression = new \Zend_Db_Expr("{$_alias}.value");
        } else {
            $dAlias = 'tad_' . $attrCode;
            $sAlias = 'tas_' . $attrCode;

            $select->joinLeft(
                [$dAlias => $attributeTable],
                "{$dAlias}.{$rowIdField} = {$entity_id} AND {$dAlias}.attribute_id = {$attributeId}"
                . " AND {$dAlias}.store_id = 0",
                []
            );
            $select->joinLeft(
                [$sAlias => $attributeTable],
                "{$sAlias}.{$rowIdField} = {$entity_id} AND {$sAlias}.attribute_id = {$attributeId}"
                . " AND {$sAlias}.store_id = {$store}",
                []
            );
            $expression = $this->getCheckSql(
                $this->getIfNullSql("{$sAlias}.value_id", -1) . ' > 0',
                "{$sAlias}.value",
                "{$dAlias}.value"
            );
        }

        $select->columns([$alias=>$expression]);

        return $this;
    }

    public function getCaseSql($valueName, $casesResults, $defaultValue = null)
    {
        $expression = 'CASE ' . $valueName;
        foreach ($casesResults as $case => $result) {
            $expression .= ' WHEN ' . $case . ' THEN ' . $result;
        }
        if ($defaultValue !== null) {
            $expression .= ' ELSE ' . $defaultValue;
        }
        $expression .= ' END';

        return new \Zend_Db_Expr($expression);
    }

    public function getCheckSql($expression, $true, $false)
    {
        if ($expression instanceof \Zend_Db_Expr || $expression instanceof Select) {
            $expression = sprintf("IF((%s), %s, %s)", $expression, $true, $false);
        } else {
            $expression = sprintf("IF(%s, %s, %s)", $expression, $true, $false);
        }

        return new \Zend_Db_Expr($expression);
    }

    public function getIfNullSql($expression, $value = 0)
    {
        if ($expression instanceof \Zend_Db_Expr || $expression instanceof Select) {
            $expression = sprintf("IFNULL((%s), %s)", $expression, $value);
        } else {
            $expression = sprintf("IFNULL(%s, %s)", $expression, $value);
        }

        return new \Zend_Db_Expr($expression);
    }
    public function removePriceIndexFromProductCollection($collection, $countSelect)
    {
        $skipTables = [];
        if ($collection->getFlag('udskip_price_index')) {
            $skipTables[] = 'price_index';
            $skipTables[] = 'stock_status_index';
        }
        if ($collection->getFlag('udskip_shared_catalog')) {
            $skipTables[] = 'shared_product';
        }
        if (!empty($skipTables)) {
            $select = $countSelect;
            $fromPart = $select->getPart(\Zend_Db_Select::FROM);
            $columnsPart = $select->getPart(\Zend_Db_Select::COLUMNS);
            $newColumnsPart = $newFromPart = [];
            foreach ($fromPart as $fwIdx => $fromEntry) {
                if (!in_array($fwIdx, $skipTables)) {
                    $newFromPart[$fwIdx] = $fromEntry;
                }
            }
            foreach ($columnsPart as $ceIdx => $columnEntry) {
                if (!in_array($columnEntry[0], $skipTables)) {
                    $newColumnsPart[] = $columnEntry;
                }
            }
            $select->setPart(\Zend_Db_Select::FROM, $newFromPart);
            $select->setPart(\Zend_Db_Select::COLUMNS, $newColumnsPart);
        }
    }

    /**
     * @return \Unirgy\Dropship\Plugin\ProductFlatState
     */
    protected function productFlatStatePlugin()
    {
        return $this->_hlp()->getObj('Unirgy\Dropship\Plugin\ProductFlatState');
    }

    /**
     * @return \Unirgy\Dropship\Plugin\CategoryFlatState
     */
    protected function categoryFlatStatePlugin()
    {
        return $this->_hlp()->getObj('Unirgy\Dropship\Plugin\CategoryFlatState');
    }

    public function getProductFlatDisabled()
    {
        return $this->productFlatStatePlugin()->getProductFlatDisabled();
    }

    public function setProductFlatDisabled($flag)
    {
        return $this->productFlatStatePlugin()->setProductFlatDisabled($flag);
    }

    public function getCategoryFlatDisabled()
    {
        return $this->categoryFlatStatePlugin()->getCategoryFlatDisabled();
    }

    public function setCategoryFlatDisabled($flag)
    {
        return $this->categoryFlatStatePlugin()->setCategoryFlatDisabled($flag);
    }
}

<?php

namespace Unirgy\RapidFlow\Model\ResourceModel\Catalog;

use Magento\Framework\Exception\LocalizedException;
use Unirgy\RapidFlow\Model\ResourceModel\AbstractResource\Fixed as AbstractResourceFixed;

class Fixed
    extends AbstractResourceFixed
{

    protected function _importFetchNewDataIds()
    {
        $fieldValues = [];
        foreach ($this->_newRows as $lineNum => $row) {
            $rowType = trim($row[0]);
            $cmd = $rowType[0];
            $rowType = $cmd === '+' || $cmd === '-' || $cmd === '%' ? substr($rowType, 1) : $rowType;
            if (empty($this->_rowTypeFields[$rowType]['columns'])) {
                continue;
            }
            foreach ($this->_rowTypeFields[$rowType]['columns'] as $fieldName => $fieldNode) {
                $col = (int)$fieldNode->col;
                if (!empty($row[$col])) {
                    $fieldValues[$fieldName][$lineNum] = $row[$col];
                }
            }
        }
        $skus = !empty($fieldValues['sku']) ? $fieldValues['sku'] : [];
        if (!empty($fieldValues['linked_sku'])) {
            $skus = array_merge($skus, $fieldValues['linked_sku']);
        }
        if (!empty($fieldValues['selection_sku'])) {
            $skus = array_merge($skus, $fieldValues['selection_sku']);
        }
        if (!empty($skus)) {
            if (count($this->_skus) > $this->_maxCacheItems['sku']) {
                $this->_skus = [];
            }
//            $select = "SELECT entity_id, sku FROM {$this->_t('catalog_product_entity')} WHERE sku IN (" . implode(',', $skus1) . ')';
            $rowId = 'entity_id';
            $columns = [$this->_entityIdField, 'sku'];
            $useSequence = $this->_rapidFlowHelper->hasMageFeature(self::ROW_ID);
            if ($useSequence) { // if is enterprise, the sequence id = entity_id and is used in some tables
                $rowId = $this->_entityIdField;
                $columns[] = 'entity_id';
            }
            $productTable = $this->_t(self::TABLE_CATALOG_PRODUCT_ENTITY);

            if ($this->getAltSku()) {
                $altSkuAttr = $this->getAltSkuAttr();
                $altTable = $this->getAttrTable($altSkuAttr->getData());
                $select = $this->_read->select()
                    ->from(['a'=>$altTable], ['alt_sku'=>'value'])
                    ->join(['p'=>$productTable], "p.{$rowId}=a.{$rowId}", $columns)
                    ->where("a.value in (?)", array_map('strval', $skus))
                    ->where('a.attribute_id in (?)', $altSkuAttr->getId())
                    ->order('a.store_id desc');
            } else {
                $select = $this->_write->select()->from($productTable, $columns)
                    ->where('sku IN (?)', array_map('strval', $skus));
            }
            $rows = $this->_read->fetchAll($select);
            foreach ($rows as $r) {
                $sku = $r['sku'];
                if ($this->getAltSku()) {
                    $sku = $r['alt_sku'];
                }
                $this->_skus[$sku] = $r[$this->_entityIdField];
                if($useSequence){
                    $this->_skuSeq[$sku] = $r['entity_id'];
                }
                if ($this->getAltSku()) {
                    $this->_skuAlt[$sku] = $r['sku'];
                }
            }
        }
    }

    public function getSkuByAlt($altSku)
    {
        if (empty($this->_skus[$altSku])) {
            throw new LocalizedException(__('Invalid SKU (%1)', $altSku));
        }
        return $this->_skuAlt[$altSku];
    }

    protected function _getIdBySku($sku, $isSeqId=false)
    {
        if ($isSeqId) {
            return $this->_getSeqIdBySku($sku);
        }
        if (empty($this->_skus[$sku])) {
            throw new LocalizedException(__('Invalid SKU (%1)', $sku));
        }
        return $this->_skus[$sku];
    }

    protected function _getSeqIdBySku($sku)
    {
        if (!$this->_rapidFlowHelper->hasMageFeature(self::ROW_ID)) {
            return $this->_getIdBySku($sku);
        }
        if (empty($this->_skuSeq[$sku])) {
            throw new LocalizedException(__('Invalid SKU (%1)', $sku));
        }
        return $this->_skuSeq[$sku];
    }

    protected function _importProcessNewData()
    {
        parent::_importProcessNewData();

        $this->_importFetchNewDataIds();
    }
}

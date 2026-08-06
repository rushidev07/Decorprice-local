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

namespace Unirgy\Dropship\Model\ResourceModel;

use \Magento\Catalog\Model\Product;
use \Magento\Eav\Model\Config;
use \Magento\Eav\Model\Entity\AbstractEntity;
use \Magento\Framework\DataObject;
use \Magento\Framework\Db\Select;
use \Magento\Framework\Model\AbstractModel;
use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use \Magento\Framework\Model\ResourceModel\Db\Context;
use \Unirgy\Dropship\Helper\Data as HelperData;

class Helper extends AbstractDb
{
    /**
     * @var HelperData
     */
    protected $_hlp;

    /**
     * @var Config
     */
    protected $_eavConfig;

    public function __construct(
        Context $context,
        HelperData $helper,
        Config $eavConfig
    ) {
    
        $this->_hlp = $helper;
        $this->_eavConfig = $eavConfig;

        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_setResource('udropship');
    }
    public function getResource()
    {
        return $this->_resources;
    }
    public function getTableName($tableName, $connectionName = \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION)
    {
        if (is_array($tableName)) {
            $cacheName = join('@', $tableName);
            list($tableName, $entitySuffix) = $tableName;
        } else {
            $cacheName = $tableName;
            $entitySuffix = null;
        }

        if ($entitySuffix !== null) {
            $tableName .= '_' . $entitySuffix;
        }
        if (!isset($this->_tables[$cacheName])) {
            $this->_tables[$cacheName] = $this->_resources->getTableName($tableName, $connectionName);
        }
        return $this->_tables[$cacheName];
    }
    public function getSalesConnection()
    {
        return $this->_resources->getConnection('sales');
    }
    public function getMyConnection($connectionName = \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION)
    {
        return $this->_resources->getConnection($connectionName);
    }
    static $metaKey;
    static $transport;
    protected static function _metaKey($key)
    {
        self::_transport();
        return self::$metaKey.$key;
    }
    protected static function _setMetaData($key, $value)
    {
        self::_transport()->setData(self::_metaKey($key), $value);
    }
    protected static function _getMetaData($key)
    {
        return self::_transport()->getData(self::_metaKey($key));
    }
    protected static function _transport($init = false)
    {
        if (null === self::$transport) {
            self::$transport = new DataObject();
            self::$metaKey = md5(uniqid(microtime(), true));
        }
        if ($init) {
            self::$metaKey = md5(uniqid(microtime(), true));
            self::$transport->unsetData();
        }
        return self::$transport;
    }
    public function getReadConnection()
    {
        return $this->getConnection();
    }
    public function getWriteConnection()
    {
        return $this->getConnection();
    }
    protected function _normalizeIdFieldWithValue($id, $res)
    {
        if ($id instanceof \Zend_Db_Expr
            || $id instanceof Select
        ) {
            $id = [$res->getIdFieldName()=>$id];
        } elseif ($id===false || is_array($id) && empty($id)) {
            $id = [$res->getIdFieldName()=>false];
        } elseif ($id===true) {
            $id = [$res->getIdFieldName()=>true];
        } elseif ($id===null) {
            $id = [$res->getIdFieldName()=>null];
        } else {
            if (!is_array($id)) {
                $id = [$id];
            }
            reset($id);
            if (is_numeric(key($id)) || count($id)>1) {
                $id = [$res->getIdFieldName()=>$id];
            }
        }
        return $id;
    }
    protected function _prepareResourceData($res, $id, $data, $fields = null)
    {
        $id = $this->_normalizeIdFieldWithValue($id, $res);
        if ($res instanceof AbstractEntity) {
            $table = $res->getEntityTable();
        } elseif (is_object($res)) {
            if (method_exists($res, 'getMainTable')) {
                $table = $res->getMainTable();
            } elseif (method_exists($res, '__toString')) {
                $table = $this->getTable($res->__toString());
            } else {
                throw new \Exception('object cannot be converted to table name string');
            }
        } elseif (is_array($res)) {
            throw new \Exception('array is not acceptable as table name string');
        } else {
            $table = $this->getTable($res);
        }
        if (is_array($fields)) {
            $data = array_intersect_key($data, array_flip($fields));
        }
        self::_transport()->setData($data);
        $preparedData = $this->_prepareDataForTable(self::_transport(), $table);
        if (is_array($fields)) {
            $preparedData = array_intersect_key($preparedData, array_flip($fields));
        }
        self::_setMetaData('id', $id);
        self::_setMetaData('table', $table);
        self::_setMetaData('data', $preparedData);
        self::_setMetaData('is_prepared', true);
        return $this;
    }
    public function myPrepareDataForTable($table, $data, $full = false)
    {
        self::_transport(true);
        self::_transport()->setData($data);
        $result = $this->_myPrepareDataForTable(self::_transport(), $this->getTable($table), $full);
        if (!$full) {
            $result = array_intersect_key($result, $data);
        }
        self::_transport(true);
        return $result;
    }
    protected function _myPrepareDataForTable(DataObject $object, $table, $full = false)
    {
        $data = [];
        $fields = $this->getConnection()->describeTable($table);
        foreach (array_keys($fields) as $field) {
            if ($object->hasData($field) || $full) {
                $fieldValue = $object->getData($field);
                if ($fieldValue instanceof \Zend_Db_Expr) {
                    $data[$field] = $fieldValue;
                } else {
                    if (null !== $fieldValue) {
                        $data[$field] = $this->_prepareValueForSave($fieldValue, $fields[$field]['DATA_TYPE']);
                    } elseif (!empty($fields[$field]['NULLABLE']) || !empty($fields[$field]['PRIMARY'])) {
                        $data[$field] = new \Zend_Db_Expr('NULL');
                    } elseif (isset($fields[$field]['DEFAULT'])) {
                        if ($fields[$field]['DEFAULT'] == 'CURRENT_TIMESTAMP') {
                            $data[$field] = new \Zend_Db_Expr('CURRENT_TIMESTAMP');
                        } else {
                            $data[$field] = $fields[$field]['DEFAULT'];
                        }
                    } else {
                        $data[$field] = '';
                    }
                }
            }
        }
        return $data;
    }

    public function updateModelFields(AbstractModel $model, $fields)
    {
        self::_transport(true);
        $this->_prepareResourceData($model->getResource(), $model->getId(), $model->getData(), $fields);
        $this->_updateTableData();
        self::_transport(true);
        return $this;
    }
    public function updateModelData($model, $data, $id = null)
    {
        if (is_string($model)) {
            $model = $this->_hlp->createObj($model);
        }
        if (null === $id) {
            $id = $model->getId();
        }
        if (!$model instanceof AbstractModel) {
            throw new \Exception('$model should be instance of AbstractModel');
        }
        self::_transport(true);
        $this->_prepareResourceData($model->getResource(), $id, $data);
        $this->_updateTableData();
        self::_transport(true);
        return $this;
    }
    public function updateTableData($table, $idFieldWithValue, $data)
    {
        self::_transport(true);
        $this->_prepareResourceData($table, $idFieldWithValue, $data);
        $this->_updateTableData();
        self::_transport(true);
        return $this;
    }
    protected function _updateTableData()
    {
        if (!self::_getMetaData('is_prepared')) {
            throw new \Exception('Nothing prepared for update');
        }
        $idFieldWithValue = self::_getMetaData('id');
        $table = self::_getMetaData('table');
        $preparedData = self::_getMetaData('data');
        reset($idFieldWithValue);
        $_idField = key($idFieldWithValue);
        $_idValue = current($idFieldWithValue);
        $condition = $this->getConnection()->quoteInto($_idField.' in (?)', $_idValue);
        $this->getConnection()->update($table, $preparedData, $condition);
        return $this;
    }

    public function insertIgnore($table, $data)
    {
        $table = $this->getTable($table);
        self::_transport(true)->setData($data);
        $preparedData = $this->_prepareDataForTable(self::_transport(), $table);
        $write = $this->getConnection();
        if (!empty($preparedData)) {
            $write->query(sprintf(
                "insert ignore into %s (%s) values (%s)",
                $write->quoteIdentifier($table),
                implode(',', array_map([$write, 'quoteIdentifier'], array_keys($preparedData))),
                $write->quote($preparedData)
            ));
        }
        self::_transport(true);
        return $this;
    }

    public function multiInsertIgnore($table, $multiData)
    {
        $table = $this->getTable($table);
        $write = $this->getConnection();
        $preparedData = [];
        $preparedDataKeys = [];
        foreach ($multiData as $data) {
            self::_transport(true)->setData($data);
            $_preparedData = $this->_myPrepareDataForTable(self::_transport(), $table, true);
            if (empty($_preparedData)) {
                continue;
            }
            if (empty($preparedDataKeys)) {
                $preparedDataKeys = implode(',', array_map([$write, 'quoteIdentifier'], array_keys($_preparedData)));
            }
            $preparedData[] = $write->quote($_preparedData);
        }
        if (!empty($preparedData)) {
            $multiInsert = sprintf(
                "insert ignore into %s (%s) values (%s)",
                $write->quoteIdentifier($table),
                $preparedDataKeys,
                implode('),(', $preparedData)
            );
            $write->query($multiInsert);
        }
        self::_transport(true);
        return $this;
    }

    public function loadModelField(AbstractModel $model, $field)
    {
        $data = $this->_loadModelFields($model, [$field], false);
        return @$data[$field];
    }
    public function loadModelFieldForUpdate(AbstractModel $model, $field)
    {
        $data = $this->_loadModelFields($model, [$field], true);
        return @$data[$field];
    }
    public function loadModelFields(AbstractModel $model, $fields)
    {
        return $this->_loadModelFields($model, $fields, false);
    }
    public function loadModelFieldsForUpdate(AbstractModel $model, $fields)
    {
        return $this->_loadModelFields($model, $fields, true);
    }
    protected function _loadModelFields(AbstractModel $model, $fields, $forUpdate = false)
    {
        self::_transport(true);
        $this->_prepareResourceData($model->getResource(), $model->getId(), array_flip($fields), $fields);
        $idFieldWithValue = self::_getMetaData('id');
        $table = self::_getMetaData('table');
        $preparedData = self::_getMetaData('data');
        $preparedFields = array_keys($preparedData);
        reset($idFieldWithValue);
        $_idField = key($idFieldWithValue);
        $_idValue = current($idFieldWithValue);
        $conn = $model->getResource()->getConnection();
        $condition = $conn->quoteInto($_idField.' in (?)', $_idValue);
        $loadSel = $conn->select()->from($table, $preparedFields)->where($condition);
        if ($forUpdate) {
            $loadSel->forUpdate(true);
        }
        $data = $conn->fetchRow($loadSel);
        self::_transport(true);
        return $data;
    }
    public function loadDbColumnsForUpdate(AbstractModel $model, $ids, $fields, $extraCondition = '')
    {
        return $this->_loadDbColumns($model, $ids, $fields, $extraCondition, true);
    }
    public function loadDbColumns(AbstractModel $model, $ids, $fields, $extraCondition = '')
    {
        return $this->_loadDbColumns($model, $ids, $fields, $extraCondition, false);
    }
    protected function _loadDbColumns(AbstractModel $model, $ids, $fields, $extraCondition = '', $forUpdate = false)
    {
        self::_transport(true);
        $this->_prepareResourceData($model->getResource(), $ids, array_flip($fields), $fields);
        $conn = $model->getResource()->getConnection();
        $idFieldWithValue = self::_getMetaData('id');
        $table = self::_getMetaData('table');
        $preparedData = self::_getMetaData('data');
        $preparedFields = array_keys($preparedData);
        reset($idFieldWithValue);
        $_idField = key($idFieldWithValue);
        $_idValue = current($idFieldWithValue);
        $excludeIdField = false;
        if (!in_array($_idField, $preparedFields)) {
            $preparedFields[] = $_idField;
            $excludeIdField = true;
        }
        $condition = '1';
        if ($_idValue instanceof \Zend_Db_Expr
            || $_idValue instanceof Select
        ) {
            $condition .= $conn->quoteInto(" AND $_idField ?", $_idValue);
        } elseif ($_idValue===false) {
            $condition .= " AND false";
        } elseif ($_idValue===null) {
            $condition .= $conn->quoteInto(" AND $_idField IS NULL", $_idValue);
        } elseif ($_idValue === true) {
        } else {
            $condition .= $conn->quoteInto(" AND $_idField in (?)", $_idValue);
        }
        $loadSel = $conn->select()->from($table, $preparedFields);
        $loadSel->where($condition);
        if ($model->getData('__udload_order')) {
            $__uloArr = $model->getData('__udload_order');
            if (!is_array($__uloArr)) {
                $__uloArr = [$__uloArr];
            }
            foreach ($__uloArr as $__ulo) {
                $__order = str_replace('{{table}}', $table, (string)$__ulo);
                if ($__ulo instanceof \Zend_Db_Expr) {
                    $__order = new \Zend_Db_Expr($__order);
                }
                if ($__order) {
                    $loadSel->order($__order);
                }
            }
        }
        if (!empty($extraCondition)) {
            $extraCondition = str_replace('{{table}}', $table, $extraCondition);
            $loadSel->where($extraCondition);
        }
        if ($forUpdate) {
            $loadSel->forUpdate(true);
        }
        $result = [];
        $data = $conn->fetchAll($loadSel);
        if (is_array($data) && !empty($data)) {
            foreach ($data as $tmp) {
                $__idValue = $tmp[$_idField];
                $result[$__idValue] = $tmp;
                if ($excludeIdField) {
                    unset($result[$__idValue][$_idField]);
                }
            }
        }
        self::_transport(true);
        return $result;
    }
    public function multiInsertOnDuplicate($table, $data, $fields = [])
    {
        $table = $this->getTable($table);
        $preparedData = [];
        foreach ($data as $single) {
            self::_transport(true)->setData($single);
            $preparedData[] = $this->_myPrepareDataForTable(self::_transport(), $table);
        }
        $write = $this->getConnection();
        $write->insertOnDuplicate($table, $preparedData, $fields);
        return $this;
    }
    public function getOrderItemPoInfo($order)
    {
        $conn = $order->getResource()->getConnection();
        $itemIds = [];
        foreach ($order->getAllItems() as $item) {
            $itemIds[] = $item->getId();
        }
        if ($this->_hlp->isUdpoActive()) {
            $select = $conn->select()
                ->from(['oi'=>$this->getTable('sales_order_item')], [])
                ->join(['poi'=>$this->getTable('udropship_po_item')], 'poi.order_item_id=oi.item_id', [])
                ->join(['po'=>$this->getTable('udropship_po')], 'poi.parent_id=po.entity_id', [])
                ->where('oi.item_id in (?)', $itemIds);
            $select->columns(['po.increment_id','oi.item_id','poi.qty','po.udropship_vendor', 'po.udropship_status']);
            $rows = $conn->fetchAll($select);
        } else {
            $select = $conn->select()
                ->from(['oi'=>$this->getTable('sales_order_item')], [])
                ->join(['poi'=>$this->getTable('sales_shipment_item')], 'poi.order_item_id=oi.item_id', [])
                ->join(['po'=>$this->getTable('sales_shipment')], 'poi.parent_id=po.entity_id', [])
                ->where('oi.item_id in (?)', $itemIds);
            $select->columns(['po.increment_id','oi.item_id','poi.qty','po.udropship_vendor', 'po.udropship_status']);
            $rows = $conn->fetchAll($select);
        }
        return $rows;
    }
    public function getProductVendors($pId, $source = 'both')
    {
        if (!is_array($pId)) {
            if ($pId instanceof Product) {
                $pId = $pId->getId();
            }
            $pId = [$pId];
        }
        $products = [];

        $read = $this->getConnection();

        if (in_array($source, ['both','attribute'])) {
            $attr = $this->_eavConfig->getAttribute('catalog_product', 'udropship_vendor');
            $table = $attr->getBackend()->getTable();
            $select = $read->select()
                ->from($table, [$this->_hlp->rowIdField(),'value'])
                ->where('attribute_id=?', $attr->getId())
                ->where($this->_hlp->rowIdField().' in (?)', $pId);
            $rows = $read->fetchAll($select);
            foreach ($rows as $row) {
                $products[$row[$this->_hlp->rowIdField()]] = [$row['value']=>$row['value']];
            }
        }
        if (in_array($source, ['both','table'])) {
            $select = $read->select()
                ->from($this->getTable('udropship_vendor_product'), ['product_id','vendor_id'])
                ->where('product_id in (?)', $pId);
            $rows = $read->fetchAll($select);
            foreach ($rows as $row) {
                $_pId = $row['product_id'];
                $_vId = $row['vendor_id'];
                if (empty($products[$_pId])) {
                    $products[$_pId] = [];
                }
                $products[$_pId] = $products[$_pId]+[$_vId=>$_vId];
            }
        }

        return $products;
    }
}

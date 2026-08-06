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

namespace Unirgy\Dropship\Model\ResourceModel\Vendor\Statement;

use \Magento\Eav\Model\Config;
use \Magento\Framework\Db\Expr;
use \Magento\Framework\Model\AbstractModel;
use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use \Magento\Framework\Model\ResourceModel\Db\Context;
use \Unirgy\Dropship\Helper\Data as HelperData;

abstract class AbstractStatement extends AbstractDb
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
        HelperData $helperData,
        Config $eavConfig,
        Context $context
    ) {
    
        $this->_hlp = $helperData;
        $this->_eavConfig = $eavConfig;

        parent::__construct($context);
    }

    abstract public function initAdjustmentsCollection($statement);
    abstract protected function _getRowTable();
    abstract protected function _getAdjustmentTable();
    abstract protected function _cleanAdjustmentTable($statement);
    abstract protected function _cleanRowTable($statement);

    public function fixStatementDate($vendor, $poType, $stPoStatuses, $dateFrom = null, $dateTo = null)
    {
        $rHlp = $this->_hlp->rHlp();
        $conn = $rHlp->getSalesConnection();
        if ('po' == $poType) {
            if (!is_array($stPoStatuses)) {
                $stPoStatuses = explode(',', $stPoStatuses);
            }
            $sdInsSelect = sprintf(
                "INSERT INTO %s (entity_id,statement_date) %s ON DUPLICATE KEY UPDATE statement_date=values(statement_date)",
                $this->getTable('udropship_po'),
                $conn->select()
                    ->from(['st' => $this->getTable('udropship_po')], [])
                    ->where('st.udropship_vendor=?', $vendor->getId())
                    ->where('st.udropship_status in (?)', $stPoStatuses)
                    ->where("st.statement_date is null or st.statement_date='0000-00-00 00:00:00'")
                    ->where(!is_null($dateFrom) ? $conn->quoteInto('st.created_at>=?', $dateFrom) : '1')
                    ->where(!is_null($dateTo) ? $conn->quoteInto('st.created_at<=?', $dateTo) : '1')
                    ->columns(['entity_id', 'statement_date' => 'st.created_at'])
            );
            //$this->_helperData->dump($sdInsSelect, 'fixStatementDate');
            $conn->query($sdInsSelect);
            $sdInsSelect = sprintf(
                "INSERT INTO %s (entity_id,statement_date) %s ON DUPLICATE KEY UPDATE statement_date=values(statement_date)",
                $this->getTable('udropship_po_grid'),
                $conn->select()
                    ->from(['st' => $this->getTable('udropship_po_grid')], [])
                    ->where('st.udropship_vendor=?', $vendor->getId())
                    ->where('st.udropship_status in (?)', $stPoStatuses)
                    ->where("st.statement_date is null or st.statement_date='0000-00-00 00:00:00'")
                    ->where(!is_null($dateFrom) ? $conn->quoteInto('st.created_at>=?', $dateFrom) : '1')
                    ->where(!is_null($dateTo) ? $conn->quoteInto('st.created_at<=?', $dateTo) : '1')
                    ->columns(['entity_id', 'statement_date' => 'st.created_at'])
            );
            //$this->_helperData->dump($sdInsSelect, 'fixStatementDate');
            $conn->query($sdInsSelect);
        } else {
            if (!is_array($stPoStatuses)) {
                $stPoStatuses = explode(',', $stPoStatuses);
            }
            $sdInsSelect = sprintf(
                "INSERT INTO %s (entity_id,statement_date) %s ON DUPLICATE KEY UPDATE statement_date=values(statement_date)",
                $this->getTable('sales_shipment'),
                $conn->select()
                    ->from(['st' => $this->getTable('sales_shipment')], [])
                    ->where('st.udropship_vendor=?', $vendor->getId())
                    ->where('st.udropship_status in (?)', $stPoStatuses)
                    ->where("st.statement_date is null or st.statement_date='0000-00-00 00:00:00'")
                    ->where(!is_null($dateFrom) ? $conn->quoteInto('st.created_at>=?', $dateFrom) : '1')
                    ->where(!is_null($dateTo) ? $conn->quoteInto('st.created_at<=?', $dateTo) : '1')
                    ->columns(['entity_id', 'statement_date' => 'st.created_at'])
            );
            //$this->_helperData->dump($sdInsSelect, 'fixStatementDate');
            $conn->query($sdInsSelect);
            $sdInsSelect = sprintf(
                "INSERT INTO %s (entity_id,statement_date) %s ON DUPLICATE KEY UPDATE statement_date=values(statement_date)",
                $this->getTable('sales_shipment_grid'),
                $conn->select()
                    ->from(['st' => $this->getTable('sales_shipment')], [])
                    ->join(['stg' => $this->getTable('sales_shipment_grid')], 'stg.entity_id=st.entity_id', [])
                    ->where('st.udropship_vendor=?', $vendor->getId())
                    ->where('st.udropship_status in (?)', $stPoStatuses)
                    ->where("st.statement_date is null or st.statement_date='0000-00-00 00:00:00'")
                    ->where(!is_null($dateFrom) ? $conn->quoteInto('st.created_at>=?', $dateFrom) : '1')
                    ->where(!is_null($dateTo) ? $conn->quoteInto('st.created_at<=?', $dateTo) : '1')
                    ->columns(['entity_id', 'statement_date' => 'st.created_at'])
            );
            //$this->_helperData->dump($sdInsSelect, 'fixStatementDate');
            $conn->query($sdInsSelect);
        }
    }

    protected function _prepareRowSave($statement, $row)
    {
        $row['row_json'] = \Zend_Json::encode($row);
        $row = array_merge($row, $row['amounts']);
        return $row;
    }
    protected function _prepareAdjustmentSave($statement, $adjustment)
    {
        $adjustment['adjustment_prefix'] = isset($adjustment['forced_adjustment_prefix'])
            ? $adjustment['forced_adjustment_prefix']
            : $statement->getAdjustmentPrefix();
        return $adjustment;
    }
    
    protected $_tableColumns = [];
    protected function _initTableColumns($table)
    {
        if (!isset($this->_tableColumns[$table])) {
            $_columns = $this->getConnection()->describeTable($table);
            $this->_tableColumns[$table] = [];
            foreach ($_columns as $_k => $_c) {
                if (!$_c['IDENTITY']) {
                    $this->_tableColumns[$table][$_k] = $_c;
                }
            }
        }
        return $this;
    }
    public function getTableColumns($table, $returnKeys = true)
    {
        $this->_initTableColumns($table);
        return $returnKeys
            ? array_keys($this->_tableColumns[$table])
            : $this->_tableColumns[$table];
    }
    protected function _prepareTableInsert($table, $data, $returnSql = true)
    {
        $this->_initTableColumns($table);
        $row = [];
        foreach ($this->_tableColumns[$table] as $key => $column) {
            if (isset($data[$key])) {
                $row[] = $this->_prepareValueForSave($data[$key], $column['DATA_TYPE']);
            } elseif ($column['NULLABLE']) {
                $row[] = new \Zend_Db_Expr('NULL');
            } elseif (isset($column['DEFAULT'])) {
                if ($column['DEFAULT'] == 'CURRENT_TIMESTAMP') {
                    $row[] = new \Zend_Db_Expr('CURRENT_TIMESTAMP');
                } else {
                    $row[] = $column['DEFAULT'];
                }
            } else {
                $row[] = '';
            }
        }
        return $returnSql
            ? implode(',', array_map([$this->getConnection(), 'quote'], $row))
            : $row;
    }
    
    protected function _saveRows(AbstractModel $object)
    {
        $this->_cleanRowTable($object);
        if ($object->getOrders()) {
            $rows = [];
            $rawRows = [];
            foreach ($object->getOrders() as $order) {
                $_row = $this->_prepareTableInsert($this->_getRowTable(), $this->_prepareRowSave($object, $order), false);
                foreach ($_row as $_r) {
                    $rawRows[] = $_r;
                }
                $rows[] = implode(',', array_fill(0, count($_row), '?'));
            }
            $this->getConnection()->query(sprintf(
                'INSERT INTO %s (%s) VALUES (%s) %s',
                $this->_getRowTable(),
                implode(',', $this->getTableColumns($this->_getRowTable())),
                implode('),(', $rows),
                $this->_hlp->createOnDuplicateExpr($this->getConnection(), $this->getTableColumns($this->_getRowTable()))
            ), $rawRows);
        }
        return $this;
    }

    protected function _saveAdjustments(AbstractModel $object)
    {
        $this->_cleanAdjustmentTable($object);
        $adjRows = [];
        foreach ($object->getAdjustmentsCollection() as $adjustment) {
            $adjRows[] = $this->_prepareTableInsert($this->_getAdjustmentTable(), $this->_prepareAdjustmentSave($object, $adjustment->getData()));
        }
        $object->resetAdjustmentCollection();
        if ($object->getOrders()) {
            foreach ($object->getOrders() as $order) {
                foreach ($order['adjustments'] as $adj) {
                    $adjRows[] = $this->_prepareTableInsert($this->_getAdjustmentTable(), $this->_prepareAdjustmentSave($object, $adj));
                }
            }
        }
        if (!empty($adjRows)) {
            $this->getConnection()->query(sprintf(
                'INSERT INTO %s (%s) VALUES (%s) %s',
                $this->_getAdjustmentTable(),
                implode(',', $this->getTableColumns($this->_getAdjustmentTable())),
                implode('),(', $adjRows),
                $this->_hlp->createOnDuplicateExpr($this->getConnection(), $this->getTableColumns($this->_getAdjustmentTable()))
            ));
            $this->getConnection()->update(
                $this->_getAdjustmentTable(),
                ['adjustment_id' => new \Zend_Db_Expr('concat(adjustment_prefix, id)')],
                'adjustment_id is null'
            );
        }
        return $this;
    }
    
    protected function _cleanStatement(AbstractModel $object)
    {
        if ($object->getOrders()) {
            $this->_changePosAttribute(array_keys($object->getOrders()), $object->getPoType(), 'udropship_payout_status', null, $this->_getCleanExcludePoSelect($object));
        }
        $this->_cleanAdjustmentTable($object);
        return $this;
    }
    protected function _changePosAttribute($poIds, $poType, $poAttr, $poAttrValue, $excludePoSelect = null)
    {
        if (empty($poIds)) {
            return $this;
        }
        $rHlp = $this->_hlp->rHlp();
        $conn = $rHlp->getSalesConnection();
        if (!is_null($excludePoSelect)) {
            $_sTbl = $this->getTable('sales_shipment');
            $poIds = $conn->fetchCol(
                $conn->select()
                    ->from($poType == 'po' ? $this->getTable('udropship_po') : $_sTbl, ['entity_id'])
                    ->where('entity_id in (?)', $poIds)
                    ->where('entity_id not in (?)', $excludePoSelect)
            );
        }
        $conn->update(
            $poType == 'po' ? $this->getTable('udropship_po') : $this->getTable('sales_shipment'),
            [$poAttr=>$poAttrValue],
            $conn->quoteInto('entity_id in (?)', $poIds)
        );
        $conn->update(
            $poType == 'po' ? $this->getTable('udropship_po_grid') : $this->getTable('sales_shipment_grid'),
            [$poAttr=>$poAttrValue],
            $conn->quoteInto('entity_id in (?)', $poIds)
        );
        if ($this->_hlp->isUdpoActive()) {
            if ($poType == 'po') {
                $poCompIds = $conn->fetchCol(
                    $conn->select()
                        ->from($this->getTable('sales_shipment'), ['entity_id'])
                        ->where('udpo_id in (?)', $poIds)
                );
                $conn->update(
                    $this->getTable('sales_shipment'),
                    [$poAttr=>$poAttrValue],
                    $conn->quoteInto('entity_id in (?)', $poCompIds)
                );
                $conn->update(
                    $this->getTable('sales_shipment_grid'),
                    [$poAttr=>$poAttrValue],
                    $conn->quoteInto('entity_id in (?)', $poCompIds)
                );
            } else {
                $poCompIds = $conn->fetchCol(
                    $conn->select()
                        ->from($this->getTable('sales_shipment'), ['udpo_id'])
                        ->where('entity_id in (?)', $poIds)
                );
                $conn->update(
                    $this->getTable('udropship_po'),
                    [$poAttr=>$poAttrValue],
                    $conn->quoteInto('entity_id in (?)', $poCompIds)
                );
                $conn->update(
                    $this->getTable('udropship_po_grid'),
                    [$poAttr=>$poAttrValue],
                    $conn->quoteInto('entity_id in (?)', $poCompIds)
                );
            }
        }
        return $this;
    }
}

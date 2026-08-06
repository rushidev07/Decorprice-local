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

namespace Unirgy\Dropship\Model\Vendor;

use \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\Db\Select;
use \Magento\Framework\Model\App;
use \Unirgy\Dropship\Helper\Catalog;
use \Unirgy\Dropship\Model\Source as ModelSource;

class Source extends AbstractSource
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ModelSource
     */
    protected $_src;

    /**
     * @var Catalog
     */
    protected $_helperCatalog;

    /**
     * @var \Unirgy\Dropship\Helper\Data
     */
    protected $_hlp;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Unirgy\Dropship\Model\Source $source,
        \Unirgy\Dropship\Helper\Data $helper,
        Catalog $helperCatalog
    ) {
    
        $this->scopeConfig = $scopeConfig;
        $this->_hlp = $helper;
        $this->_src = $source;
        $this->_helperCatalog = $helperCatalog;
    }

    protected static $_isEnabled;
    protected function _isEnabled()
    {
        if (is_null(self::$_isEnabled)) {
            self::$_isEnabled = $this->_hlp->isModuleActive('Unirgy_Dropship');
        }
        return self::$_isEnabled;
    }

    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        $options = $this->toOptionArray();
        if ($withEmpty) {
            array_unshift($options, ['label' => '', 'value' => '']);
        }
        return $options;
    }

    public function toOptionArray()
    {
        $source = $this->_getSource();
        return $source ? $source->toOptionArray() : [];
    }

    public function toOptionHash()
    {
        $source = $this->_getSource();
        return $source ? $source->toOptionHash() : [];
    }

    protected function _getSource()
    {
        if (!$this->_isEnabled()) {
            return false;
        }
        return $this->_src->setPath('vendors');
    }

    public function addValueSortToCollection($collection, $dir = Select::SQL_ASC)
    {
        $valueTable1    = $this->getAttribute()->getAttributeCode() . '_t1';
        $valueTable2    = $this->getAttribute()->getAttributeCode() . '_t2';
        $collection->getSelect()
            ->joinLeft(
                [$valueTable1 => $this->getAttribute()->getBackend()->getTable()],
                "e.".$this->_hlp->rowIdField()."={$valueTable1}.".$this->_hlp->rowIdField()
                . " AND {$valueTable1}.attribute_id='{$this->getAttribute()->getId()}'"
                . " AND {$valueTable1}.store_id=0",
                []
            )
            ->joinLeft(
                [$valueTable2 => $this->getAttribute()->getBackend()->getTable()],
                "e.".$this->_hlp->rowIdField()."={$valueTable2}.".$this->_hlp->rowIdField()
                . " AND {$valueTable2}.attribute_id='{$this->getAttribute()->getId()}'"
                . " AND {$valueTable2}.store_id='{$collection->getStoreId()}'",
                []
            );
        $valueExpr = $this->_helperCatalog
            ->getCheckSql("{$valueTable2}.value_id > 0", "{$valueTable2}.value", "{$valueTable1}.value");

        /* @var \Unirgy\Dropship\Model\ResourceModel\Helper $rHlp */
        $rHlp = $this->_hlp->getObj('\Unirgy\Dropship\Model\ResourceModel\Helper');
        $conn = $rHlp->getConnection();

        $attributeCode  = $this->getAttribute()->getAttributeCode();
        $optionTable1   = $attributeCode . '_option_value_t1';
        $tableJoinCond1 = "{$optionTable1}.vendor_id={$valueExpr}";

        $collection->getSelect()
            ->joinLeft(
                [$optionTable1 => $rHlp->getTable('udropship_vendor')],
                $tableJoinCond1,
                [$attributeCode=>$valueExpr, $attributeCode.'_value' => $optionTable1.'.vendor_name']
            );

        $collection->getSelect()
            ->order("{$this->getAttribute()->getAttributeCode()}_value {$dir}");

        return $this;
    }

    public function getFlatColumns()
    {
        return $this->getFlatColums();
    }
    public function getFlatColums()
    {
        $columns = [];
        $attributeCode = $this->getAttribute()->getAttributeCode();

        $columns[$attributeCode] = [
            'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            'unsigned'  => false,
            'is_null'   => true,
            'default'   => null,
            'extra'     => null
        ];
        $columns[$attributeCode . '_value'] = [
            'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'unsigned'  => false,
            'is_null'   => true,
            'default'   => null,
            'extra'     => null
        ];

        return $columns;
    }

    public function getFlatIndexes()
    {
        $indexes = [];

        $index = sprintf('IDX_%s', strtoupper($this->getAttribute()->getAttributeCode()));
        $indexes[$index] = [
            'type'      => 'index',
            'fields'    => [$this->getAttribute()->getAttributeCode()]
        ];

        $sortable   = $this->getAttribute()->getUsedForSortBy();
        if ($sortable) {
            $index = sprintf('IDX_%s_VALUE', strtoupper($this->getAttribute()->getAttributeCode()));

            $indexes[$index] = [
                'type'      => 'index',
                'fields'    => [$this->getAttribute()->getAttributeCode() . '_value']
            ];
        }

        return $indexes;
    }

    public function getFlatUpdateSelect($store)
    {
        /* @var \Unirgy\Dropship\Model\ResourceModel\Helper $rHlp */
        $rHlp = $this->_hlp->getObj('\Unirgy\Dropship\Model\ResourceModel\Helper');
        $conn = $rHlp->getConnection();
        $attribute = $this->getAttribute();
        $adapter        = $conn;
        $attributeTable = $attribute->getBackend()->getTable();
        $attributeCode  = $attribute->getAttributeCode();

        $joinConditionTemplate = "%s.".$this->_hlp->rowIdField()." = %s.".$this->_hlp->rowIdField()
            . " AND %s.attribute_id = " . $attribute->getId()
            . " AND %s.store_id = %d";
        $joinCondition = sprintf($joinConditionTemplate, 'e', 't1', 't1', 't1', 't1', 0);

        $valueExpr = $this->_helperCatalog->getCheckSql('t2.value_id > 0', 't2.value', 't1.value');
        /** @var $select Select */
        $select = $adapter->select()
            ->joinLeft(['t1' => $attributeTable], $joinCondition, [])
            ->joinLeft(
                ['t2' => $attributeTable],
                sprintf($joinConditionTemplate, 't1', 't2', 't2', 't2', 't2', $store),
                [$attributeCode => $valueExpr]
            );

        $select
            ->joinLeft(
                ['to2' => $rHlp->getTable('udropship_vendor')],
                "to2.vendor_id = {$valueExpr}",
                [$attributeCode . '_value' => 'to2.vendor_name']
            );

        return $select;
    }
}

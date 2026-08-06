<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Pquestion\Model\ResourceModel\Product;

use Magento\Catalog\Model\Product\Visibility;

/**
 * Class Collection
 *
 * @package Aheadworks\Pquestion\Model\ResourceModel\Product
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addAttributeToSelect(
            'sku'
        )->addAttributeToSelect(
            'name'
        )->addAttributeToFilter([
            ['attribute' => 'visibility', 'eq' => Visibility::VISIBILITY_IN_CATALOG],
            ['attribute' => 'visibility', 'eq' => Visibility::VISIBILITY_IN_SEARCH],
            ['attribute' => 'visibility', 'eq' => Visibility::VISIBILITY_BOTH]
        ]);
        $this->joinTotals();
    }

    /**
     * Join totals
     *
     * @return $this
     */
    public function joinTotals()
    {
        if (!$this->getFlag('total_count_joined')) {
            $this->getSelect()->joinLeft(
                new \Zend_Db_Expr(
                    "(SELECT COUNT(entity_id) as product_only_questions, product_id"
                    . " FROM {$this->getTable('aw_pq_question')}"
                    . " WHERE sharing_type = "
                    . \Aheadworks\Pquestion\Model\Source\Question\Sharing\Type::ORIGINAL_PRODUCT_VALUE
                    . " GROUP BY product_id)"
                ),
                "e.entity_id = t.product_id",
                ['product_only_questions' => "IFNULL(t.product_only_questions, 0)"]
            );
            $this->getSelect()->joinLeft(
                new \Zend_Db_Expr(
                    "(SELECT COUNT(product_id) as shared_questions"
                    . " FROM {$this->getTable('aw_pq_question')}"
                    . " WHERE sharing_type = "
                    . \Aheadworks\Pquestion\Model\Source\Question\Sharing\Type::ALL_PRODUCTS_VALUE
                    . " GROUP BY product_id)"
                ),
                "1=1",
                ['shared_questions' => "IFNULL(t_2.shared_questions, 0)"]
            );
            $totalQuestionsZendDbExpr = new \Zend_Db_Expr(
                'IFNULL(IFNULL(t.product_only_questions, 0) ' .
                '+ IFNULL(t_2.shared_questions, 0), 0)'
            );
            $this->getSelect()->columns(['total_questions' => $totalQuestionsZendDbExpr], 't');
            $this->getSelect()->group('e.entity_id');
            $this->setFlag('total_count_joined', true);
        }
        return $this;
    }

    /**
     * Custom filter for total_questions, shared_questions, product_only_questions fields
     *
     * @param mixed $attribute
     * @param null  $condition
     *
     * @return $this|\Magento\Framework\Data\Collection\AbstractDb
     */
    public function addFieldToFilter($attribute, $condition = null)
    {
        switch ($attribute) {
            case 'total_questions':
                $where = $this->_getConditionSql(
                    new \Zend_Db_Expr('(IFNULL(t.product_only_questions, 0) + IFNULL(t_2.shared_questions, 0))'),
                    $condition
                );
                $this->getSelect()->where($where);
                return $this;
            case 'shared_questions':
                $where = $this->_getConditionSql('IFNULL(t_2.shared_questions, 0)', $condition);
                $this->getSelect()->where($where);
                return $this;
            case 'product_only_questions':
                $where = $this->_getConditionSql('IFNULL(t.product_only_questions, 0)', $condition);
                $this->getSelect()->where($where);
                return $this;
        }
        return parent::addFieldToFilter($attribute, $condition);
    }

    /**
     * Custom sort for total_questions, shared_questions, product_only_questions fields
     *
     * @param string $attribute
     * @param string $dir
     *
     * @return $this
     */
    public function addAttributeToSort($attribute, $dir = self::SORT_ORDER_ASC)
    {
        switch ($attribute) {
            case 'total_questions':
                $this->getSelect()->order('total_questions' . ' ' . $dir);
                return $this;
            case 'shared_questions':
                $this->getSelect()->order('t_2.shared_questions' . ' ' . $dir);
                return $this;
            case 'product_only_questions':
                $this->getSelect()->order('t.product_only_questions' . ' ' . $dir);
                return $this;
        }
        return parent::addAttributeToSort($attribute, $dir);
    }

    /**
     * {@inheritdoc}
     */
    public function getSelectCountSql()
    {
        return $this->prepareSelectCountSql();
    }

    /**
     * Prepare count select
     *
     * @param null $select
     * @param bool $resetLeftJoins
     * @return \Magento\Framework\DB\Select
     */
    private function prepareSelectCountSql($select = null, $resetLeftJoins = true)
    {
        $this->_renderFilters();
        $countSelect = ($select === null) ? $this->_getClearSelect() : $this->_buildClearSelect($select);
        $countSelect->columns('COUNT(DISTINCT e.entity_id)');
        if ($resetLeftJoins) {
            $countSelect->resetJoinLeft();
        }
        $countSelect->reset(\Zend_Db_Select::GROUP);
        return $countSelect;
    }
}

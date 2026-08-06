<?php

namespace Unirgy\Dropship\Model\ResourceModel;

class ProductCollection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    public function getSelectCountSql()
    {
        return $this->_getSelectCountSqlUd();
    }
    protected function _prepareStatisticsData()
    {
        $select = clone $this->getSelect();
        $priceExpression = $this->getPriceExpression($select) . ' ' . $this->getAdditionalPriceExpression($select);
        $sqlEndPart = ') * ' . $this->getCurrencyRate() . ', 2)';
        $select = $this->_getSelectCountSqlUd($select, false);
        $select->columns(
            [
                'max' => 'ROUND(MAX(' . $priceExpression . $sqlEndPart,
                'min' => 'ROUND(MIN(' . $priceExpression . $sqlEndPart,
                'std' => $this->getConnection()->getStandardDeviationSql('ROUND((' . $priceExpression . $sqlEndPart),
            ]
        );
        $select->where($this->getPriceExpression($select) . ' IS NOT NULL');
        $row = $this->getConnection()->fetchRow($select, $this->_bindParams, \Zend_Db::FETCH_NUM);
        $this->_pricesCount = (int)$row[0];
        $this->_maxPrice = (double)$row[1];
        $this->_minPrice = (double)$row[2];
        $this->_priceStandardDeviation = (double)$row[3];

        return $this;
    }
    protected function _getSelectCountSqlUd($select = null, $resetLeftJoins = true)
    {
        $this->_renderFilters();
        $countSelect = (is_null($select)) ?
            $this->_getClearSelect() :
            $this->_buildClearSelect($select);
        if ($this->getFlag('has_group_entity')) {
            $group = $countSelect->getPart(\Zend_Db_Select::GROUP);
            $newGroup = [];
            foreach ($group as $g) {
                if ("$g" != 'e.entity_id') {
                    $newGroup[] = $g;
                }
            }
            $countSelect->setPart(\Zend_Db_Select::GROUP, $newGroup);
        }
        $countSelect->columns('COUNT(DISTINCT e.entity_id)');
        if ($resetLeftJoins) {
            $countSelect->resetJoinLeft();
        }
        $catHlp = \Magento\Framework\App\ObjectManager::getInstance()->get('\Unirgy\Dropship\Helper\Catalog');
        $catHlp->removePriceIndexFromProductCollection($this, $countSelect);
        return $countSelect;
    }

    protected function _beforeLoad()
    {
        $this->myCatalogHelper()->removePriceIndexFromProductCollection($this, $this->getSelect());
        return parent::_beforeLoad();
    }

    /**
     * @return \Unirgy\Dropship\Helper\Catalog
     */
    protected function myCatalogHelper()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get('Unirgy\Dropship\Helper\Catalog');
    }

    protected function _applyProductLimitations()
    {
        parent::_applyProductLimitations();
        $this->myCatalogHelper()->removePriceIndexFromProductCollection($this, $this->getSelect());
    }
}

<?php

namespace {

    include_once __DIR__ . '/QueryProcessorInterface.php';
}

namespace Unirgy\Dropship\Model\ResourceModel\StockStatus {

    use Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\QueryProcessorInterface;
    use Magento\Framework\DB\Select;
    use Magento\Catalog\Model\ResourceModel\Product\Indexer\AbstractIndexer;

    class QueryProcessor extends AbstractIndexer implements QueryProcessorInterface
    {
        protected $_hlp;

        public function __construct(
            \Unirgy\Dropship\Helper\Data $udropshipHelper,
            \Magento\Framework\Model\ResourceModel\Db\Context $context,
            \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy,
            \Magento\Catalog\Model\Config $eavConfig,
            $connectionName = null
        ) {
        
            $this->_hlp = $udropshipHelper;
            parent::__construct($context, $tableStrategy, $eavConfig, $connectionName);
        }

        protected function _construct()
        {
            $this->_init('cataloginventory_stock_status', 'product_id');
        }

        public function processQuery(Select $select, $entityIds = null, $usePrimaryTable = false)
        {
            if ($this->_hlp->getScopeConfig('udropship/stock/availability') == 'local_if_in_stock') {
                $connection = $this->getConnection();
                $uvExpr = $this->_addAttributeToSelect($select, 'udropship_vendor', 'e.entity_id', '0');
                $localVendorId = $this->_hlp->getLocalVendorId();
                $columns = $select->getPart(Select::COLUMNS);
                $newColumns = [];
                foreach ($columns as $col) {
                    if ($col[2] == 'status') {
                        $col[1] = $connection->getCheckSql("$uvExpr!=$localVendorId", 1, $col[1]);
                    }
                    $newColumns[] = $col;
                }
                $select->setPart(Select::COLUMNS, $newColumns);
            }
            return $select;
        }
    }
}

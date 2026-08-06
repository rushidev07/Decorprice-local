<?php

namespace Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock {

    use Magento\Framework\DB\Select;

    if (!interface_exists('\Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\QueryProcessorInterface')) {
        interface QueryProcessorInterface
        {
            public function processQuery(Select $select, $entityIds = null, $usePrimaryTable = false);
        }
    }
}

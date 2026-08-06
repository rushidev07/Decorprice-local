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
 * @package    Unirgy_DropshipPo
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\DropshipPo\Model\ResourceModel\Po;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Collection\AbstractCollection;
use Psr\Log\LoggerInterface;
use Unirgy\Dropship\Helper\Catalog;
use Unirgy\Dropship\Helper\Data as HelperData;

class Collection extends AbstractCollection
{
    /**
     * @var HelperData
     */
    protected $_hlp;

    /**
     * @var Catalog
     */
    protected $_helperCatalog;

    /**
     * @var OrderFactory
     */
    protected $_orderFactory;

    public function __construct(EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Snapshot $entitySnapshot,
        HelperData $helperData,
        Catalog $helperCatalog,
        OrderFactory $modelOrderFactory,
        AdapterInterface $connection = null,
        AbstractDb $resource = null)
    {
        $this->_hlp = $helperData;
        $this->_helperCatalog = $helperCatalog;
        $this->_orderFactory = $modelOrderFactory;

        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $entitySnapshot, $connection, $resource);
    }

    protected $_eventPrefix = 'udpo_po_collection';
    protected $_eventObject = 'po_collection';
    protected $_orderField = 'order_id';

    protected function _construct()
    {
        $this->_init('Unirgy\DropshipPo\Model\Po', 'Unirgy\DropshipPo\Model\ResourceModel\Po');
    }

    public function afterLoad()
    {
        $this->_afterLoad();
        return $this;
    }
    protected function _afterLoad()
    {
        /*$this->walk('afterLoad');
        if (($stockPo = $this->getStockPo())) {
            foreach ($this->getItems() as $item) {
                $item->setStockPo($stockPo);
            }
        }*/
    }

    public function addPendingBatchStatusFilter()
    {
        return $this->_addPendingBatchStatusFilter();
    }
    public function addPendingBatchStatusVendorFilter($vendor)
    {
        return $this->_addPendingBatchStatusFilter($vendor);
    }
    protected function _addPendingBatchStatusFilter($vendor=null)
    {
        if (is_array($vendor)) {
            $exportOnPoStatusAll = [];
            foreach ($vendor as $vId) {
                $exportOnPoStatus = [];
                if ($vId && ($v = $this->_hlp->getVendor($vId)) && $v->getId()) {
                    $exportOnPoStatus = $v->getData('batch_export_orders_export_on_po_status');
                }
                if (in_array('999', $exportOnPoStatus) || empty($exportOnPoStatus)) {
                    $exportOnPoStatus = $this->_hlp->getScopeConfig('udropship/batch/export_on_po_status');
                    if (!is_array($exportOnPoStatus)) {
                        $exportOnPoStatus = explode(',', $exportOnPoStatus);
                    }
                }
                $exportOnPoStatusAll[(int)$vId] = $this->getSelect()->getAdapter()->quoteInto('main_table.udropship_status in (?)', $exportOnPoStatus);
            }
            $this->getSelect()->where(
                $this->_helperCatalog->getCaseSql('main_table.udropship_vendor', $exportOnPoStatusAll)
            );
        } else {
            $exportOnPoStatus = [];
            if ($vendor && ($vendor = $this->_hlp->getVendor($vendor)) && $vendor->getId()) {
                $exportOnPoStatus = $vendor->getData('batch_export_orders_export_on_po_status');
            }
            if (in_array('999', $exportOnPoStatus) || empty($exportOnPoStatus)) {
                $exportOnPoStatus = $this->_hlp->getScopeConfig('udropship/batch/export_on_po_status');
                if (!is_array($exportOnPoStatus)) {
                    $exportOnPoStatus = explode(',', $exportOnPoStatus);
                }
            }
            $this->getSelect()->where("main_table.udropship_status in (?)", $exportOnPoStatus);
        }
        return $this;
    }
    public function addPendingStockpoBatchStatusFilter()
    {
    	$exportOnPoStatus = $this->_hlp->getScopeConfig('udropship/batch/export_on_stockpo_status');
    	if (!is_array($exportOnPoStatus)) {
    		$exportOnPoStatus = explode(',', $exportOnPoStatus);
    	}
        $this->getSelect()->where("main_table.udropship_status in (?)", $exportOnPoStatus);
        return $this;
    }

    public function addPendingStockpoFilter()
    {
    	$exportOnPoStatus = $this->_hlp->getScopeConfig('udropship/stockpo/generate_on_po_status');
    	if (!is_array($exportOnPoStatus)) {
    		$exportOnPoStatus = explode(',', $exportOnPoStatus);
    	}
        $this->getSelect()->where("main_table.udropship_status in (?)", $exportOnPoStatus);
        $this->getSelect()->where("ustockpo_id is null");
        return $this;
    }

    protected $_orderJoined=false;
    protected function _joinOrderTable()
    {
        if (!$this->_orderJoined) {
            $this->getSelect()->join(
                ['order_table'=>$this->getTable('sales_order')],
                'order_table.entity_id=main_table.order_id',
                []
            );
            $this->_orderJoined = true;
        }
        return $this;
    }

    public function addOrderDateFilter($dateFilter)
    {
        $this->_joinOrderTable();
        $this->addFieldToFilter('order_table.created_at', $dateFilter);
        return $this;
    }


    public function addOrders()
    {
        $orderIds = [];
        foreach ($this as $po) {
            if ($po->getOrderId()) {
                $orderIds[$po->getOrderId()] = 1;
            }
        }

        if ($orderIds) {
            $orders = $this->_orderFactory->create()->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', ['in'=>array_keys($orderIds)]);
            foreach ($this as $po) {
                $po->setOrder($orders->getItemById($po->getOrderId()));
            }
        }
        return $this;
    }

    public function addStockPos()
    {
        $this->addAttributeToSelect('*', 'inner');

        $stockPoIds = [];
        foreach ($this as $po) {
            if ($po->getUstockpoId()) {
                $stockPoIds[$po->getUstockpoId()] = 1;
            }
        }

        if ($stockPoIds) {
            $stockPos = $this->_hlp->createObj('Unirgy\DropshipStockPo\Model\Po')->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', ['in'=>array_keys($stockPoIds)]);
            foreach ($this as $po) {
                $po->setStockPo($stockPos->getItemById($po->getUstockpoId()));
            }
        }
        return $this;
    }

    protected $_stockPo;
    public function setStockPo($stockPo)
    {
        $this->_stockPo = $stockPo;
        return $this;
    }
    public function getStockPo()
    {
        return $this->_stockPo;
    }

    public function setStockPoFilter($stockPo)
    {
        if ($stockPo instanceof \Unirgy\DropshipStockPo\Model\Po) {
            $this->setStockPo($stockPo);
            $stockPoId = $stockPo->getId();
            if ($stockPoId) {
                $this->addFieldToFilter('ustockpo_id', $stockPoId);
            } else {
                $this->_totalRecords = 0;
                $this->_setIsLoaded(true);
            }
        } else {
            $this->addFieldToFilter('ustockpo_id', $stockPo);
        }
        return $this;
    }
}

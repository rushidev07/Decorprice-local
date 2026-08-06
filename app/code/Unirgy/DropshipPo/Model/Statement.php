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
 
namespace Unirgy\DropshipPo\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Helper\Data as TaxHelperData;
use Unirgy\DropshipPo\Model\Po\CommentFactory;
use Unirgy\DropshipPo\Model\Po\ItemFactory;
use Unirgy\Dropship\Helper\Data as HelperData;
use Unirgy\Dropship\Model\EmailTransportBuilder;
use Unirgy\Dropship\Model\ResourceModel\Helper;
use Unirgy\Dropship\Model\Vendor\Statement as VendorStatement;

class Statement extends VendorStatement
{
    /**
     * @var CommentFactory
     */
    protected $_poCommentFactory;

    /**
     * @var ItemFactory
     */
    protected $_poItemFactory;

    public function __construct(EmailTransportBuilder $transportBuilder, 
        StateInterface $inlineTranslation, 
        StoreManagerInterface $storeManager, 
        ScopeConfigInterface $scopeConfig, 
        Helper $resourceHelper, 
        HelperData $helperData, 
        TaxHelperData $taxHelperData,
        \Unirgy\Dropship\Model\Source $source,
        Context $context, 
        Registry $registry, 
        CommentFactory $poCommentFactory,
        ItemFactory $poItemFactory,
        AbstractResource $resource = null, 
        AbstractDb $resourceCollection = null, 
        array $data = [])
    {
        $this->_poCommentFactory = $poCommentFactory;
        $this->_poItemFactory = $poItemFactory;

        parent::__construct($transportBuilder, $inlineTranslation, $storeManager, $scopeConfig, $resourceHelper, $helperData, $taxHelperData, $source, $context, $registry, $resource, $resourceCollection, $data);
    }

    public function fetchOrders()
    {
        $vendor = $this->getVendor();
        $withholdOptions = $this->_hlp->src()->setPath('statement_withhold_totals')->toOptionHash();
        $withhold = array_flip((array)$vendor->getStatementWithholdTotals());
        $adjTrigger = 'ADJUSTMENT:';

        $res = $this->_hlp->rHlp();
        $pos = $this->_hlp->createObj('Unirgy\DropshipPo\Model\ResourceModel\Po\GridCollection');
        $pos->getSelect()->join(
            ['t'=>$res->getTableName('udropship_po')],
            't.entity_id=main_table.entity_id'/*,
            array('udropship_vendor', 'udropship_available_at', 'udropship_method',
                'udropship_method_description', 'udropship_status', 'shipping_amount'
            )*/
        )
        ->where("udropship_vendor=?", $this->getVendorId())
        ->where("order_created_at>=?", $this->getOrderDateFrom())
        ->where("order_created_at<=?", $this->getOrderDateTo())
        ->order('main_table.entity_id asc');
        
        $adjustments = $this->_poCommentFactory->create()->getCollection()
            ->addAttributeToFilter('parent_id', ['in'=>$pos->getAllIds()])
            ->addAttributeToFilter('comment', ['like'=>$adjTrigger.'%'])
        ;

        $orders = [];
        $totals = [
            'subtotal'=>0, 'tax'=>0, 'shipping'=>0, 'handling'=>0,
            'com_amount'=>0, 'trans_fee'=>0, 'adj_amount'=>0, 'total_payout'=>0,
        ];
        $poItemsToLoad = [];
        foreach ($pos as $id=>$po) {
            foreach (['base_total_value', 'base_tax_amount'] as $k) {
                if (!$po->hasData($k)) {
                    $poItemsToLoad[$id][$k] = true;
                }
            }

            $order = [
                'po_id' => $po->getId(),
                'date' => $po->getOrderCreatedAt(),
                'id' => $po->getOrderIncrementId(),
                'subtotal' => $po->getBaseTotalValue(),
                'shipping' => $po->getShippingAmount(),
                'tax' => $po->getBaseTaxAmount(),
                'handling' => $po->getBaseHandlingFee(),
                'com_percent' => $po->getCommissionPercent(),
                'trans_fee' => $po->getTransactionFee(),
                'adj_amount' => 0,
            ];

            $this->_eventManager->dispatch('udropship_vendor_statement_row', [
                'po'=>$po,
                'order'=>&$order
            ]);

            $orders[$id] = $order;
        }
        if ($poItemsToLoad) {
            $poItems = $this->_poItemFactory->create()->getCollection();
            $poItems->getSelect()
                ->join(['i'=>$res->getTableName('sales_order_item')], 'i.item_id=order_item_id', ['base_row_total', 'base_tax_amount'])
                ->where('order_item_id<>0 and parent_id in (?)', array_keys($poItemsToLoad))
            ;
            $itemTotals = [];
            foreach ($poItems as $item) {
                $id = $item->getParentId();
                if (empty($itemTotals[$id])) {
                    $itemTotals[$id] = ['subtotal'=>0, 'tax'=>0];
                }
                $itemTotals[$id]['subtotal'] += $item->getBaseRowTotal();
                $itemTotals[$id]['tax'] += $item->getBaseTaxAmount();
            }
            foreach ($itemTotals as $id=>$total) {
                foreach ($total as $k=>$v) {
                    $orders[$id][$k] = $v;
                }
            }
        }

        foreach ($orders as &$order) {
            if (is_null($order['com_percent'])) {
                $order['com_percent'] = $vendor->getCommissionPercent();
            }
            $order['com_percent'] *= 1;
            if (is_null($order['trans_fee'])) {
                $order['trans_fee'] = $vendor->getTransactionFee();
            }
            $order['com_amount'] = round($order['subtotal']*$order['com_percent']/100, 2);
            $order['total_payout'] = $order['subtotal']-$order['com_amount']-$order['trans_fee'];
            //+$order['tax']+$order['handling']+$order['shipping'];

            foreach ($withholdOptions as $k=>$l) {
                if (!isset($withhold[$k]) && isset($order[$k])) {
                    $order['total_payout'] += $order[$k];
                }
            }
            foreach (array_keys($totals) as $k) {
                $totals[$k] += $order[$k];
                $order[$k] = $this->_hlp->formatPrice($order[$k], false);
            }
        }
        unset($order);

        $adjTriggerQ = preg_quote($adjTrigger);
        foreach ($adjustments as $adjustment) {
            if (!preg_match("#({$adjTriggerQ})\\s*([0-9.-]+)\\s*(.*)\$#m", $adjustment->getComment(), $match)) {
                continue;
            }
            $adj = [
                'amount' => (float)$match[2],
                'comment' => $match[1].' '.$match[3],
            ];
            $totals['adj_amount'] += $adj['amount'];
            $totals['total_payout'] += $adj['amount'];

            $adj['amount'] = $this->_hlp->formatPrice($adj['amount'], false);
            $orders[$adjustment->getParentId()]['adjustments'][] = $adj;
        }

        $this->setTotalOrders(sizeof($orders));
        $this->setTotalPayout($totals['total_payout']);

        foreach ($totals as &$total) {
            $total = $this->_hlp->formatPrice($total, false);
        }
        unset($total);

        $this->setOrdersData($this->_hlp->jsonEncode(compact('orders', 'totals')));
        return $this;
    }
}

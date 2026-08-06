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

namespace Unirgy\DropshipPo\Model\ResourceModel;

class Po extends \Magento\Sales\Model\ResourceModel\EntityAbstract
{
    protected $_eventPrefix = 'udpo_po_resource';
    protected $_grid = true;
    protected $_useIncrementId = true;
    protected $_entityTypeForIncrementId = 'udpo_po';

    protected function _construct()
    {
        $this->_init('udropship_po', 'entity_id');
    }

    public function hasExternalInvoice($po, $oItemIds)
    {
        return $this->getConnection()->fetchOne(
            $this->getConnection()->select()
                ->from(['sii' => $this->getTable('sales_invoice_item')], [])
                ->join(['si' => $this->getTable('sales_invoice')], 'sii.parent_id=si.entity_id', [])
                ->where('sii.order_item_id in (?)', $oItemIds)
                ->where('si.udpo_id!=?', $po->getId())
                ->columns('count(*)')
        );        
    }
}

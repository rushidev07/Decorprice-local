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
 
namespace Unirgy\DropshipPo\Block\Adminhtml\Po\View\Tab;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Context;
use Magento\Sales\Block\Adminhtml\Order\View\Tab\Invoices as TabInvoices;

class Invoices extends TabInvoices
{
    /**
     * @var Registry
     */
    protected $_frameworkRegistry;

    public function __construct(Context $context, 
        Registry $frameworkRegistry, 
        array $data = [])
    {
        $this->_frameworkRegistry = $frameworkRegistry;

        parent::__construct($context, $data);
    }

    public function setCollection($collection)
    {
        $collection->addAttributeToFilter('udpo_id', $this->getPo()->getId());
        return parent::setCollection($collection);
    }
    public function getPo()
    {
        return $this->_frameworkRegistry->registry('current_udpo');
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('sales/order_invoice/view',
            [
                'invoice_id'=> $row->getId(),
                'order_id'  => $row->getOrderId()
            ]
        );
    }

}
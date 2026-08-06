<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mageplaza\RMA\Block\Adminhtml\Request\Edit\Form\Renderer;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

/**
 * Renderer for Qty field in sales create new order search grid
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Currency extends Extended
{

    protected $_orderColFact;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param Data $backendHelper
     * @param CollectionFactory $_orderColFact
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $orderColFact,
        array $data = []
    )
    {
        $this->_orderColFact = $orderColFact;
        parent::__construct($context,$backendHelper,$data);
    }


    /**
     * Render product qty field
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $getReservationsQuantity = $this->_orderColFact->create()->load($row->getData('entity_id'));
        $currency = $getReservationsQuantity->getOrderCurrencyCode();
        return "VND";
    }
}

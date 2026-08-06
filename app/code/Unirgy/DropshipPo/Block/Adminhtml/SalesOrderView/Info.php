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
 
namespace Unirgy\DropshipPo\Block\Adminhtml\SalesOrderView;

use Magento\Sales\Block\Adminhtml\Order\View\Info as ViewInfo;

class Info extends ViewInfo
{
    public function getCustomerViewUrl()
    {
        if ($this->getOrder()->getCustomerIsGuest()) {
            return false;
        }
        return $this->getUrl('customer/index/edit', ['id' => $this->getOrder()->getCustomerId()]);
    }

    public function getViewUrl($orderId)
    {
        return $this->getUrl('sales/order/view', ['order_id'=>$orderId]);
    }
}
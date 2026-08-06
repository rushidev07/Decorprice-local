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

use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;

class Info
    extends AbstractOrder
    implements TabInterface
{

    public function getPo()
    {
        return $this->_coreRegistry->registry('current_udpo');
    }
    
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    public function getSource()
    {
        return $this->getPo();
    }
    
    /**
     * ######################## TAB settings #################################
     */
    public function getTabLabel()
    {
        return __('Information');
    }

    public function getTabTitle()
    {
        return __('Order Information');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
}
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
 
namespace Unirgy\DropshipPo\Block\Adminhtml\Po\Create;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Block\Adminhtml\Items\AbstractItems;

class Items extends AbstractItems
{
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }
    
    protected function _beforeToHtml()
    {
        $onclick = "submitAndReloadArea($('po_items_container'),'".$this->getUpdateUrl()."')";
        $this->setChild(
            'update_button',
            $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData([
                'class'     => 'update-button',
                'label'     => __('Update Qty\'s'),
                'onclick'   => $onclick,
            ])
        );
        $this->setChild(
            'submit_button',
            $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData([
                'label'     => __('Create Purchase Orders'),
                'class'     => 'save submit-button',
                'onclick'   => 'disableElements(\'submit-button\');$(\'edit_form\').submit()',
            ])
        );

        return parent::_beforeToHtml();
    }
    
    public function getUpdateButtonHtml()
    {
        return $this->getChildHtml('update_button');
    }
    
    public function getUpdateUrl()
    {
        return $this->getUrl('*/*/updateQty', ['order_id'=>$this->getOrder()->getId()]);
    }
    
    public function getCommentText()
    {
        return ObjectManager::getInstance()->get('Magento\Backend\Model\Session')->getCommentText(true);
    }
}
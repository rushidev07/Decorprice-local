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
 
namespace Unirgy\DropshipPo\Block\Adminhtml\Po;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;

class View extends Container
{

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    public function __construct(
        Registry $frameworkRegistry,
        Context $context,
        array $data = [])
    {
        $this->_coreRegistry = $frameworkRegistry;

        parent::__construct($context, $data);

    }

    protected function _construct()
    {
        $this->_blockGroup  = 'Unirgy_DropshipPo';
        $this->_objectId    = 'udpo_id';
        $this->_controller  = 'adminhtml_po';
        $this->_mode        = 'view';

        parent::_construct();

        $this->removeButton('reset');
        $this->removeButton('save');

        if (!$this->_scopeConfig->isSetFlag('udropship/purchase_order/allow_delete_po', ScopeInterface::SCOPE_STORE)) {
            $this->removeButton('delete');
        }

        if ($this->getPo()->getId()) {

            if ($this->_authorization->isAllowed('Unirgy_DropshipPo::action_edit_cost')) {
                $this->addButton('po_editcosts', [
                    'label'     => __('Edit Costs'),
                    'onclick'   => 'setLocation(\'' . $this->getEditCostsUrl() . '\')',
                    'class'     => 'go'
                ]);
            }

            if ($this->_authorization->isAllowed('Magento_Sales::ship') && $this->getPo()->canCreateShipment()) {
                $this->addButton('po_create_shipment', [
                    'label'     => __('Create Shipment'),
                    'onclick'   => 'setLocation(\'' . $this->getCreateShipmentUrl() . '\')',
                    'class'     => 'go'
                ]);
            }

            $this->addButton('print', [
                    'label'     => __('Print'),
                    'class'     => 'save',
                    'onclick'   => 'setLocation(\''.$this->getPrintUrl().'\')'
                ]
            );
        }
    }

    public function getEditCostsUrl()
    {
        return $this->getUrl('*/*/editCosts', ['udpo_id'=>$this->getPo()->getId()]);
    }
    
	public function getCreateShipmentUrl()
    {
        return $this->getUrl('*/*/newShipment', ['udpo_id'=>$this->getPo()->getId()]);
    }

    public function getPo()
    {
        return $this->_coreRegistry->registry('current_udpo');
    }

    public function getHeaderText()
    {
        return __('Purchase Order #%1$s | %2$s', $this->getPo()->getIncrementId(), $this->formatDate($this->getPo()->getCreatedAt(), 'medium', true));
    }

    public function getBackUrl()
    {
        return $this->getUrl(
            'sales/order/view',
            [
                'order_id'  => $this->getPo()->getOrderId(),
                'active_tab'=> 'order_udpos'
            ]);
    }

    public function getPrintUrl()
    {
        return $this->getUrl('udpo/po/printPo', [
            'udpo_id' => $this->getPo()->getId()
        ]);
    }

    public function updateBackButtonUrl($flag)
    {
        if ($flag) {
            return $this->updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('udpo/po') . '\')');
        }
        return $this;
    }
}
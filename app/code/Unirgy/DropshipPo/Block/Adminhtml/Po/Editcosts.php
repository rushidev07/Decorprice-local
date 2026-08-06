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
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\DropshipPo\Block\Adminhtml\Po;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;

class Editcosts extends Container
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    public function __construct(Context $context, 
        Registry $frameworkRegistry, 
        array $data = [])
    {
        $this->_coreRegistry = $frameworkRegistry;

        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_blockGroup = 'Unirgy_DropshipPo';
        $this->_objectId = 'order_id';
        $this->_controller = 'adminhtml_po';
        $this->_mode = 'editcosts';

        parent::_construct();

        $this->removeButton('save');
    }

    public function getPo()
    {
        return $this->_coreRegistry->registry('current_udpo');
    }

    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    public function getHeaderText()
    {
        $header = __('Edit Costs for Purchase Orders #%1', $this->getPo()->getIncrementId());
        return $header;
    }

    public function getBackUrl()
    {
        return $this->getUrl('udpo/order_po/view', ['udpo_id'=>$this->getPo()->getId()]);
    }
}
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
 * @package    \Unirgy\Dropship
 * @copyright  Copyright (c) 2015-2016 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\Dropship\Block\Adminhtml\Vendor;

use \Magento\Backend\Block\Widget\Context;
use \Magento\Backend\Block\Widget\Form\Container;
use \Magento\Directory\Helper\Data as DirectoryHelperData;
use \Magento\Framework\Registry;
use \Unirgy\Dropship\Helper\Data as HelperData;

class Edit extends Container
{
    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var HelperData
     */
    protected $_hlp;

    /**
     * @var DirectoryHelperData
     */
    protected $_directoryHelperData;

    protected $_template = 'Unirgy_Dropship::widget/vendor/form/container.phtml';

    public function __construct(
        Registry $registry,
        HelperData $helperData,
        DirectoryHelperData $directoryHelperData,
        Context $context,
        array $data = []
    ) {
    
        $this->_registry = $registry;
        $this->_hlp = $helperData;
        $this->_directoryHelperData = $directoryHelperData;

        parent::__construct($context, $data);

        $this->setData('form_action_url', $this->getUrl('*/*/save', ['id' => $this->getRequest()->getParam('id')]));
    }

    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Unirgy_Dropship';
        $this->_controller = 'adminhtml_vendor';

        parent::_construct();

        $this->updateButton('save', 'label', __('Save Vendor'));
        $this->updateButton('save_continue', 'label', __('Save and Continue Edit'));
        $this->addButton('save_continue_btn', [
            'label'     => __('Save and Continue Edit'),
            'class'     => 'save',
        ], 10);
        $this->updateButton('delete', 'label', __('Delete Vendor'));

        if ($this->getRequest()->getParam($this->_objectId)) {
            $vendor = $this->_hlp->createObj('\Unirgy\Dropship\Model\Vendor')
                ->load($this->getRequest()->getParam($this->_objectId));
            $this->_registry->register('vendor_data', $vendor);
        } elseif (($sessVD = $this->_hlp->getObj('Magento\Backend\Model\Session')->getData('uvendor_edit_data', true))) {
            unset($sessVD['logo']);
            if ($this->_registry->registry('vendor_data')) {
                $this->_registry->registry('vendor_data')->setData($sessVD);
            } else {
                $this->_registry->register('vendor_data', $this->_hlp->createObj('\Unirgy\Dropship\Model\Vendor')->setData($sessVD));
            }
        }
    }

    public function getHeaderText()
    {
        if ($this->_registry->registry('vendor_data') && $this->_registry->registry('vendor_data')->getId()) {
            return __("Edit Vendor '%1'", $this->escapeHtml($this->_registry->registry('vendor_data')->getVendorName()));
        } else {
            return __('New Vendor');
        }
    }

    public function getRegionJson()
    {
        return $this->_directoryHelperData->getRegionJson();
    }
}

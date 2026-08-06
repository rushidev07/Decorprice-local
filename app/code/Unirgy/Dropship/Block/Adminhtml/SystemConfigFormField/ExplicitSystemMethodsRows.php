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
 * @package    \Unirgy\DropshipPo
 * @copyright  Copyright (c) 2015-2016 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\Dropship\Block\Adminhtml\SystemConfigFormField;

use \Magento\Config\Block\System\Config\Form\Field;
use \Magento\Framework\Data\Form\Element\AbstractElement;

class ExplicitSystemMethodsRows extends Field
{
    /**
     * @var \Unirgy\Dropship\Helper\Data
     */
    protected $_hlp;

    public function __construct(
        \Unirgy\Dropship\Helper\Data $udropshipHelper,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
    
        $this->_hlp = $udropshipHelper;
        parent::__construct($context, $data);
        if (!$this->getTemplate()) {
            $this->setTemplate('Unirgy_Dropship::system/form_field/system_methods_rows.phtml');
        }
    }
    public function getStore()
    {
        return $this->_hlp->getDefaultStoreView();
    }

    public function getElementHtml(AbstractElement $element)
    {
        return $this->_getElementHtml($element);
    }
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->setElement($element);
        $html = $this->_toHtml();
        return $html;
    }

    public function setFieldName($fName)
    {
        $this->resetIdSuffix();
        return $this->setData('field_name', $fName);
    }

    public function getFieldName()
    {
        return $this->getData('field_name')
            ? $this->getData('field_name')
            : ($this->getElement() ? $this->getElement()->getName() : '');
    }

    protected $_idSuffix;
    public function resetIdSuffix()
    {
        $this->_idSuffix = null;
        return $this;
    }
    public function getIdSuffix()
    {
        if (null === $this->_idSuffix) {
            $this->_idSuffix = $this->prepareIdSuffix($this->getFieldName());
        }
        return $this->_idSuffix;
    }

    public function prepareIdSuffix($id)
    {
        return preg_replace('/[^a-zA-Z0-9\$]/', '_', $id);
    }

    public function suffixId($id)
    {
        return $id.$this->getIdSuffix();
    }

    public function getAddButtonId()
    {
        return $this->suffixId('addBtn');
    }
    /**
     * @return \Magento\Shipping\Model\Config
     */
    protected function _shippingConfig()
    {
        return $this->_hlp->getObj('Magento\Shipping\Model\Config');
    }

    protected $_carriers;
    public function getCarriers()
    {
        if (null === $this->_carriers) {
            $carriers = $this->_shippingConfig()->getAllCarriers();
            foreach ($carriers as $carrierCode => $carrierModel) {
                if (in_array($carrierCode, ['udsplit', 'udropship','googlecheckout'])) {
                    continue;
                }
                $this->_carriers[$carrierCode] = $this->_hlp->getScopeConfig('carriers/'.$carrierCode.'/title');
            }
        }
        return $this->_carriers;
    }
    protected $_carriersJson;
    public function getCarriersJson()
    {
        if (null === $this->_carriersJson) {
            $carriers = $this->_shippingConfig()->getAllCarriers();
            foreach ($carriers as $carrierCode => $carrierModel) {
                if (in_array($carrierCode, ['udsplit', 'udropship','googlecheckout'])) {
                    continue;
                }
                $params = [];
                if ($carrierCode=='ups') {
                    $params['values'] = array_merge_recursive(
                        [['value'=>'', 'label'=>__('* Please Select')]],
                        [['value'=>'*', 'label'=>__('* Any available')]],
                        $this->_hlp->src()->setPath('ups_shipping_method_combined')->toOptionArray()
                    );
                } else {
                    if (method_exists($carrierModel, 'getCode')
                        && !in_array($carrierCode, ['tablerate','dhl'])
                    ) {
                        $carrierMethods = $carrierModel->getCode('method');
                    } else {
                        try {
                            $carrierMethods = $carrierModel->getAllowedMethods();
                        } catch (\Exception $e) {
                            continue;
                        }
                    }
                    if (!$carrierMethods || !is_array($carrierMethods)) {
                        $array = [];
                        $arrays = [
                            [''=>(string)__('* Not used')],
                            ['*'=>(string)__('* Any available')],
                        ];
                        foreach ($arrays as $array_i) {
                            if (is_array($array_i)) {
                                $this->_hlp->array_merge_2($array, $array_i);
                            }
                        }
                        $params['options'] = $array;
                    } else {
                        foreach ($carrierMethods as $cmCode => $cmTitle) {
                            $carrierMethods[$cmCode] = (string)$cmTitle;
                        }
                        $array = [];
                        $arrays = [
                            [''=>(string)__('* Not used')],
                            ['*'=>(string)__('* Any available')],
                            $carrierMethods
                        ];
                        foreach ($arrays as $array_i) {
                            if (is_array($array_i)) {
                                $this->_hlp->array_merge_2($array, $array_i);
                            }
                        }
                        $params['options'] = $array;
                    }
                }
                $this->_carriersJson[$carrierCode] = $params;
            }
            $this->_carriersJson = $this->_hlp->serialize($this->_carriersJson);
        }
        return $this->_carriersJson;
    }
}

<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsGroup
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Houzz\Block\Adminhtml\Profile\Edit\Tab\Attribute;

/**
 * Rolesedit Tab Display Block.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
//class Requiredattribute extends \Magento\Backend\Block\Template

class Requiredattribute extends \Magento\Backend\Block\Widget implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{

    /**
     * @var string
     */
    protected $_template = 'Ced_Houzz::profile/attribute/required_attribute.phtml';


    protected  $_objectManager;

    protected  $_coreRegistry;

    protected  $_profile;

    protected  $_houzzAttribute;


    public function __construct(\Magento\Backend\Block\Template\Context $context,
                                \Magento\Framework\ObjectManagerInterface $objectManager,
                                \Magento\Framework\Registry $registry,
                                array $data = []

    )
    {
        $this->_objectManager = $objectManager;
        $this->_coreRegistry = $registry;

        $this->_profile = $this->_coreRegistry->registry('current_profile');

        parent::__construct($context, $data);
    }



    /**
     * Prepare global layout
     * Add "Add tier" button to layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            ['label' => __('Add Attribute'), 'onclick' => 'return requiredAttributeControl.addItem()', 'class' => 'add']
        );
        $button->setName('add_required_item_button');

        $this->setChild('add_button', $button);
        return parent::_prepareLayout();
    }

    /**
     * Retrieve 'add group price item' button HTML
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }



    /**
     * Retrieve houzz attributes
     *
     * @param int|null $groupId  return name by customer group id
     * @return array|string
     */
    public function getHouzzAttributes()
    {
        //$parentCategory = "Vehicle";
        //$childCategory = "LandVehicles";

        /*if($this->_profile && $this->_profile->getId()>0){
            $parentCategory = $this->_profile->getData('profile_category_level_1');
            $childCategory =  $this->_profile->getData('profile_category_level_2');
        }else{
            $parentCategory = $this->getPId();
            $childCategory =  $this->getCId();
        }

      //  echo $parentCategory ." - ". $childCategory;die ("nooooo");

        $requiredAttribute = array();
        $attribute = $this->_objectManager->create('Ced\Houzz\Model\Categories')->getCollection()
            ->addFieldToFilter('cat_id', $parentCategory)->getFirstItem();
          //  ->addFieldToFilter('cat_id', $childCategory)->getFirstItem();
          //->addFieldToFilter('path', $childCategory)->getFirstItem();


        if($attribute && $attribute->getId()){
            $model = $this->_objectManager->create('Ced\Houzz\Model\Attributes');
            $houzzRequiredAttributes = explode(",", $attribute->getData('houzz_required_attributes'));
            foreach ($houzzRequiredAttributes as $item) {
                //$requiredAttribute[$item] = $item;
                $magentoAttr = $model->loadByField('houzz_attribute_name', $item);
                $temp = array();
                $temp['houzz_attribute_name'] = $item;
                $temp['magento_attribute_code'] = $magentoAttr->getMagentoAttributeCode();
                $temp['houzz_attribute_type'] = $magentoAttr->getHouzzAttributeType();
                $temp['houzz_attribute_enum'] = $magentoAttr->getHouzzAttributeEnum();
                $temp['required'] = true;
                $requiredAttribute[$item] = $temp;
            }
        }


        $attributeCollections = $model =  $this->_objectManager->create('Ced\Houzz\Model\Attributes')->getCollection();
        $optionalAttribues = array();
        foreach ($attributeCollections as $item) {
            if(!isset($requiredAttribute[$item->getHouzzAttributeName()]))
                $optionalAttribues[$item->getHouzzAttributeName()] = ['houzz_attribute_name' => $item->getHouzzAttributeName(),
                                                                        'houzz_attribute_type' => $item->getHouzzAttributeType(),
                                                                        'houzz_attribute_enum' => $item->getHouzzAttributeEnum(),];
        }*/
        $requiredAttribute = array();
        $houzzRequiredAttributes = [
            [
                'value' => ['houzz_attribute_name' => 'title', 'magento_attribute_code' => 'name'],
                'label' => __('Title')
            ],
            [
                'value' => ['houzz_attribute_name' => 'description', 'magento_attribute_code' => 'description'],
                'label' => __('Description')
            ],
            [
                'value' => ['houzz_attribute_name' => 'sku', 'magento_attribute_code' => 'sku'],
                'label' => __('SKU')
            ],
            [
                'value' => ['houzz_attribute_name' => 'upc', 'magento_attribute_code' => 'houzz_upc'],
                'label' => __('UPC')
            ],
            [
                'value' => ['houzz_attribute_name' => 'price', 'magento_attribute_code' => 'price'],
                'label' => __('Price')
            ],
            /*[
                'value' => ['houzz_attribute_name' => 'category_id', 'magento_attribute_code' => 'houzz_category_id'],
                'label' => __('Category Id')
            ],*/
            [
                'value' => ['houzz_attribute_name' => 'currency', 'magento_attribute_code' => 'houzz_currency', 'type' => 'select', 'enum_values' => array('USD')],
                'label' => __('Currency')
            ],
            [
                'value' => ['houzz_attribute_name' => 'manufacturer', 'magento_attribute_code' => 'houzz_manufacturer'],
                'label' => __('Manufacturer')
            ],
            /*[
                'value' => ['houzz_attribute_name' => 'style', 'magento_attribute_code' => 'houzz_style'],
                'label' => __('Style')
            ],*/
            [
                'value' => ['houzz_attribute_name' => 'msrp', 'magento_attribute_code' => 'houzz_msrp'],
                'label' => __('MSRP')
            ],
            [
                'value' => ['houzz_attribute_name' => 'quantity', 'magento_attribute_code' => 'houzz_quantity'],
                'label' => __('Quantity')
            ],
            [
                'value' => ['houzz_attribute_name' => 'width', 'magento_attribute_code' => 'houzz_width'],
                'label' => __('Currency')
            ],
            [
                'value' => ['houzz_attribute_name' => 'height', 'magento_attribute_code' => 'houzz_height'],
                'label' => __('Manufacturer')
            ],
            [
                'value' => ['houzz_attribute_name' => 'depth', 'magento_attribute_code' => 'houzz_depth'],
                'label' => __('Depth')
            ],
            [
                'value' => ['houzz_attribute_name' => 'weight', 'magento_attribute_code' => 'houzz_weight'],
                'label' => __('MSRP')
            ],
            [
                'value' => ['houzz_attribute_name' => 'dimensions_unit', 'magento_attribute_code' => 'houzz_dimensions_unit', 'type' => 'select', 'enum_values' => array('IN', 'FT', 'MM', 'CM', 'M')],
                'label' => __('Dimenstion Unit')
            ],
            [
                'value' => ['houzz_attribute_name' => 'weight_unit', 'magento_attribute_code' => 'houzz_weight_unit', 'type' => 'select', 'enum_values' => array('OZ', 'LB', 'GR', 'KG')],
                'label' => __('Weight Unit')
            ],
            /*[
                'value' => ['houzz_attribute_name' => 'status', 'magento_attribute_code' => 'status'],
                'label' => __('Status')
            ],*/
            [
                'value' => ['houzz_attribute_name' => 'assembly_required', 'magento_attribute_code' => 'houzz_assembly_required', 'type' => 'select', 'enum_values' => array('Yes', 'No')],
                'label' => __('Assembly Required')
            ],
            [
                'value' => ['houzz_attribute_name' => 'made_to_order', 'magento_attribute_code' => 'houzz_made_to_order', 'type' => 'select', 'enum_values' => array('Yes', 'No')],
                'label' => __('Made To Order')
            ],
            [
                'value' => ['houzz_attribute_name' => 'package_dimensions_unit', 'magento_attribute_code' => 'houzz_package_dimensions_unit', 'type' => 'select', 'enum_values' => array('IN', 'FT', 'MM', 'CM', 'M')],
                'label' => __('Shipping Dimensions Unit')
            ],
            [
                'value' => ['houzz_attribute_name' => 'package_weight_unit', 'magento_attribute_code' => 'houzz_package_weight_unit', 'type' => 'select', 'enum_values' => array('OZ', 'LB', 'GR', 'KG')],
                'label' => __('Shipping Weight Unit')
            ],
            [
                'value' => ['houzz_attribute_name' => 'package_width', 'magento_attribute_code' => 'houzz_package_width'],
                'label' => __('Shipping Width')
            ],
            [
                'value' => ['houzz_attribute_name' => 'package_height', 'magento_attribute_code' => 'houzz_package_height'],
                'label' => __('Shipping Height')
            ],
            [
                'value' => ['houzz_attribute_name' => 'package_weight', 'magento_attribute_code' => 'houzz_package_weight'],
                'label' => __('Shipping Weight')
            ],
            [
                'value' => ['houzz_attribute_name' => 'package_depth', 'magento_attribute_code' => 'houzz_package_depth'],
                'label' => __('Shipping Depth')
            ],
            [
                'value' => ['houzz_attribute_name' => 'shipping_country', 'magento_attribute_code' => 'houzz_shipping_country', 'type' => 'select', 'enum_values' => array('US', 'US_OTHER', 'CA')],
                'label' => __('Shipping Country')
            ],
            [
                'value' => ['houzz_attribute_name' => 'shipping_type', 'magento_attribute_code' => 'houzz_shipping_type', 'type' => 'select', 'enum_values' => array('Standard', 'Expedited')],
                'label' => __('Shipping Type')
            ],
            [
                'value' => ['houzz_attribute_name' => 'shipping_price', 'magento_attribute_code' => 'houzz_shipping_price'],
                'label' => __('Shipping Price')
            ],
            [
                'value' => ['houzz_attribute_name' => 'lead_time_min', 'magento_attribute_code' => 'houzz_lead_time_min'],
                'label' => __('Shipping LeadTimeMin')
            ],
            [
                'value' => ['houzz_attribute_name' => 'lead_time_max', 'magento_attribute_code' => 'houzz_lead_time_max'],
                'label' => __('Shipping LeadTimeMax')
            ],
            /*[
                'value' => ['houzz_attribute_name' => 'imagelink', 'magento_attribute_code' => 'image'],
                'label' => __('ImageLink')
            ],*/
            [
                'value' => ['houzz_attribute_name' => 'MinimumOrderQuantity', 'magento_attribute_code' => 'houzz_minimum_order_quantity'],
                'label' => __('MinimumOrderQuantity')
            ],
            /*[
                'value' => ['houzz_attribute_name' => 'AssemblyRequired', 'magento_attribute_code' => 'houzz_assembly_required'],
                'label' => __('AssemblyRequired')
            ],*/
            [
                'value' => ['houzz_attribute_name' => 'FreightItem', 'magento_attribute_code' => 'houzz_freight_item', 'type' => 'select', 'enum_values' => array('No', 'Yes')],
                'label' => __('FreightItem')
            ],
            [
                'value' => ['houzz_attribute_name' => 'Prop65Disclosure', 'magento_attribute_code' => '', 'type' => 'select', 'enum_values' => array('No', 'Yes')],
                'label' => __('Prop65Disclosure')
            ],
        ];
        foreach ($houzzRequiredAttributes as $item) {
            //$requiredAttribute[$item] = $item;
            //$magentoAttr = $model->loadByField('houzz_attribute_name', $item);
            $temp = array();

            $temp['houzz_attribute_name'] = $item['value']['houzz_attribute_name'];
            $temp['magento_attribute_code'] = $item['value']['magento_attribute_code'];
            $temp['houzz_attribute_type'] = isset($item['value']['type']) ? $item['value']['type'] : 'text';
            $temp['houzz_attribute_enum'] = isset($item['value']['enum_values']) ? implode(',', $item['value']['enum_values']) : '';
            $temp['required'] = true;
            $requiredAttribute[$item['value']['houzz_attribute_name']] = $temp;
        }
        $this->_houzzAttribute[] = array(
            'label' => __('Required Attributes'),
            'value' => $requiredAttribute
        );

        $optionalAttribues = array();
        $houzzOptionalAttributes = [
            [
                'value' => ['houzz_attribute_name' => 'Keywords', 'magento_attribute_code' => '', 'type' => 'text'],
                'label' => __('Keywords')
            ],
            [
                'value' => ['houzz_attribute_name' => 'Prop65WarningType', 'magento_attribute_code' => '', 'type' => 'select', 'enum_values' => array('ShortForm', 'LongForm')],
                'label' => __('Prop65WarningType')
            ],
        ];
        foreach ($houzzOptionalAttributes as $item) {
            $temp = array();

            $temp['houzz_attribute_name'] = $item['value']['houzz_attribute_name'];
            $temp['magento_attribute_code'] = $item['value']['magento_attribute_code'];
            $temp['houzz_attribute_type'] = isset($item['value']['type']) ? $item['value']['type'] : 'text';
            $temp['houzz_attribute_enum'] = isset($item['value']['enum_values']) ? implode(',', $item['value']['enum_values']) : '';
            $temp['required'] = true;
            $optionalAttribues[$item['value']['houzz_attribute_name']] = $temp;
        }


        $this->_houzzAttribute[] = array(
            'label' => __('Optional Attributes'),
            'value' => $optionalAttribues
        );


        return $this->_houzzAttribute;
    }


    /**
     * Retrieve magento attributes
     *
     * @param int|null $groupId  return name by customer group id
     * @return array|string
     */
    public function getMagentoAttributes()
    {


        $attributes = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection')
            ->getItems();

        $mattributecode = '--please select--';

        $magentoattributeCodeArray[''] = $mattributecode;
        $magentoattributeCodeArray['default'] = "--Set Default Value--";

        foreach ($attributes as $attribute){
            $magentoattributeCodeArray[$attribute->getAttributecode()] = $attribute->getAttributecode();
        }

        return $magentoattributeCodeArray;
    }


    public function getHouzzAttributeValuesMapping(){

        //return $this->_houzzAttribute[0]['value'];
        $data = array();
        if($this->_profile && $this->_profile->getId()>0){
            $data = json_decode($this->_profile->getProfileAttributeMapping(), true);

            if(isset($data['required_attributes']) && isset($data['required_attributes']))
                $data = array_merge($data['required_attributes'], $data['optional_attributes']);
        }else{
            if(!$this->_houzzAttribute)
                $this->_houzzAttribute = $this->getHouzzAttributes();

            $model = $this->_objectManager->create('Ced\Houzz\Model\Attributes');
            if(count($this->_houzzAttribute[0]['value'])>0){
                $data = $this->_houzzAttribute[0]['value'];

                /*foreach($this->_houzzAttribute[0]['value'] as $houzzAttr){
                    $magentoAttr = $model->loadByField('houzz_attribute_name', $houzzAttr)->getMagentoAttributeCode();
                    $temp = array();
                    $temp['houzz_attribute_name'] = $houzzAttr;
                    $temp['magento_attribute_code'] = $magentoAttr;
                    $temp['required'] = true;
                    $data[] = $temp;
                }*/
            }
        }
        return $data;
    }


    /**
     * Render form element as HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }
}

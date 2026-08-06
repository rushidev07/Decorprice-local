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
//class Configattribute extends \Magento\Backend\Block\Template
class Configattribute extends \Magento\Backend\Block\Widget implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface

{
    /**
     * @var string
     */
    protected $_template = 'Ced_Houzz::profile/attribute/config_attribute.phtml';


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
            ['label' => __('Add Attribute'), 'onclick' => 'return configAttributeControl.addItem()', 'class' => 'add']
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
    public function getHouzzConfigAttributes()
    {
        $houzzconfigAttributes = array(
            array(
                'value' => array('houzz_attribute_name' => 'Color', 'magento_attribute_code' => 'houzz_color'),
                'label' => __('Color')
            ),
            array(
                'value' => array('houzz_attribute_name' => 'Size', 'magento_attribute_code' => 'houzz_size'),
                'label' => __('Size')
            ),
            array(
                'value' => array('houzz_attribute_name' => 'Design', 'magento_attribute_code' => 'houzz_design'),
                'label' => __('Design')
            ),
            array(
                'value' => array('houzz_attribute_name' => 'Configuration', 'magento_attribute_code' => 'houzz_configuration'),
                'label' => __('Configuration')
            )
        );

        $configAttribute = array();
        foreach ($houzzconfigAttributes as $item) {
            $temp = array();
            $temp['houzz_attribute_name'] = $item['value']['houzz_attribute_name'];
            $temp['magento_attribute_code'] = $item['value']['magento_attribute_code'];
            $temp['houzz_attribute_type'] = 'text';
            $configAttribute[$item['value']['houzz_attribute_name']] = $temp;
        }
        $this->_houzzAttribute = $configAttribute;
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
            ->addFieldToFilter('frontend_input', ['in' => ['select', 'multiselect']])
            ->getItems();
        $magentoattributeCodeArray = array();
        foreach ($attributes as $attribute) {
            $magentoattributeCodeArray[$attribute->getAttributecode()] = $attribute->getAttributecode();
        }
        return $magentoattributeCodeArray;
    }


    public function getHouzzAttributeValuesMapping(){
        $data = array();
        if($this->_profile && $this->_profile->getId()>0){
            $configdata = json_decode($this->_profile->getProfileAttributeMapping(), true);
            if(isset($configdata['variant_attributes']))
                $data = $configdata['variant_attributes'];
        }else{
            if(!$this->_houzzAttribute)
                $this->_houzzAttribute = $this->getHouzzConfigAttributes();

            //$collection = $this->_objectManager->create('Ced\Houzz\Model\Confattributes')->getCollection()->addFieldToFilter('magento_attribute_code', array('neq' => 'NULL' ));
            foreach($this->_houzzAttribute as $key => $value){
                if(isset($value['magento_attribute_code']) && $value['magento_attribute_code']!=""){
                    $data[] = $value;

                }
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

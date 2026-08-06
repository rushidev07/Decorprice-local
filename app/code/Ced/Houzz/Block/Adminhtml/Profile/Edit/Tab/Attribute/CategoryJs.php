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
class CategoryJs extends \Magento\Backend\Block\Widget implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    public $_systemStore;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    public $_wysiwygConfig;

    public $_template = 'profile/category_js.phtml';

    public  $_profile;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Helper\Catalog $helperCatalog
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $collectionFactory,
        \Magento\Catalog\Helper\Catalog $helperCatalog,
        \Magento\Framework\Registry $registry,
        $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_coreRegistry = $registry;
        $this->_helperCatalog = $helperCatalog;
        $this->_profile = $this->_coreRegistry->registry('current_profile');

        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function getCurrentCategory()
    {
        if($this->_profile && $this->_profile->getId()){
            $this->_profile->getData('profile_category_level_2');
        }
        return false;
    }

    /**
     * @return bool
     */
    public function getRootCategory()
    {
        if($this->_profile && $this->_profile->getId()){
            $this->_profile->getData('profile_category_level_1');
        }
        return false;
    }

    /**
     * Get current level of houzz category
     * @param integer $level
     * @return string
     */
    public function getLevel($level)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collection = $objectManager->create('Ced\Houzz\Model\Categories')
            ->getCollection()
            ->addFieldToSelect(['id', 'cat_id', 'parent_cat_id', 'name', 'path'])
            ->addFieldToFilter('level', $level);
        return $collection->getData();
    }

    /**
     * Get current mapped houzz category
     * @param integer $level
     * @return string
     */
    public function getSavedCategoryData($level)
    {
        $magentoCatId = $this->getCurrentCategory();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $obj = $objectManager->create('Ced\Houzz\Model\Categories')
            ->getCollection()
            ->addFieldToFilter(['magento_cat_id'], [[ 'like' => "%,".$magentoCatId.",%" ]])
            ->getFirstItem();
        if ($level == '0') {
            return $obj->getData('parent_cat_id');
        } else {
            return $obj->getData('path');
        }
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
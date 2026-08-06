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

namespace Ced\Houzz\Block\Adminhtml\Profile\Edit\Tab;

/**
 * Rolesedit Tab Display Block.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Categorymapping extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Ced_Houzz::profile/categorymapping.phtml';

    /**
     * @param string $field
     * @return string
     */

    public function getHouzzCategoryId($field='csv_parent_id'){
        $category_id=$this->getRequest()->getParam('id');

        $value = $this->_objectManager->create('Ced\Houzz\Model\Categories')->getCollection()->addFieldToFilter('magento_cat_id',$category_id)->getFirstItem();

        $houzz_mapped_id=$value->getData($field);
        $houzz_mapped_id=($houzz_mapped_id === 0?'':$houzz_mapped_id);
        return $houzz_mapped_id;
    }

    public function getFilteredHouzzCollection($level){
        return $this->_objectManager->create('Ced\Houzz\Model\Categories')->getCollection()->addFieldToFilter('level' , $level);
    }
}

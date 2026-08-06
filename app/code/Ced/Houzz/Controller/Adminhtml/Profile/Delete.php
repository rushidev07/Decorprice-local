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
 * @package     Ced_Houzz
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */


namespace Ced\Houzz\Controller\Adminhtml\Profile;
 
class Delete extends \Magento\Customer\Controller\Adminhtml\Group
{
	protected $_objectManager;
	
	protected $_session;

	/**
	 * Delete the Attribute
	 */
	public function execute()
    {
        $code = $this->getRequest()->getParam('pcode');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($code) {
            $model = $this->_objectManager->create('Ced\Houzz\Model\Profile')->getCollection()->addFieldToFilter('profile_code', $code);
            // entity type check
            try {
              foreach ($model as $value) {
                if($code == $value->getData('profile_code')){
                    $value->delete();
                }
              }
                $this->messageManager->addSuccess(__('You deleted the profile.'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath(
                    'houzz/profile/edit',
                    ['pcode' => $this->getRequest()->getParam('pcode')]
                );         
                //End
            }
        }
        $this->_redirect('houzz/profile/index');
          return ;
    }
}


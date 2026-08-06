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
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Houzz\Controller\Adminhtml\Profile;

use Braintree\Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;


class Save extends \Magento\Backend\App\Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    protected $_eventManager;
    protected $_objectManager;
    protected $messageManager;
    protected $_coreRegistry;
    Protected $_configFactory;
    protected $_configStructure;
    protected $_cache;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Config\Model\Config\Structure\Element\Group $group,
        \Magento\Config\Model\Config\Structure $configStructure,
        \Magento\Config\Model\Config\Factory $configFactory,
        \Ced\Houzz\Helper\Cache $cache,
        PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_eventManager = $eventManager;
        $this->_objectManager = $objectManager;
        $this->messageManager = $messageManager;
        $this->_configStructure = $configStructure;
        $this->_coreRegistry = $coreRegistry;
        $this->_configFactory = $configFactory;
        $this->_cache = $cache;
    }

    /**
     *
     * @param string $idFieldName
     * @return mixed
     */
    protected function _initProfile($idFieldName = 'pcode')
    {

        $profileCode = $this->getRequest()->getParam($idFieldName);
        $profile = $this->_objectManager->get('Ced\Houzz\Model\Profile');
        if ($profileCode) {
            $profile->loadByField('profile_code', $profileCode);
        }
        $this->getRequest()->setParam('is_houzz', 1);
        $this->_coreRegistry->register('current_profile', $profile);
        return $this->_coreRegistry->registry('current_profile');
    }


    public function execute()
    {
        $data = $this->_objectManager->create('Magento\Config\Model\Config\Structure\Element\Group')->getData();
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_context = $this->_objectManager->get('Magento\Framework\App\Helper\Context');
        $redirectBack = $this->getRequest()->getParam('back', false);
        $tab = $this->getRequest()->getParam('tab', false);
        $pcode = $this->getRequest()->getParam('pcode', false);
        $profileData = $this->getRequest()->getPostValue();
        $entity_id = array();
        $profileData = json_decode(json_encode($profileData), 1);

        $inProfile = $this->getRequest()->getParam('in_profile');
        /*$profileProducts = $this->getRequest()->getParam('in_profile_product', null);
        parse_str($profileProducts, $profileProducts);
        $profileProducts = array_keys($profileProducts);*/
        $profileData = json_decode(json_encode($profileData), 1);

        $profileProductsStr = $this->getRequest()->getParam('in_profile_products', null);
        if(strlen($profileProductsStr) > 0 ) {
            $profileProducts  = explode(',' , $profileProductsStr);
        }else{
            $profileProducts = [];
        }
        
        $resource = $this->getRequest()->getPost('resource', false);
        try {
            $profile = $this->_initProfile('pcode');
            if (!$profile->getId() && $pcode) {
                $this->messageManager->addError(__('This Profile no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
            $pname = $profileData['profile_name'];
            if (isset($profileData['profile_code'])) {
                $pcode = $profileData['profile_code'];
                $profileCollection = $this->_objectManager->get('Ced\Houzz\Model\Profile')->getCollection()->
                addFieldToFilter('profile_code', $profileData['profile_code']);
                if (count($profileCollection) > 0) {
                    $this->messageManager->addError(__('This Profile Already Exist Please Change Profile Code'));
                    $this->_redirect('*/*/new');
                    return;
                }
            }
            $profile->addData($profileData);
            $requriedAttributes = array();
            if (isset($profileData['required_attributes'])) {
                $temAttribute = $this->unique_multidim_array($profileData['required_attributes'], 'houzz_attribute_name');
                $requriedAttributes['required_attributes'] = array_filter(array_map(function ($n) {
                    if ($n['required']) return $n;
                }, $temAttribute));
                $requriedAttributes['optional_attributes'] = array_filter(array_map(function ($n) {
                    if (!$n['required']) return $n;
                }, $temAttribute));
            }

            if (isset($profileData['variant_attributes']))
                $requriedAttributes['variant_attributes'] = $this->unique_multidim_array($profileData['variant_attributes'], 'houzz_attribute_name');
            $profile->setProfileAttributeMapping(json_encode($requriedAttributes));
            $profile->save();
            $profileArray = $profile->getData();
            $profileArray['profile_attribute_mapping'] = json_decode($profileArray['profile_attribute_mapping'], true);
            //cache values
            $this->_cache->setValue(\Ced\Houzz\Helper\Cache::PROFILE_CACHE_KEY . $profile->getId(), $profileArray);
            $oldProfileProducts = $this->_objectManager->create("Ced\Houzz\Model\Profileproducts")
                ->getProfileProducts($profile->getId());
            $deleteProds = array_diff($oldProfileProducts, $profileProducts);
            $addProds = array_diff($profileProducts, $oldProfileProducts);
            foreach ($deleteProds as $oUid) {
                $this->_deleteProductFromProfile($oUid);
                $this->_cache->removeValue(\Ced\Houzz\Helper\Cache::PROFILE_PRODUCT_CACHE_KEY . $oUid);
            }

            foreach ($addProds as $nRuid) {
                if ($this->_addProductToProfile($nRuid, $profile->getId()))
                    $this->_cache->setValue(\Ced\Houzz\Helper\Cache::PROFILE_PRODUCT_CACHE_KEY . $nRuid, $profile->getId());
            }
            if ($redirectBack && $redirectBack == 'edit') {
                $this->messageManager->addSuccess(__('
		   		You Saved The Houzz Profile And Its Products.
		   			'));
                $this->_redirect('*/*/edit', array(
                    'back' => 'edit',
                    'tab' => $tab,
                    'active_tab' => null,
                    'pcode' => $pcode,
                    'section' => 'houzzconfiguration',
                ));
            } else if ($redirectBack && $redirectBack == 'upload') {
                $this->messageManager->addSuccess(__('
		   		You Saved The Houzz Profile And Its Products. Upload Product Now.
		   			'));
                $this->_redirect('houzz/products/index', array(
                    'profile_id' => $profile->getId(),
                    'pcode' => $profile->getProfileCode()
                ));
            } else {
                $this->messageManager->addSuccess(__('
		   		You Saved The Houzz Profile And Its Products.
		   		'));
                $this->_redirect('*/*/');
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__('
		   		Unable to Save Profile Please Try Again.
		   			' . $e->getMessage()));
            $this->_redirect('*/*/edit', array(
                'back' => 'edit',
                'tab' => $tab,
                'active_tab' => null,
                'pcode' => $pcode,
                'section' => 'houzzconfiguration',
            ));
        }

        return;
    }

    protected function _addProductToProfile($productId, $profileId)
    {

        $profileproduct = $this->_objectManager->create("Ced\Houzz\Model\Profileproducts");
        $product = $this->_objectManager->create("Magento\Catalog\Model\Product")->load($productId);
        if ($product->getTypeId() == 'configurable') {
            //removed from already assigned profile
            $this->_deleteProductFromProfile($productId);
            $childIds = $product->getTypeInstance()->getUsedProductIds($product);
            if (isset($childIds))
                foreach ($childIds as $id) {
                    $this->_addProductToProfile($id, $profileId);
                }
        }
        //skip product if parent already exist in other profile
        if ($product->getTypeId() == 'simple') {

            $checkForChild = $this->_objectManager->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable')->getParentIdsByChild($product->getId());

            if (!empty($checkForChild) && count($checkForChild) > 0) {
                $profileToProducts = $profileproduct->loadByField('product_id', $checkForChild[0])->getData();
                if (!empty($profileToProducts) && $profileToProducts['profile_id'] != $profileId) {
                    $this->messageManager->addError('The Parent Product (ID - ' . $checkForChild[0] . ' ) of the SKU - ' . $product->getSku() . ' is already assigned to Pofile ID - ' . $profileToProducts['profile_id'] . '. Please unassign it to continue.');
                    return false;
                }
            }
        }
        if ($profileproduct->profileProductExists($productId, $profileId) === true) {
            return false;
        } else {
            $profileproduct->deleteFromProfile($productId);
            $profileproduct->setProductId($productId);
            $profileproduct->setProfileId($profileId);
            $profileproduct->save();
            return true;
        }
    }

    protected function _deleteProductFromProfile($productId)
    {
        try {
            $this->_objectManager->create("Ced\Houzz\Model\Profileproducts")
                ->deleteFromProfile($productId);
        } catch (\Exception $e) {
            throw $e;
            return false;
        }
        return true;
    }

    /* Identify unique houzz attributes
   */
    function unique_multidim_array($array, $key)
    {
        $temp_array = array();
        $i = 0;
        $key_array = array();
        foreach ($array as $val) {
            if ($val['delete'] == 1)
                continue;

            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }
}
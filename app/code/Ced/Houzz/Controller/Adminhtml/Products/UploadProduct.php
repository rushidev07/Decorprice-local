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

namespace Ced\Houzz\Controller\Adminhtml\Products;

class UploadProduct extends \Magento\Backend\App\Action
{
    /**
     * Result Page Factory
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Houzz::houzz_products_uploadproduct';


    /**
     * UploadProduct constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Product Sync on Catalog Form
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {

        $dataHelper = $this->_objectManager->get('Ced\Houzz\Helper\Data');
        if(!$dataHelper->checkForConfiguration()) {
            $this->messageManager->addNoticeMessage(__('Houzz API not enabled or Invalid. Please check Houzz Configuration.'));

            $this->_redirect->getRefererUrl();

        }
        $productids[] = $this->getRequest()->getParam('id');
        if ($dataHelper->createProductOnHouzz($productids)) {
            $this->messageManager->addSuccessMessage(count($productids) . ' Product Synced on Houzz Successfully');
            return  $this->_redirect('catalog/product/edit/id/'. $productids[0]);
        }
        $this->messageManager->addErrorMessage('Product Synced on Houzz Failed.');
        return $this->_redirect('catalog/product/edit/id/' . $productids[0]);



    }

    /**
     * IsALLowed
     * @return boolean
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_Houzz::Houzz');
    }

}

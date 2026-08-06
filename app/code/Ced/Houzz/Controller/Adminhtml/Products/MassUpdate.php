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

class MassUpdate extends \Magento\Backend\App\Action
{
    const CHUNK_SIZE = 1;

    /**
     * PageFactory
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * Filter
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /**
     * Session
     * @var \Magento\Backend\Model\Session
     */
    public $session;

    /**
     * Json Factory
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    public $resultJsonFactory;

    public $dataHelper;

    public $registry;

    /**
     * MassUpload constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Ced\Houzz\Helper\Data $data
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Ced\Houzz\Helper\Data $data,
        \Magento\Catalog\Model\Product $product
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->filter = $filter;
        $this->session =  $context->getSession();
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dataHelper = $data;
        $this->registry = $registry;
        $this->product = $product;
    }

    /**
     * Product sync
     */
    public function execute()
    {
        $this->session->unsResponseSession();
        if(!$this->dataHelper->checkForConfiguration()) {
            $this->messageManager->addErrorMessage(__('Products Update Failed. Houzz API not enabled or Invalid. Please check Houzz Configuration.'));
            $resultRedirect = $this->resultFactory->create('redirect');
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
        $batchid = $this->getRequest()->getParam('batchid');

        if (isset($batchid)) {
            $resultJson = $this->resultJsonFactory->create();
            $productids = $this->session->getHouzzProducts();
            $lastElement = end($productids);
            //$lastElementKey = key($productids);
            $product = $this->product->load($productids[$batchid][0]);
            if(empty($this->session->getResponseSession()))
                $this->session->setResponseSession([]);
            if($lastElement[0] == $productids[$batchid][0] && $product->getTypeId() == 'simple' ) {
                $this->session->setAllBatchCompleted(true);
            }
            if (isset($productids[$batchid]) && $this->dataHelper->createProductOnHouzz($productids[$batchid], 'UpdateListingRequest')) {
                return $resultJson->setData([
                    'success' => "<b> SKU : " . $product->getSku() . "</b> Product(s) Updated successfully",
                ]);
            }
            return $resultJson->setData([
                'error' => "<b> SKU : " . $product->getSku() . "</b> Product(s) Update Failed",
            ]);
        }

        $this->dataHelper = $this->_objectManager->get('Ced\Houzz\Helper\Data');
        $collection = $this->filter->getCollection($this->_objectManager->create('Magento\Catalog\Model\Product')
            ->getCollection());
        $productids = $collection->getAllIds();

        if (count($productids) == 0) {
            $this->messageManager->addErrorMessage('No Product selected to update.');
            $resultRedirect = $this->resultFactory->create('redirect');
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

        if (count($productids) < self::CHUNK_SIZE - 1) {
            if ( $this->dataHelper->createProductOnHouzz($productids, 'UpdateListingRequest')) {
                $this->messageManager->addSuccessMessage(count($productids) . ' Product(s) Updated Successfully');
            } else {
                $this->messageManager->addErrorMessage('Product(s) Updated Failed.');
            }

            $resultRedirect = $this->resultFactory->create('redirect');
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
        $productids = array_chunk($productids, self::CHUNK_SIZE);
        $this->registry->register('productids', count($productids));
        $this->session->setHouzzProducts($productids);
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_Houzz::Houzz');
        $resultPage->getConfig()->getTitle()->prepend(__('Update Products'));
        return $resultPage;

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
